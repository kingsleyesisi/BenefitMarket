<?php

session_start();

// Enable Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config.php';  // provides $conn (PDO) to Neon Postgres

// Include PHPMailer classes
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$debug = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF and authentication checks
    if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    // File upload handling
    if (empty($_FILES['deposit_proof']) || $_FILES['deposit_proof']['error'] !== UPLOAD_ERR_OK) {
        die("Error uploading deposit proof.");
    }

    // Process form data
    $user_id = $_SESSION['user_id'];
    $crypto_type = $_POST['crypto_type'] ?? '';
    $amount = $_POST['amount'] ?? '';
    
    // Validate inputs early
    if (empty($crypto_type) || empty($amount)) {
        die("Missing required fields.");
    }
    
    // Validate and clean amount before transaction
    $amount = str_replace(',', '', $amount);
    if (!is_numeric($amount) || $amount <= 0) {
        die("Invalid amount value.");
    }
    
    // Define wallet addresses
    $walletAddresses = [
        'BTC'    => '1Lv8ATWZRtHETcMdcUXAYPFenzweef4h2Z',
        'ETH'    => '0xcfa4ab51d1e3c1b152b8dbb56dc436f2685d9926',
        'USDT'   => '0xcfa4ab51d1e3c1b152b8dbb56dc436f2685d9926',
        'SOLANA' => 'oaLS1TdRuhfukFky7ggo37EKB91rTBKBtxfc19wKbjY',
        'TON'    => 'UQBnvXwgfbGrnwhyXFNrunhuhTdODci_QvRSBXjUwwJaSNTW',
        'XRP'    => 'rJn2zAPdFA193sixJwuFixRkYDUtx3apQh'
    ];
    
    if (!isset($walletAddresses[$crypto_type])) {
        die("Invalid crypto type selected.");
    }

    // Process file upload
    $uploadDir = sys_get_temp_dir() . '/uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0700, true)) {
            die("Failed to create upload directory.");
        }
    }

    $fileTmpPath = $_FILES['deposit_proof']['tmp_name'];
    $fileExtension = pathinfo($_FILES['deposit_proof']['name'], PATHINFO_EXTENSION);
    $uniqueFileName = uniqid('proof_', true) . '.' . $fileExtension;
    $dest_path = $uploadDir . $uniqueFileName;
    
    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        die("Error moving the uploaded file.");
    }

    // Database operations with proper error handling
    $deposit_id = null;
    $user = null;
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert deposit
        $stmt = $conn->prepare("
            INSERT INTO deposits 
                (user_id, crypto_type, amount, wallet_address, deposit_proof, status)
            VALUES 
                (:user_id, :crypto_type, :amount, :wallet_address, :deposit_proof, 'pending')
            RETURNING deposit_id
        ");
        
        $result = $stmt->execute([
            ':user_id' => $user_id,
            ':crypto_type' => $crypto_type,
            ':amount' => $amount,
            ':wallet_address' => $walletAddresses[$crypto_type],
            ':deposit_proof' => $dest_path
        ]);
        
        if (!$result) {
            throw new Exception("Failed to insert deposit record.");
        }
        
        // Get auto-generated ID
        $depositResult = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$depositResult || !isset($depositResult['deposit_id'])) {
            throw new Exception("Failed to retrieve deposit ID.");
        }
        $deposit_id = $depositResult['deposit_id'];

        // Get user details
        $userStmt = $conn->prepare("SELECT email, fname, lname FROM users WHERE user_id = :user_id");
        $userResult = $userStmt->execute([':user_id' => $user_id]);
        
        if (!$userResult) {
            throw new Exception("Failed to query user details.");
        }
        
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            throw new Exception("User not found.");
        }
        
        // Commit transaction only after all database operations succeed
        $conn->commit();
        
    } catch (PDOException $e) {
        // Rollback on database error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("POSTGRES ERROR: " . $e->getMessage());
        // Clean up uploaded file on database error
        if (file_exists($dest_path)) {
            unlink($dest_path);
        }
        die("DATABASE ERROR: " . $e->getMessage());
    } catch (Exception $e) {
        // Rollback on other errors
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error: " . $e->getMessage());
        // Clean up uploaded file on error
        if (file_exists($dest_path)) {
            unlink($dest_path);
        }
        die("Error processing deposit: " . $e->getMessage());
    }
    
    // If we reach here, database operations were successful
    // Now handle email notifications (outside of transaction)
    
    // Prepare email data
    $depositDetails = [
        ['label' => 'Deposit ID',     'value' => $deposit_id],
        ['label' => 'Crypto Type',    'value' => $crypto_type],
        ['label' => 'Amount',         'value' => $amount],
        ['label' => 'Wallet Address', 'value' => $walletAddresses[$crypto_type]],
        ['label' => 'Status',         'value' => 'pending'],
        ['label' => 'Date',           'value' => date('Y-m-d H:i:s')]
    ];

    // Send emails (failures here won't affect the database transaction)
    $emailSuccess = false;
    $adminEmailSuccess = false;
    
    try {
        $emailSuccess = sendDepositConfirmationEmail(
            $user['email'],
            $user['fname'],
            $user['lname'],
            'pending',
            $depositDetails,
            $debug
        );
    } catch (Exception $e) {
        error_log("Failed to send user confirmation email: " . $e->getMessage());
    }
    
    try {
        $adminEmailSuccess = sendAdminNotificationEmail($dest_path, $depositDetails, $debug);
    } catch (Exception $e) {
        error_log("Failed to send admin notification email: " . $e->getMessage());
    }
    
    // Provide feedback to user
    if ($emailSuccess) {
        echo "Deposit submitted successfully. Confirmation sent.<br>";
    } else {
        echo "Deposit submitted but email confirmation failed.<br>";
    }
    
    if ($adminEmailSuccess) {
        error_log("Admin notification sent successfully");
    } else {
        error_log("Failed to send admin notification");
    }

} else {
    die("Invalid request method.");
}

