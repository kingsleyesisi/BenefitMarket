<?php
session_start();
include_once 'config.php';  // provides $conn (PDO)

header('Content-Type: application/json');

// 1. Auth check
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Validate inputs
$plan_id       = isset($_POST['plan_id'])        ? intval($_POST['plan_id'])        : 0;
$monthly_price = isset($_POST['monthly_price'])  ? floatval($_POST['monthly_price']) : 0.0;

if ($plan_id <= 0 || $monthly_price <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid input."]);
    exit();
}

try {
    // 3. Fetch user usd_balance
    $sqlUser = "SELECT usd_balance FROM users WHERE user_id = :uid";
    $stmt = $conn->prepare($sqlUser);
    $stmt->execute([':uid' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not found."]);
        exit();
    }
    $current_balance = floatval($user['usd_balance']);

    // 4. Check funds
    if ($current_balance < $monthly_price) {
        echo json_encode(["status" => "error", "message" => "Insufficient funds."]);
        exit();
    }

    // 5. Deduct balance
    $new_balance = $current_balance - $monthly_price;
    $sqlUpdate = "UPDATE users SET usd_balance = :new_balance WHERE user_id = :uid";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->execute([
        ':new_balance' => $new_balance,
        ':uid'         => $user_id
    ]);

    // 6. Insert subscription
    $sqlInsert = "
      INSERT INTO subscriptions
        (user_id, plan_id, status, created_at)
      VALUES
        (:uid, :pid, 'completed', NOW())
    ";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->execute([
        ':uid' => $user_id,
        ':pid' => $plan_id
    ]);

    echo json_encode(["status" => "success", "message" => "Subscription successful!"]);
    exit();

} catch (PDOException $e) {
    // Log $e->getMessage() in production instead of returning it
    echo json_encode(["status" => "error", "message" => "Database error."]);
    exit();
}
