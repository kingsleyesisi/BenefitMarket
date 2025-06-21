<?php

session_start();
include_once 'config.php'; // expects: $conn = new PDO(...);

// CSRF Token Setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retrieve and then clear any flash notification
$notification      = $_SESSION['notification']      ?? '';
$notification_type = $_SESSION['notification_type'] ?? '';
unset($_SESSION['notification'], $_SESSION['notification_type']);

// Ensure user is logged in and has the correct role
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit();
}

// Function to generate a unique 8-digit trade ID
function generateUniqueTradeId(PDO $conn): int {
    do {
        $trade_id = random_int(10000000, 99999999);
        $stmt     = $conn->prepare("SELECT 1 FROM trades WHERE trade_id = ?");
        $stmt->execute([$trade_id]);
        $exists   = (bool) $stmt->fetchColumn();
    } while ($exists);
    return $trade_id;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

function sendTradeConfirmationEmail($toEmail, $toName, $tradeId, $trade_category, $trade_type, $asset, $lot_size, $entry_price, $amount, $trade_date): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug   = 2;
        $mail->Debugoutput = 'error_log';
        $mail->isSMTP();
        $mail->Host        = 'mail.benefitsmart.online';
        $mail->SMTPAuth    = true;
        $mail->Username    = 'info@benefitsmart.online';
        $mail->Password    = 'Kingsley419.';
        $mail->SMTPSecure  = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port        = 465;

        $mail->setFrom('info@nextrade.com', 'Benefit Market Trade');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = "Trade Confirmation - Trade ID: $tradeId";
        $mail->Body    = "
        <!DOCTYPE html>
        <html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Trade Confirmation</title>
        <style>
          .bg-gray-100 { background-color: #f7fafc; }
          .bg-white { background-color: #ffffff; }
          .text-gray-800 { color: #2d3748; }
          .text-gray-700 { color: #4a5568; }
          .rounded-lg { border-radius: .5rem; }
          .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05); }
          .max-w-xl { max-width: 36rem; }
          .mx-auto { margin-left:auto;margin-right:auto; }
          .p-6 { padding:1.5rem; }
          .mb-4 { margin-bottom:1rem; }
          .mt-6 { margin-top:1.5rem; }
          .text-2xl { font-size:1.5rem; }
          .font-bold { font-weight:700; }
          table { border-collapse:collapse;width:100%; }
          th,td { padding:.5rem;border:1px solid #e2e8f0; }
          th { background:#edf2f7;text-align:left; }
        </style>
        </head><body class='bg-gray-100 p-6'>
          <div class='max-w-xl mx-auto bg-white rounded-lg shadow-lg p-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4'>Trade Confirmation</h1>
            <p class='text-gray-700 mb-2'>Dear $toName,</p>
            <p class='text-gray-700 mb-4'>Your trade has been successfully placed. Details below:</p>
            <table>
              <tr><th>Trade ID</th><td>$tradeId</td></tr>
              <tr><th>Category</th><td>$trade_category</td></tr>
              <tr><th>Type</th><td>$trade_type</td></tr>
              <tr><th>Asset</th><td>$asset</td></tr>
              <tr><th>Lot Size</th><td>$lot_size</td></tr>
              <tr><th>Entry Price</th><td>$entry_price</td></tr>
              <tr><th>Amount (USD)</th><td>$amount</td></tr>
              <tr><th>Date</th><td>$trade_date</td></tr>
            </table>
            <p class='mt-6 text-gray-700'>Thank you for trading with Benefit Market Trade!</p>
            <p class='mt-4 text-gray-500 text-sm'>This is an automated email. Do not reply.</p>
          </div>
        </body></html>
        ";
        $mail->AltBody = "Trade Confirmation: ID $tradeId — Category: $trade_category, Type: $trade_type, Asset: $asset, Lot Size: $lot_size, Entry Price: $entry_price, Amount: $amount USD, Date: $trade_date.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Fetch current user info
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([ (int)$_SESSION['user_id'] ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['notification']      = "Invalid CSRF token.";
        $_SESSION['notification_type'] = "error";
        header("Location: trades.php");
        exit();
    }

    // Sanitize + validate
    $user_id        = (int) $_SESSION['user_id'];
    $trade_category = trim($_POST['trade_category'] ?? '');
    $trade_type     = trim($_POST['trade_type']     ?? '');
    $asset          = strtolower(trim($_POST['asset'] ?? ''));
    $lot_size       = filter_var($_POST['lot_size'] ?? '', FILTER_VALIDATE_FLOAT);
    $entry_price    = filter_var($_POST['entry_price'] ?? '', FILTER_VALIDATE_FLOAT);
    $amount         = filter_var($_POST['amount'] ?? '', FILTER_VALIDATE_FLOAT);
    $trade_date     = date('Y-m-d H:i:s');

    if (!$trade_category || !$trade_type || !$asset || $lot_size === false || $entry_price === false || $amount === false) {
        $_SESSION['notification']      = "Invalid input values. Please check your entries.";
        $_SESSION['notification_type'] = "error";
        header("Location: trades.php");
        exit();
    }

    if (strtolower($trade_category) === 'sell') {
        $_SESSION['notification']      = "Sell trades are unavailable at the moment.";
        $_SESSION['notification_type'] = "error";
        header("Location: trades.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Check USD balance for Buy
        if (strtolower($trade_category) === 'buy') {
            $stmtBal = $conn->prepare("SELECT usd_balance FROM users WHERE user_id = ?");
            $stmtBal->execute([$user_id]);
            $balRow  = $stmtBal->fetch(PDO::FETCH_ASSOC);
            $usd_balance = $balRow['usd_balance'] ?? 0.0;
            if ($amount > $usd_balance) {
                throw new Exception("Insufficient USD balance. Available: $" . number_format($usd_balance, 2));
            }
        }

        // Ensure user exists
        $stmtCheck = $conn->prepare("SELECT 1 FROM users WHERE user_id = ?");
        $stmtCheck->execute([$user_id]);
        if (!$stmtCheck->fetchColumn()) {
            throw new Exception("User not found.");
        }

        // Insert trade
        $trade_id = generateUniqueTradeId($conn);
        $ins = $conn->prepare("
            INSERT INTO trades (
                trade_id, user_id, trade_category, trade_type,
                asset, lot_size, entry_price, amount, trade_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $trade_id, $user_id, $trade_category, $trade_type,
            $asset, $lot_size, $entry_price, $amount, $trade_date
        ]);

        // Deduct USD for Buy
        if (strtolower($trade_category) === 'buy') {
            $upd = $conn->prepare("UPDATE users SET usd_balance = usd_balance - ? WHERE user_id = ?");
            $upd->execute([$amount, $user_id]);
        }

        $conn->commit();

        // Send confirmation email
        $mailSent = sendTradeConfirmationEmail(
            $user['email'],
            $user['fname'],
            $trade_id,
            $trade_category,
            $trade_type,
            $asset,
            $lot_size,
            $entry_price,
            $amount,
            $trade_date
        );
        if (!$mailSent) {
            error_log("Failed to send trade email for ID $trade_id");
        }

        $_SESSION['notification']      = "Trade placed successfully! (ID: $trade_id)";
        $_SESSION['notification_type'] = "success";
        header("Location: trades.php");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['notification']      = "Error: " . $e->getMessage();
        $_SESSION['notification_type'] = "error";
        header("Location: trades.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trades</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
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
      
      // Dropdowns for notifications and user menu
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
      
      // Prevent multiple form submissions
      const tradeForm = document.getElementById("tradeForm");
      if(tradeForm) {
        tradeForm.addEventListener("submit", function(){
          document.getElementById("submitBtn").disabled = true;
        });
      }
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
        <a href="login.php">
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
      <a href="login.php" class="flex items-center">
        <i class="ri-home-line text-2xl text-white"></i>
        <span class="text-2xl ml-2">Benefit Smart</span>
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
        <!-- <li>
  <a href="notifications.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
    <i class="ri-notification-line text-xl"></i>
    <span class="ml-2">Notifications</span>
  </a>
</li> -->

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
                <span class=" text-2xl ">Benefit Smart</span>
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
          WHERE user_id = $1
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
          WHERE user_id = $1
        ) AS combined
        ORDER BY activity_date DESC
        LIMIT 3
      ";

      $stmt_activity = $conn->prepare($sql);
      $stmt_activity->execute([$user_id]);
      $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

      if ($result_activity && count($result_activity) > 0) {
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
          WHERE user_id = $1
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
          WHERE user_id = $1
        ) AS combined
        ORDER BY activity_date DESC
        LIMIT 3
      ";

      $stmt_activity = $conn->prepare($sql);
      $stmt_activity->execute([$user_id]);
      $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

      if ($result_activity && count($result_activity) > 0) {
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
      <main class="p-6 space-y-8 bg-gray-50 min-h-screen">
        <!-- Trades Header and Notification -->
        <div class="welcome-message">
          <h2 class="text-3xl font-bold text-gray-800">Trades</h2>
          <?php if (!empty($notification)) : ?>
            <div class="notification mt-4 p-3 rounded <?php echo ($notification_type == 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'); ?>">
              <?php echo htmlspecialchars($notification); ?>
            </div>
          <?php endif; ?>
        </div>
        <!-- Trade Form -->
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Left Column: TradingView Widget -->
          <div class="lg:w-1/2">
            <div class="tradingview-widget-container h-[400px] [&_div.tradingview-widget-copyright]:hidden [&_a]:pointer-events-none">
              <div id="tradingview_chart" class="rounded-lg h-full"></div>
              <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
              <script type="text/javascript">
                new TradingView.widget({
                  "width": "100%",
                  "height": "400",
                  "symbol": "NASDAQ:AAPL",
                  "interval": "D",
                  "timezone": "Etc/UTC",
                  "theme": "light",
                  "style": "1",
                  "locale": "en",
                  "toolbar_bg": "#f1f3f6",
                  "enable_publishing": false,
                  "allow_symbol_change": true,
                  "container_id": "tradingview_chart"
                });
              </script>
            </div>
          </div>
          <!-- Right Column: Trade Form -->
          <div class="lg:w-1/2">
            <div class="account-summary bg-white p-4 rounded-lg shadow-md h-[400px] overflow-y-auto">
              <h3 class="text-xl font-semibold mb-3">Place Trade</h3>
              <form id="tradeForm" action="" method="post" class="space-y-3">
                <!-- Include CSRF token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div>
                  <label for="trade_category" class="block text-gray-700 text-sm">Trade Category:</label>
                  <select id="trade_category" name="trade_category" required class="w-full border border-gray-300 p-2 rounded text-sm">
                    <option value="">Select Trade Category</option>
                    <option value="Buy">Buy</option>
                    <option value="Sell">Sell</option>
                  </select>
                </div>
                <div>
                  <label for="trade_type" class="block text-gray-700 text-sm">Trade Type:</label>
                  <select id="trade_type" name="trade_type" required onchange="updateAssets()" class="w-full border border-gray-300 p-2 rounded text-sm">
                    <option value="">Select Trade Type</option>
                    <option value="forex">Forex</option>
                    <option value="crypto">Crypto</option>
                    <option value="stocks">Stocks</option>
                  </select>
                </div>
                <div>
                  <label for="asset" class="block text-gray-700 text-sm">Asset:</label>
                  <select id="asset" name="asset" required class="w-full border border-gray-300 p-2 rounded text-sm">
                    <option value="">Select an Asset</option>
                  </select>
                </div>
                <div>
                  <label for="lot_size" class="block text-gray-700 text-sm">Lot Size:</label>
                  <input type="number" step="0.01" id="lot_size" name="lot_size" required class="w-full border border-gray-300 p-2 rounded text-sm" placeholder="E.g., 0.01 for micro lot">
                  <small class="text-gray-500">A standard lot is 100,000 units, mini lot is 10,000, and micro lot is 1,000.</small>
                </div>
                <div>
                  <label for="entry_price" class="block text-gray-700 text-sm">Entry Price:</label>
                  <input type="number" step="0.01" id="entry_price" name="entry_price" required class="w-full border border-gray-300 p-2 rounded text-sm" placeholder="Enter the price per unit">
                  <small class="text-gray-500">Enter the price per unit. (No conversion calculation is done.)</small>
                </div>
                <div>
                  <label for="amount" class="block text-gray-700 text-sm">Amount (in USD):</label>
                  <input type="number" step="0.01" id="amount" name="amount" required class="w-full border border-gray-300 p-2 rounded text-sm" placeholder="Amount in USD">
                  <small class="text-gray-500">Enter the USD amount for this trade.</small>
                </div>
                <div>
                  <input type="submit" id="submitBtn" value="Place Trade" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded cursor-pointer text-sm">
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- JavaScript for updating asset options -->
        <script>
          const assetOptions = {
            forex: ["EUR/USD", "GBP/USD", "USD/JPY"],
            crypto: ["Bitcoin", "Ethereum", "Ripple"],
            stocks: ["Apple", "Tesla", "Amazon"]
          };

          function updateAssets() {
            const tradeType = document.getElementById("trade_type").value;
            const assetSelect = document.getElementById("asset");
            assetSelect.innerHTML = '<option value="">Select an Asset</option>';
            if (tradeType && assetOptions[tradeType]) {
              assetOptions[tradeType].forEach(asset => {
                const option = document.createElement("option");
                option.value = asset.toLowerCase(); // normalized value for comparison
                option.textContent = asset;
                assetSelect.appendChild(option);
              });
            }
          }
        </script>
        <!-- Trade History Section -->
        <div class="mt-8 max-w-6xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
          <h3 class="text-2xl font-bold text-white mb-4">Trade History</h3>
          <?php include 'trade_history.php'; ?>
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