/**
 * Sends an admin notification email with the deposit proof attachment.
 */
function sendAdminNotificationEmail($filePath, $depositDetails, $debug = false) {
    $mail = new PHPMailer(true);
    try {
        $smtp_accounts = [
            [
                'from_email' => 'support@benefitsmart.online',
                'username'   => 'support@benefitsmart.online',
                'password'   => 'mF(UO8Ls!F'
            ],
            [
                'from_email' => 'info@benefitsmart.online',
                'username'   => 'info@benefitsmart.online',
                'password'   => 'Kingsley419.'
            ],
        ];
        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];

        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.online';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        if ($debug) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
        }

        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress('kingsleyesisi1@gmail.com', 'Admin');
        $mail->addAddress('kingsleyesisi@gmail.com', 'Second Admin');
        
        // Add the uploaded file as attachment
        if (file_exists($filePath)) {
            $mail->addAttachment($filePath, 'Deposit_Proof_'.basename($filePath));
        }

        // Build details table
        $detailsRows = '';
        foreach ($depositDetails as $detail) {
            $label = htmlspecialchars($detail['label']);
            $value = htmlspecialchars($detail['value']);
            $detailsRows .= "<tr>
                <th style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'>$label</th>
                <td style='padding: 10px; border: 1px solid #ddd;'>$value</td>
            </tr>";
        }

        $mail->isHTML(true);
        $mail->Subject = "New Deposit Document Uploaded";
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>New Deposit Document Received</h2>
        <p>A new deposit proof document has been uploaded with the following details:</p>
        <table>$detailsRows</table>
        <p>The attached document has been automatically forwarded for review.</p>
    </div>
</body>
</html>";

        $mail->AltBody = "A new deposit proof document has been uploaded. Check attachments for details.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Admin Notification Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Sends a deposit confirmation email to the user.
 */
