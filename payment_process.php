<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

// Retrieve form data
$plan_id    = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
$cryptoType = isset($_POST['crypto_type']) ? $_POST['crypto_type'] : '';

if ($plan_id <= 0 || empty($cryptoType)) {
    die("Missing plan or crypto type.");
}

// Check if file is uploaded without errors
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
    die("No payment proof was uploaded or there was an upload error.");
}

// Create an uploads directory if not exists
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate a unique file name and move the uploaded file
$originalName = basename($_FILES['payment_proof']['name']);
$uniqueName   = time() . '_' . preg_replace('/\s+/', '_', $originalName);
$targetFile   = $uploadDir . $uniqueName;

if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetFile)) {
    die("Failed to upload the payment proof. Please try again.");
}

// Assume the user is logged in; retrieve user id from session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    die("User not logged in.");
}

// Retrieve the monthly price for the selected plan using PDO
$price = 0.00;
try {
    $stmtPlan = $conn->prepare("SELECT monthly_price FROM subscription_plans WHERE plan_id = :plan_id");
    $stmtPlan->execute([':plan_id' => $plan_id]);
    $row = $stmtPlan->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $price = $row['monthly_price'];
    } else {
        die("Plan not found.");
    }
} catch (PDOException $e) {
    die("Error fetching plan price: " . $e->getMessage());
}

// Insert the subscription record into the database using PDO
$status = 'pending';
try {
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, plan_id, amount_paid, status, crypto_transaction_id) VALUES (:user_id, :plan_id, :amount_paid, :status, :crypto_transaction_id)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':plan_id' => $plan_id,
        ':amount_paid' => $price,
        ':status' => $status,
        ':crypto_transaction_id' => $targetFile
    ]);
} catch (PDOException $e) {
    die("Error inserting subscription: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Confirmation</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white">
  <div class="container mx-auto py-12 text-center">
    <h1 class="text-3xl font-bold mb-4">Payment Confirmation</h1>
    <p class="mb-4">
      Thank you for your payment. Your subscription is now under review. You will receive a confirmation within 24 hours.
    </p>
    <a href="user_dashboard.php" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
      Go to Dashboard
    </a>
  </div>
  
<!-- GTranslate Widget -->
  <div class="gtranslate_wrapper"></div>
  <script>
    window.gtranslateSettings = {"default_language":"en","wrapper_selector":".gtranslate_wrapper"};
  </script>
  <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>
  
  <!-- Combined JavaScript for Interactive Elements and Smartsupp Live Chat -->
<!-- Smartsupp Live Chat script -->
<script type="text/javascript">
var _smartsupp = _smartsupp || {};
_smartsupp.key = 'dc15533bf1aa14311d8189fbfd7312a2d14486b5';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>

  <noscript>Powered by <a href="https://www.smartsupp.com" target="_blank">Smartsupp</a></noscript>
</body>
</html>
