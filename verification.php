<?php
session_start();
include_once 'config.php';
// at the top of verification.php (or in config.php)
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Maximum allowed file size (10 MB here; adjust as needed)
const MAX_FILE_SIZE = 10 * 1024 * 1024; // bytes

// Check if the user is logged in and has the role 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve & clear flash message
$verificationMessage = $_SESSION['verification_message'] ?? '';
unset($_SESSION['verification_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['udocument'])) {
    $dtype    = $_POST['dtype'];
    $document = $_FILES['udocument'];

    // 1) Check for PHP upload errors
    switch ($document['error']) {
        case UPLOAD_ERR_OK:
            break; // all good
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $_SESSION['verification_message'] = "File is too large. Maximum size is " . (MAX_FILE_SIZE / (1024*1024)) . " MB.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        case UPLOAD_ERR_PARTIAL:
            $_SESSION['verification_message'] = "File was only partially uploaded. Please try again.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        case UPLOAD_ERR_NO_FILE:
            $_SESSION['verification_message'] = "No file uploaded.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        default:
            $_SESSION['verification_message'] = "Upload error (code {$document['error']}).";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
    }

    // 2) Enforce our own size limit
    if ($document['size'] > MAX_FILE_SIZE) {
        $_SESSION['verification_message'] = "File exceeds the server limit of " . (MAX_FILE_SIZE / (1024*1024)) . " MB.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // 3) Ensure it really is an uploaded file
    if (!is_uploaded_file($document['tmp_name'])) {
        $_SESSION['verification_message'] = "Possible file upload attack detected.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // 4) Move to your uploads directory
    $uploadDir  = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $_SESSION['verification_message'] = "Cannot create upload directory.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $safeName   = preg_replace('/[^a-zA-Z0-9_\.\-]/','_', basename($document['name']));
    $uploadFile = $uploadDir . $safeName;

    if (move_uploaded_file($document['tmp_name'], $uploadFile)) {
      try {
          $sql = "
              INSERT INTO verification
                (user_id, document_type, file_path, submission_date, status)
              VALUES
                (:uid, :dtype, :path, NOW(), 'Pending')
          ";
          $stmt = $conn->prepare($sql);
          $success = $stmt->execute([
              ':uid'   => $user_id,
              ':dtype' => $dtype,
              ':path'  => $uploadFile
          ]);
          
                  
        if ($success && $stmt->rowCount() > 0) {
          // Get user details for email
          $userDetails = getUserDetails($conn, $user_id);
          
          if ($userDetails) {
              // Send user confirmation
              sendUserConfirmation(
                  $userDetails['email'],
                  $userDetails['fname'],
                  $dtype
              );
              
              // Send admin notification
              sendAdminNotification(
                  $uploadFile,
                  $user_id,
                  $dtype
              );
          }
          
          $_SESSION['verification_message'] = "Document submitted successfully for verification.";
        } else {
              // No rows inserted—this is odd, log info
              $info = $stmt->errorInfo();
              $_SESSION['verification_message'] = "Insert failed: " . var_export($info, true);
          }
      } catch (PDOException $e) {
          // Temporarily expose the actual error
          $_SESSION['verification_message'] = "Exception: " . $e->getMessage();
          // In production: error_log($e->getMessage());
      }
  } else {
      $_SESSION['verification_message'] = "Error uploading file. Please try again.";
  }
  

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch the latest verification status
try {
    $sqlLatest = "
        SELECT
          submission_date,
          document_type,
          status,
          results
        FROM verification
        WHERE user_id = :uid
        ORDER BY submission_date DESC
        LIMIT 1
    ";
    $stmt = $conn->prepare($sqlLatest);
    $stmt->execute([':uid' => $user_id]);
    $latestVerification = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // Fetch full verification history
    $sqlHistory = "
        SELECT
          submission_date,
          document_type,
          status,
          results
        FROM verification
        WHERE user_id = :uid
        ORDER BY submission_date DESC
    ";
    $stmt = $conn->prepare($sqlHistory);
    $stmt->execute([':uid' => $user_id]);
    $verificationHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


function sendUserConfirmation($userEmail, $userName, $documentType) {
  $mail = new PHPMailer(true);
  try {
      $mail->isSMTP();
      $mail->Host       = 'mail.benefitsmart.xyz';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'support@benefitsmart.xyz';
      $mail->Password   = 'mF(UO8Ls!F';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;

      $mail->setFrom('support@benefitsmart.xyz', 'Benefit Market Trade Verification');
      $mail->addAddress($userEmail, $userName);

      $mail->isHTML(true);
      $mail->Subject = 'Verification Submission Received';
      $mail->Body = "
          <h2>Verification Document Received</h2>
          <p>Dear $userName,</p>
          <p>We've received your $documentType document for verification. Our team will review it and update your status within 2-3 business days.</p>
          <p><strong>Submission Details:</strong></p>
          <ul>
              <li>Document Type: $documentType</li>
              <li>Submission Date: " . date('Y-m-d H:i') . "</li>
          </ul>
          <p>Thank you for your patience.</p>
          <p>Benefit Market Trade Support Team</p>
      ";

      $mail->send();
      return true;
  } catch (Exception $e) {
      error_log("User confirmation email failed: " . $e->getMessage());
      return false;
  }
}

function sendAdminNotification($filePath, $userId, $documentType) {
  $mail = new PHPMailer(true);
  try {
      $mail->isSMTP();
      $mail->Host       = 'mail.benefitsmart.xyz';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'support@benefitsmart.xyz';
      $mail->Password   = 'mF(UO8Ls!F';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;

      $mail->setFrom('support@benefitsmart.xyz', 'Verification System');
      $mail->addAddress('kingsleyesisi1@gmail.com', 'Admin');
       // Add a second admin as a normal TO (visible to all recipients)
      $mail->addAddress('richinvestor973@gmail.com', 'Second Admin');
      $mail->addAttachment($filePath, 'Verification_Document_' . basename($filePath));

      $mail->isHTML(true);
      $mail->Subject = "New Verification Submission - User $userId";
      $mail->Body = "
          <h2>New Verification Document Submitted</h2>
          <p><strong>User ID:</strong> $userId</p>
          <p><strong>Document Type:</strong> $documentType</p>
          <p><strong>Submission Time:</strong> " . date('Y-m-d H:i:s') . "</p>
          <p>The attached document requires review.</p>
      ";

      $mail->send();
      return true;
  } catch (Exception $e) {
      error_log("Admin notification failed: " . $e->getMessage());
      return false;
  }
}

// Fetch user details for email
function getUserDetails($conn, $userId) {
  try {
      $stmt = $conn->prepare("SELECT email, fname FROM users WHERE user_id = :uid");
      $stmt->execute([':uid' => $userId]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      error_log("User details fetch failed: " . $e->getMessage());
      return false;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Benefit Market Trade - Verification</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <!-- Optional: Link to your local Tailwind file -->
  <link rel="stylesheet" href="tailwind.min.css" />
  <link rel="stylesheet" href="./remixicon/remixicon.css" />
  <style>
    .menu-items { padding: 1rem; }
    .nav-links li { margin: 1rem 0; }
    .nav-links li a { display: flex; align-items: center; gap: 0.5rem; }
    .logout-mode li { margin-top: 1rem; }
    @media (max-width: 1024px) {
      .desktop-nav { display: none; }
    }
  </style>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Mobile sidebar toggle
      const menuToggle = document.getElementById("menuToggle");
      const closeSidebar = document.getElementById("closeSidebar");
      const sidebar = document.getElementById("mobileSidebar");
      const overlay = document.getElementById("overlay");

      menuToggle.addEventListener("click", function () {
          sidebar.classList.remove("-translate-x-full");
          overlay.classList.remove("hidden");
      });

      closeSidebar.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
      });

      overlay.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
      });

      // Desktop dropdowns
      const notificationBtn = document.getElementById("notificationBtn");
      const notificationDropdown = document.getElementById("notificationDropdown");
      const userBtn = document.getElementById("userBtn");
      const userDropdown = document.getElementById("userDropdown");

      if(notificationBtn){
        notificationBtn.addEventListener("click", function(e){
          e.stopPropagation();
          notificationDropdown.classList.toggle("hidden");
          userDropdown.classList.add("hidden");
        });
      }

      if(userBtn){
        userBtn.addEventListener("click", function(e){
          e.stopPropagation();
          userDropdown.classList.toggle("hidden");
          notificationDropdown.classList.add("hidden");
        });
      }

      // Mobile dropdowns
      const mobileNotificationBtn = document.getElementById("mobileNotificationBtn");
      const mobileNotificationDropdown = document.getElementById("mobileNotificationDropdown");
      const mobileUserBtn = document.getElementById("mobileUserBtn");
      const mobileUserDropdown = document.getElementById("mobileUserDropdown");

      if(mobileNotificationBtn){
        mobileNotificationBtn.addEventListener("click", function(e){
          e.stopPropagation();
          mobileNotificationDropdown.classList.toggle("hidden");
          mobileUserDropdown.classList.add("hidden");
        });
      }
      if(mobileUserBtn){
        mobileUserBtn.addEventListener("click", function(e){
          e.stopPropagation();
          mobileUserDropdown.classList.toggle("hidden");
          mobileNotificationDropdown.classList.add("hidden");
        });
      }

      document.addEventListener("click", function(){
        notificationDropdown && notificationDropdown.classList.add("hidden");
        userDropdown && userDropdown.classList.add("hidden");
        mobileNotificationDropdown && mobileNotificationDropdown.classList.add("hidden");
        mobileUserDropdown && mobileUserDropdown.classList.add("hidden");
      });
    });
  </script>
</head>
<body class="bg-gray-300 text-gray-900 overflow-hidden">

<div class="flex h-screen">
    <!-- Desktop Sidebar -->
    <aside class="desktop-nav hidden lg:block w-64 bg-gray-700 text-white">
      
    <nav>
  <div class="menu-items">
    <ul class="nav-links space-y-1"> <!-- Reduced spacing -->
      <!-- Nav Logo -->
      <li class="navlogo flex items-center">
        <a href="user_dashboard.php">
          <i class="ri-home-line text-2xl"></i>
          <span class="text-2xl">Benefit Market Trade</span>
        </a>
      </li>
      <hr class="my-2 border-gray-400">
      <!-- Navigation Items -->
      <li>
        <a href="user_dashboard.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-home-line text-xl"></i>
          <span class="ml-2">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="profile.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-user-line text-xl"></i>
          <span class="ml-2">Profile</span>
        </a>
      </li>
      <li>
        <a href="trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-bar-chart-line text-xl"></i>
          <span class="ml-2">Trades</span>
        </a>
      </li>
      <li>
        <a href="deposit.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-download-line text-xl"></i>
          <span class="ml-2">Deposit</span>
        </a>
      </li>
      <!-- <li>
  <a href="notifications.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
    <i class="ri-notification-line text-xl"></i>
    <span class="ml-2">Notifications</span>
  </a>
</li> -->

      <li>
        <a href="withdraw.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-wallet-3-line text-xl"></i>
          <span class="ml-2">Withdrawal</span>
        </a>
      </li>
      <li>
        <a href="suscribption.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-cash-line text-xl"></i>
          <span class="ml-2">Subscriptions</span>
        </a>
      </li>
      
      <li>
        <a href="settings.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-settings-3-line text-xl"></i>
          <span class="ml-2">Settings</span>
        </a>
      </li>
      <li>
        <a href="support.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-chat-3-line text-xl"></i>
          <span class="ml-2">Support</span>
        </a>
      </li>
    </ul>
    <br>
    <ul class="logout-mode">
      <li>
        <a href="logout.php" class="hover:text-gray-300 flex items-center">
          <i class="ri-logout-box-line text-xl"></i>
          <span class="ml-2">Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>


    </aside>
<!-- Mobile Sidebar -->
<aside
  id="mobileSidebar"
  class="fixed top-0 left-0 w-64 h-full bg-gray-700 text-white transform -translate-x-full transition-transform duration-300 lg:hidden z-50"
>
  <!-- Header with Logo and Close Button -->
  <nav>
  <div class="p-4 flex justify-between items-center">
    <div class="flex items-center">
      <a href="user_dashboard.php" class="flex items-center">
        <i class="ri-home-line text-2xl text-white"></i>
        <span class="text-2xl ml-2">Benefit Market Trade</span>
      </a>
    </div>
    <button id="closeSidebar" class="text-white">
      <i class="ri-close-line text-2xl"></i>
    </button>
  </div>

  <!-- Navigation -->

    <div class="menu-items">
      <ul class="nav-links space-y-1">
        <!-- Sidebar Logo (Duplicate Navigation Logo) -->
      
        <hr class="my-2 border-gray-400">

        <!-- Navigation Items -->
        <li>
          <a
            href="user_dashboard.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-home-line text-xl"></i>
            <span class="ml-2">Dashboard</span>
          </a>
        </li>
        <li>
          <a
            href="profile.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-user-line text-xl"></i>
            <span class="ml-2">Profile</span>
          </a>
        </li>
        <li>
          <a
            href="trades.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-bar-chart-line text-xl"></i>
            <span class="ml-2">Trades</span>
          </a>
        </li>
        <li>
          <a
            href="deposit.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-download-line text-xl"></i>
            <span class="ml-2">Deposit</span>
          </a>
        </li>
        <li>
  <a href="notifications.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
    <i class="ri-notification-line text-xl"></i>
    <span class="ml-2">Notifications</span>
  </a>
</li>

        <li>
          <a
            href="withdraw.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-wallet-3-line text-xl"></i>
            <span class="ml-2">Withdrawal</span>
          </a>
        </li>
        <li>
          <a
            href="suscribption.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-cash-line text-xl"></i>
            <span class="ml-2">Subscriptions</span>
          </a>
        </li>
      
        <li>
          <a
            href="settings.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-settings-3-line text-xl"></i>
            <span class="ml-2">Settings</span>
          </a>
        </li>
        <li>
          <a
            href="support.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-chat-3-line text-xl"></i>
            <span class="ml-2">Support</span>
          </a>
        </li>
      </ul>

      <!-- Logout Section -->
      <br>
      <ul class="logout-mode">
        <li>
          <a href="logout.php" class="hover:text-gray-300 flex items-center">
            <i class="ri-logout-box-line text-xl"></i>
            <span class="ml-2">Logout</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>
</aside>


    <!-- Overlay for Mobile Sidebar -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden z-40"></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-auto">
      <!-- Mobile Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm lg:hidden w-full px-4 py-3">
        <div class="flex items-center justify-between">
          <button id="menuToggle" class="text-gray-500 focus:outline-none">
            <i class="ri-menu-line text-2xl"></i>
          </button>
          <a href="user_dashboard.php">
                <i class="ri-home-line text-2xl"></i>
                <span class=" text-2xl ">Benefit Market Trade</span>
              </a>
          <div class="flex items-center space-x-4">
            <button id="mobileNotificationBtn" class="text-gray-500 focus:outline-none relative">
              <i class="ri-notification-3-line text-2xl"></i>
              <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
            </button>
            <button id="mobileUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
              <i class="ri-user-line text-xl text-gray-600"></i>
            </button>
          </div>
        </div>
        <!-- Mobile Dropdowns -->
        <div id="mobileNotificationDropdown" class="absolute top-16 right-4 w-64 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
          <div class="p-4">
            <h3 class="text-lg font-bold mb-2">Notifications</h3>
            <ul class="space-y-2">
            <ul class="space-y-4 text-gray-900">
            <?php
      // Get the user_id from the session
      $user_id = $_SESSION['user_id'];

      // SQL query that unions trades, activity_log, and deposits notifications
      $sql = "
        SELECT activity_date, activity FROM (
          -- Trades Notification
          SELECT 
            trade_date AS activity_date,
            CONCAT(
              'Placed a ',
              trade_category, ' ',
              trade_type, ' trade for ',
              INITCAP(asset),
              ' worth $',
              TO_CHAR(amount, 'FM999999999.00'),
              '.'
            ) AS activity
          FROM trades
          WHERE user_id = $1
          UNION ALL
          -- Activity Log Notification
          SELECT 
            activity_date,
            activity
          FROM activity_log
          WHERE user_id = $2
          UNION ALL
          -- Deposit Notification (without wallet address)
          SELECT 
            created_at AS activity_date,
            CONCAT(
              'Made a deposit of $',
              TO_CHAR(amount, 'FM999999999.00'),
              ' ',
              crypto_type,
              ' (',
              status,
              ').'
            ) AS activity
          FROM deposits
          WHERE user_id = $3
        ) AS combined
        ORDER BY activity_date DESC
        LIMIT 3
      ";

      $stmt_activity = $conn->prepare($sql);
      $stmt_activity->execute([$user_id, $user_id, $user_id]);
      $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

      if (count($result_activity) > 0) {
          foreach ($result_activity as $activity) {
              echo '<li class="border-b border-gray-700 pb-2">';
              echo '<p class="text-sm">' . htmlspecialchars($activity["activity"]) . '</p>';
              echo '<span class="text-xs text-gray-400">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
              echo '</li>';
          }
      } else {
          echo '<li class="text-center text-sm">No recent activity found.</li>';
      }
    ?>
  </ul>
            </ul>
          </div>
        </div>
        <div id="mobileUserDropdown" class="absolute top-16 right-4 w-40 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
          <ul>
            <li>
              <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="ri-user-line mr-2"></i>Profile
              </a>
            </li>
            <li>
              <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="ri-settings-3-line mr-2"></i>Settings
              </a>
            </li>
            <li>
              <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="ri-logout-box-line mr-2"></i>Logout
              </a>
            </li>
          </ul>
        </div>
      </header>

      <!-- Desktop Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm hidden lg:flex items-center px-6 py-4 w-full relative">
  <!-- Left Section -->
  <div class="flex-1">
    <h1 class="text-xl font-semibold">Dashboard</h1>
  </div>

  <!-- Center Section: Image -->
  <div class="flex-1 flex justify-center">
  <a href="user_dashboard.php">
                <i class="ri-home-line text-2xl"></i>
                <span class=" text-2xl ">Benefit Market Trade</span>
              </a>
</div>

  <!-- Right Section -->
  <div class="flex-1 flex items-center justify-end space-x-4">
    <div class="relative">
      <input type="text" placeholder="Search..." class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      <i class="ri-search-line absolute right-3 top-3 text-gray-400"></i>
    </div>
    <!-- Notifications Dropdown -->
    <div class="relative">
      <button id="notificationBtn" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="ri-notification-3-line text-2xl"></i>
        <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
      </button>
      <div id="notificationDropdown" class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
        <div class="p-4">
          <h3 class="text-lg font-bold mb-2">Notifications</h3>
          <ul class="space-y-2">
          <ul class="space-y-4 text-gray-900">
          <?php
      // Get the user_id from the session
      $user_id = $_SESSION['user_id'];

      // SQL query that unions trades, activity_log, and deposits notifications
      $sql = "
        SELECT activity_date, activity FROM (
          -- Trades Notification
          SELECT 
            trade_date AS activity_date,
            CONCAT(
              'Placed a ',
              trade_category, ' ',
              trade_type, ' trade for ',
              INITCAP(asset),
              ' worth $',
              TO_CHAR(amount, 'FM999999999.00'),
              '.'
            ) AS activity
          FROM trades
          WHERE user_id = $1
          UNION ALL
          -- Activity Log Notification
          SELECT 
            activity_date,
            activity
          FROM activity_log
          WHERE user_id = $2
          UNION ALL
          -- Deposit Notification (without wallet address)
          SELECT 
            created_at AS activity_date,
            CONCAT(
              'Made a deposit of $',
              TO_CHAR(amount, 'FM999999999.00'),
              ' ',
              crypto_type,
              ' (',
              status,
              ').'
            ) AS activity
          FROM deposits
          WHERE user_id = $3
        ) AS combined
        ORDER BY activity_date DESC
        LIMIT 3
      ";

      $stmt_activity = $conn->prepare($sql);
      $stmt_activity->execute([$user_id, $user_id, $user_id]);
      $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

      if (count($result_activity) > 0) {
          foreach ($result_activity as $activity) {
              echo '<li class="border-b border-gray-700 pb-2">';
              echo '<p class="text-sm">' . htmlspecialchars($activity["activity"]) . '</p>';
              echo '<span class="text-xs text-gray-400">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
              echo '</li>';
          }
      } else {
          echo '<li class="text-center text-sm">No recent activity found.</li>';
      }
    ?>
  </ul>
          </ul>
        </div>
      </div>
    </div>
    <!-- User Dropdown -->
    <div class="relative">
      <button id="userBtn" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="ri-user-line text-2xl"></i>
      </button>
      <div id="userDropdown" class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
        <ul>
          <li>
            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="ri-user-line mr-2"></i>Profile
            </a>
          </li>
          <li>
            <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="ri-settings-3-line mr-2"></i>Settings
            </a>
          </li>
          <li>
            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="ri-logout-box-line mr-2"></i>Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>
      <!-- Verification Notification -->
      <?php if (!empty($verificationMessage)): ?>
      <div id="verification-notification" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
          <?php echo $verificationMessage; ?>
      </div>
      <?php endif; ?>
      <!-- Main Content -->
      <main class="p-6 min-h-screen">
        <div class="bg-gray-800 text-gray-200 rounded-lg shadow-lg p-6 mb-8">
          <h2 class="text-2xl mb-4 font-bold text-gray-200">
            <i class="ri-coin-line mr-2"></i>Verification
          </h2>
          <div>
            <i class="ri-shield-check-line text-xl"></i> Verification Status:
            <?php
            if ($latestVerification) {
                echo $latestVerification['status'];
            } else {
                echo "Not Verified";
            }
            ?>
          </div>
        </div>
        <div class="bg-gray-800 text-gray-200 rounded-lg shadow-lg p-6 mb-8">
          <h2 class="text-2xl font-bold mb-4 text-white">KYC Verification</h2>
          
          
          
          <form id="verification-form" class="verification-form" method="POST" enctype="multipart/form-data" autocomplete="off">
  <div class="mb-4">
    <label class="block text-white mb-1" for="document-type">Select Document Type:</label>
    <select class="w-full p-2 bg-gray-700 rounded" id="document-type" name="dtype" required>
      <option value="id-card">ID Card</option>
      <option value="passport">Passport</option>
      <option value="driving-licence">Driving Licence</option>
    </select>
  </div>

  <div class="mb-4">
    <label class="block text-white mb-1" for="upload-document">Upload Document:</label>
    <input class="w-full p-2 bg-gray-700 rounded" type="file" id="upload-document" name="udocument" accept=".pdf, .jpg, .png" required>
  </div>

  <!-- Progress Bar -->
  <div id="progress-wrapper" class="mb-4 hidden">
    <div class="w-full bg-gray-300 rounded">
      <div id="progress-bar" class="bg-blue-600 text-xs leading-none py-1 text-center text-white rounded" style="width: 0%;">0%</div>
    </div>
  </div>

  <div>
    <button class="w-full bg-blue-600 hover:bg-blue-500 py-2 rounded transition-colors" type="submit">Submit</button>
  </div>
</form>

<!-- Popup Modal -->
<div id="success-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50">
  <div class="bg-white p-8 rounded shadow-lg text-center w-96">
    <h2 class="text-2xl font-bold mb-4 text-green-600">Upload Successful!</h2>
    <p class="mb-4 text-gray-700">
      Document uploaded successfully.<br>
      Your trading account will be verified within 24 hours.
    </p>
    <p class="mb-4 text-gray-500">Redirecting in <span id="countdown">5</span> seconds...</p>
    <button id="redirect-now" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded">Redirect Now</button>
  </div>
</div>

<script>
document.getElementById('verification-form').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  const xhr = new XMLHttpRequest();

  xhr.open('POST', form.getAttribute('action') || window.location.href, true);

  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      const percentComplete = Math.round((e.loaded / e.total) * 100);
      const progressBar = document.getElementById('progress-bar');
      const progressWrapper = document.getElementById('progress-wrapper');

      progressWrapper.classList.remove('hidden');
      progressBar.style.width = percentComplete + '%';
      progressBar.textContent = percentComplete + '%';
    }
  });

  xhr.onload = function() {
    if (xhr.status === 200) {
      const modal = document.getElementById('success-modal');
      modal.classList.remove('hidden');

      let countdown = 5;
      const countdownElement = document.getElementById('countdown');
      const interval = setInterval(() => {
        countdown--;
        countdownElement.textContent = countdown;
        if (countdown <= 0) {
          clearInterval(interval);
          window.location.href = 'user_dashboard.php';
        }
      }, 1000);

      document.getElementById('redirect-now').addEventListener('click', function() {
        window.location.href = 'user_dashboard.php';
      });

    } else {
      alert("An error occurred during upload. Please try again.");
    }
  };

  xhr.onerror = function() {
    alert("Failed to upload. Network error.");
  };

  xhr.send(formData);
});
</script>

        </div>
      </main>
    </div>
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
_smartsupp.key = '11a8ccf4bcac20b10ed32e93cb4201c86165ce96';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>

</body>
</html>