function sendDepositConfirmationEmail($toEmail, $fname, $lname, $newStatus, $details = [], $debug = false) {
    // Validate status
    $validStatuses = ['pending', 'approved', 'failed'];
    if (!in_array(strtolower($newStatus), $validStatuses)) {
        throw new InvalidArgumentException("Invalid deposit status: $newStatus");
    }
    
    $mail = new PHPMailer(true);
    try {
        // Define SMTP accounts (choose one at random)
        $smtp_accounts = [
            [
                'from_email' => 'support@benefitsmart.online',
                'username'   => 'support@benefitsmart.online',
                'password'   => 'mF(UO8Ls!F'
            ],
            [
                'from_email' => 'info@benefitsmart.online',
                'username'   => 'info@benefitsmart.online',
                'password'   => 'Kingsley419.'
            ],
        ];
        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];
        
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.online';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        if ($debug) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
        }
        
        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress($toEmail, "$fname $lname");
        
        // Build HTML table rows from the deposit details
        $detailsRows = '';
        foreach ($details as $detail) {
            $label = htmlspecialchars($detail['label']);
            $value = htmlspecialchars($detail['value']);
            $detailsRows .= "<tr>
                <th style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'>$label</th>
                <td style='padding: 10px; border: 1px solid #ddd;'>$value</td>
            </tr>";
        }
        
        $mail->isHTML(true);
        $mail->Subject = "Deposit Confirmation - Benefit Market Trade";
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <title>Deposit Confirmation</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7fafc; }
    .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ddd; }
    th { background-color: #f9f9f9; text-align: left; }
  </style>
</head>
<body>
  <div class='container'>
    <h2>Deposit Confirmation</h2>
    <p>Dear $fname $lname,</p>
    <p>Thank you for your deposit. Below are the details of your transaction:</p>
    <table>$detailsRows</table>
    <p>Your deposit is currently pending. You will receive an update when the status changes.</p>
    <p>Best regards,<br>Benefit Market Trade Team</p>
  </div>
</body>
</html>
";
        $mail->AltBody = "Dear $fname $lname, thank you for your deposit. Your deposit is pending. You will receive an update when the status changes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Deposit Confirmation Email Error: " . $mail->ErrorInfo);
        if ($debug) {
            echo "Deposit Confirmation Email Error: " . $mail->ErrorInfo;
        }
        return false;
    }
}

/**
 * Sends an email notification when the deposit status changes.
 */
function sendDepositStatusChangeEmail($toEmail, $fname, $lname, $newStatus, $details = [], $debug = false) {
    $mail = new PHPMailer(true);
    try {
        // Use the same SMTP accounts as before
        $smtp_accounts = [
            [
                'from_email' => 'support@benefitsmart.online',
                'username'   => 'support@benefitsmart.online',
                'password'   => 'mF(UO8Ls!F'
            ],
            [
                'from_email' => 'info@benefitsmart.online',
                'username'   => 'info@benefitsmart.online',
                'password'   => 'Kingsley419.'
            ],
        ];
        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];
        
        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.online';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        if ($debug) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
        }
        
        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress($toEmail, "$fname $lname");
        
        // Build HTML table rows for deposit details
        $detailsRows = '';
        foreach ($details as $detail) {
            $label = htmlspecialchars($detail['label']);
            $value = htmlspecialchars($detail['value']);
            $detailsRows .= "<tr>
                <th style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'>$label</th>
                <td style='padding: 10px; border: 1px solid #ddd;'>$value</td>
            </tr>";
        }
        $statusDisplay = ucfirst(strtolower($newStatus));
        $mail->isHTML(true);
        $mail->Subject = "Deposit Status Update - " . $statusDisplay;
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <title>Deposit Status Update</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7fafc; }
    .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ddd; }
    th { background-color: #f9f9f9; text-align: left; }
  </style>
</head>
<body>
  <div class='container'>
    <h2>Deposit Status Update</h2>
    <p>Dear $fname $lname,</p>
    <p>Your deposit status has been updated to <strong>" . $statusDisplay . "</strong>. Below are the updated details of your deposit:</p>
    <table>$detailsRows</table>
    <p>Thank you for your patience. For further inquiries, please contact our support team.</p>
    <p>Best regards,<br>Benefit Market Trade Team</p>
  </div>
</body>
</html>
";
        $mail->AltBody = "Dear $fname $lname, your deposit status has been updated to " . $statusDisplay . ". Please contact support for more details.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Deposit Status Update Email Error: " . $mail->ErrorInfo);
        if ($debug) {
            echo "Deposit Status Update Email Error: " . $mail->ErrorInfo;
        }
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Deposit Confirmation - Tradex Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
  <div class="bg-white p-6 sm:p-8 rounded-xl shadow-2xl w-full max-w-md text-center">
    <h1 class="text-2xl sm:text-3xl font-bold mb-4">Thank You!</h1>
    <p class="text-sm sm:text-base mb-6">
      Your deposit has been recorded and is pending confirmation.
    </p>
    <a href="user_dashboard.php" class="inline-block bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-500 transition">
      Back to Dashboard
    </a>
  </div>
</body>
</html>