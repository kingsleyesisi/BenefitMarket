<?php
session_start();
include_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$trade_category = isset($_POST['trade_category']) ? strtolower(trim($_POST['trade_category'])) : "";
$trade_type     = isset($_POST['trade_type']) ? strtolower(trim($_POST['trade_type'])) : "";
$asset          = isset($_POST['asset']) ? trim($_POST['asset']) : "";
$amount         = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if (empty($trade_category) || empty($trade_type) || empty($asset) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters.']);
    exit();
}

// Fetch user's balances using PDO
try {
    $stmt = $conn->prepare("SELECT total_balance, bitcoin_balance, ethereum_balance FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $total_usd = floatval($row['total_balance']);
        $bitcoin_balance = floatval($row['bitcoin_balance']);
        $ethereum_balance = floatval($row['ethereum_balance']);

        // Crypto Buy: Ensure USD balance is enough
        if ($trade_type === 'crypto' && $trade_category === 'buy' && $amount > $total_usd) {
            echo json_encode(['success' => false, 'message' => 'Insufficient USD balance for this trade.']);
            exit();
        }

        // Crypto Sell: Ensure asset balance is enough
        if ($trade_type === 'crypto' && $trade_category === 'sell') {
            if (($asset === 'Bitcoin' && $amount > $bitcoin_balance) || ($asset === 'Ethereum' && $amount > $ethereum_balance)) {
                echo json_encode(['success' => false, 'message' => 'Insufficient asset balance.']);
                exit();
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'User balance not found.']);
        exit();
    }

    $stmt->close();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    exit();
}

// If no issues, return success
echo json_encode(['success' => true]);
exit();
?>
