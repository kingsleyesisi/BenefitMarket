<?php
session_start();
include_once 'config.php';  // provides $conn (PDO) to Neon Postgres

// Enable error reporting (for debugging purposes; remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);



// Your ExchangeRate-API key
define('FX_API_KEY', '98c2c6e024e9d0446a5c3c59');


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// CSRF Token Setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

// Ensure the database connection exists
if (!isset($conn)) {
    die("Database connection not established.");
}

try {
    // Check user's USD balance from the users table
    $sql_balance = "SELECT profit FROM users WHERE user_id = :uid";
    $stmt = $conn->prepare($sql_balance);
    $stmt->execute([':uid' => $user_id]);
    $usd_balance = $stmt->fetchColumn();
    if ($usd_balance === false) {
        throw new Exception("Could not retrieve USD balance.");
    }
} catch (Exception $e) {
    die("Balance check error: " . $e->getMessage());
}


try {
  // Get user data
  $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
  $stmt->execute([':user_id' => $_SESSION['user_id']]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Pull fields out
  $userId          = $row['user_id'];
  $account_id      = $row['account_id']       ?? 0;
  $usd_balance     = $row['profit']      ?? 0.0;
  $account_type    = $row['account_type']     ?? 'Standard';
  $userCurrency    = $row['currency']         ?? 'USD';
  $tradingBotStatus= $row['trading_bot']      ?? '';

  // 2) Get (and cache) live USD→userCurrency rate
  function getFxRate($base, $target, $apiKey, $cacheDir = '/tmp', $ttl = 1800) {
      $cacheFile = "{$cacheDir}/fx_{$base}_{$target}.json";
      // serve from cache if still fresh
      if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $ttl) {
          $j = file_get_contents($cacheFile);
      } else {
          $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/{$base}/{$target}";
          $ch = curl_init($url);
          curl_setopt_array($ch, [
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_TIMEOUT        => 5,
              CURLOPT_FAILONERROR    => true,
          ]);
          $j = curl_exec($ch);
          if (!$j) {
              // on failure, fall back to cache if exists, else return 1.0
              if (file_exists($cacheFile)) {
                  $j = file_get_contents($cacheFile);
              } else {
                  return 1.0;
              }
          }
          curl_close($ch);
          file_put_contents($cacheFile, $j);
      }

      $data = json_decode($j, true);
      if (isset($data['conversion_rate'])) {
          return (float)$data['conversion_rate'];
      }
      return 1.0;
  }

  $fxRate = getFxRate('USD', $userCurrency, FX_API_KEY);

  // 3) Format helper
  function formatFx($usdAmount, $rate, $currency) {
      return number_format($usdAmount * $rate, 2) . " {$currency}";
  }

  // 4) Gather dashboard metrics
  // Total Trades
  $stmt = $conn->prepare("SELECT COUNT(*) FROM trades WHERE user_id = :user_id");
  $stmt->execute([':user_id' => $userId]);
  $totalTrades = $stmt->fetchColumn() ?? 0;

  // Verification Status
  $stmt = $conn->prepare("
      SELECT status FROM verification 
      WHERE user_id = :user_id 
      ORDER BY id DESC LIMIT 1
  ");
  $stmt->execute([':user_id' => $userId]);
  $verificationStatus = $stmt->fetch(PDO::FETCH_ASSOC)['status'] ?? 'UNVERIFIED';

  // Total Withdrawal
  $stmt = $conn->prepare("
      SELECT COALESCE(SUM(amount), 0) 
      FROM withdrawals 
      WHERE user_id = $1 
      AND status = 'Approved'
  ");
  $stmt->execute([$userId]);
  $totalWithdrawal = $stmt->fetchColumn() ?? 0;

  // Total Traded Amount
  $stmt = $conn->prepare("
      SELECT COALESCE(SUM(amount),0) 
      FROM trades 
      WHERE user_id = :user_id
  ");
  $stmt->execute([':user_id' => $userId]);
  $totalAmount = $stmt->fetchColumn() ?? 0;

  // Wins & Losses
  $stmt = $conn->prepare("
      SELECT COUNT(*) 
      FROM trades 
      WHERE user_id = :user_id 
      AND trading_results = 'win'
  ");
  $stmt->execute([':user_id' => $userId]);
  $totalWins = $stmt->fetchColumn() ?? 0;

  $stmt = $conn->prepare("
      SELECT COUNT(*) 
      FROM trades 
      WHERE user_id = :user_id 
      AND trading_results = 'loss'
  ");
  $stmt->execute([':user_id' => $userId]);
  $totalLosses = $stmt->fetchColumn() ?? 0;

} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}

// Process the final withdrawal submission (after modal confirmation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crypto_type'])) {
  // Validate CSRF token
  if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token.");
  }

  $crypto_type = $_POST['crypto_type'];
  $amount      = floatval($_POST['amount']);

  // Convert the amount from user currency to USD
  $amountInUSD = $amount / $fxRate;

  // Check if the user has sufficient USD balance
  if ($amountInUSD > $usd_balance) {
    die("Insufficient USD balance for this withdrawal.");
  }

  // For non-bank withdrawals, check that a wallet address is provided.
  if ($crypto_type !== "BANK") {
    $wallet_address = trim($_POST['wallet_address']);
    if ($wallet_address === "") {
      die("Wallet address is required for crypto withdrawals.");
    }
  }

  // Generate a random withdrawal ID with 8 to 10 digits
  $withdrawal_id = random_int(10000000, 2147483647);

  try {
    // Build the INSERT statement and parameters
    if ($crypto_type === "BANK") {
      $bank_name      = trim($_POST['bank_name']);
      $account_number = trim($_POST['account_number']);
      $routing_number = trim($_POST['routing_number']);

      $sql = "
        INSERT INTO withdrawals
          (withdrawal_id, user_id, crypto_type, amount, wallet_address, status, bank_name, account_number, routing_number)
        VALUES
          (:wid, :uid, :ctype, :amt, '', 'pending', :bname, :acct, :rout)
      ";
      $params = [
        ':wid'   => $withdrawal_id,
        ':uid'   => $user_id,
        ':ctype' => $crypto_type,
        ':amt'   => $amount,
        ':bname' => $bank_name,
        ':acct'  => $account_number,
        ':rout'  => $routing_number,
      ];
    } else {
      // Crypto withdrawal
      $wallet_address = trim($_POST['wallet_address']);

      $sql = "
        INSERT INTO withdrawals
          (withdrawal_id, user_id, crypto_type, amount, wallet_address, status, bank_name, account_number, routing_number)
        VALUES
          (:wid, :uid, :ctype, :amt, :waddr, 'pending', '', '', '')
      ";
      $params = [
        ':wid'   => $withdrawal_id,
        ':uid'   => $user_id,
        ':ctype' => $crypto_type,
        ':amt'   => $amount,
        ':waddr' => $wallet_address,
      ];
    }

    // Execute the INSERT
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
      // Deduct the withdrawal amount from the user's balance
      $updateBalanceSql = "UPDATE users SET profit = profit - :amt WHERE user_id = :uid";
      $updateStmt = $conn->prepare($updateBalanceSql);
      $updateStmt->execute([':amt' => $amountInUSD, ':uid' => $user_id]);

      header("Location: withdraw_thankyou.php");
      exit();
    } else {
      $message = "Failed to submit withdrawal request.";
    }

  } catch (Exception $e) {
    die("Withdrawal error: " . $e->getMessage());
  }
}

// Retrieve withdrawal history for this user
try {
  $withdrawals = [];
  $historySql = "
    SELECT
      withdrawal_id,
      user_id,
      crypto_type,
      amount,
      wallet_address,
      status,
      created_at,
      bank_name,
      account_number,
      routing_number
    FROM withdrawals
    WHERE user_id = :uid
    ORDER BY created_at DESC
  ";
  $stmt = $conn->prepare($historySql);
  $stmt->execute([':uid' => $user_id]);
  $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
  die("History fetch error: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Withdrawal</title>

   <!-- Include jsPDF & jsPDF-AutoTable Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <!-- Tailwind CSS -->
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  
  <style>
    .menu-items { padding: 1rem; }
    .nav-links li { margin: 1rem 0; }
    .nav-links li a { display: flex; align-items: center; gap: 0.5rem; }
    .logout-mode li { margin-top: 1rem; }
    @media (max-width: 1024px) { .desktop-nav { display: none; } }
  </style>
  <!-- Custom CSS for DataTables to ensure white text is visible -->
<style>
  /* Ensure DataTables info and filter texts are white */
  .dataTables_wrapper .dataTables_length label,
  .dataTables_wrapper .dataTables_filter label,
  .dataTables_wrapper .dataTables_info {
    color: white !important;
  }
  /* Style pagination buttons */
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    color: white !important;
    background-color: #2d3748 !important; /* Tailwind gray-800 */
    border: 1px solid #4a5568 !important; /* Tailwind gray-700 */
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    margin: 0 0.1rem;
    cursor: pointer;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #4a5568 !important; /* Tailwind gray-700 */
    color: white !important;
  }
  /* Optionally, adjust hover state */
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #4a5568 !important;
    color: white !important;
  }
</style>
  
  <!-- jQuery (full version) and DataTables JS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Sidebar toggle for mobile
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
      
      // Toggle bank details vs crypto wallet address based on selection
      document.getElementById("crypto_type").addEventListener("change", function(){
          if(this.value === "BANK"){
              document.getElementById("wallet_address_field").classList.add("hidden");
              document.getElementById("bank_details").classList.remove("hidden");
              document.getElementById("wallet_address").required = false;
              document.getElementById("bank_name").required = true;
              document.getElementById("account_number").required = true;
              document.getElementById("routing_number").required = true;
          } else {
              document.getElementById("wallet_address_field").classList.remove("hidden");
              document.getElementById("bank_details").classList.add("hidden");
              document.getElementById("wallet_address").required = true;
              document.getElementById("bank_name").required = false;
              document.getElementById("account_number").required = false;
              document.getElementById("routing_number").required = false;
          }
      });
      
      // Intercept form submission to show confirmation modal
      const withdrawForm = document.getElementById("withdrawForm");
      withdrawForm.addEventListener("submit", function(e) {
          e.preventDefault(); // prevent immediate submission
          
          const cryptoType = document.getElementById("crypto_type").value;
          const amount = document.getElementById("amount").value;
          let detailsHTML = "";
          
          if(cryptoType === "BANK"){
              const bankName = document.getElementById("bank_name").value;
              const accountNumber = document.getElementById("account_number").value;
              const routingNumber = document.getElementById("routing_number").value;
              detailsHTML = `<p><strong>Bank Name:</strong> ${bankName}</p>
                             <p><strong>Account Number:</strong> ${accountNumber}</p>
                             <p><strong>Routing Number:</strong> ${routingNumber}</p>`;
          } else {
              const walletAddress = document.getElementById("wallet_address").value;
              detailsHTML = `<p><strong>Wallet Address:</strong> ${walletAddress}</p>`;
          }
          document.getElementById("modalCrypto").innerText = cryptoType;
          document.getElementById("modalAmount").innerText = amount;
          document.getElementById("modalDetails").innerHTML = detailsHTML;
          document.getElementById("withdrawModal").classList.remove("hidden");
      });
      
      // Modal confirm button: show spinner for 3 seconds then submit the form
      document.getElementById("modalConfirm").addEventListener("click", function() {
          this.disabled = true;
          document.getElementById("modalSpinner").classList.remove("hidden");
          setTimeout(function(){
             withdrawForm.submit();
          }, 3000);
      });
      
      // Modal close button: hide modal and re-enable confirm button
      document.getElementById("modalClose").addEventListener("click", function(){
         document.getElementById("withdrawModal").classList.add("hidden");
         document.getElementById("modalConfirm").disabled = false;
         document.getElementById("modalSpinner").classList.add("hidden");
      });
      
      // Initialize DataTable for withdrawal history table
      $('#withdrawalTable').DataTable({
         "pageLength": 5,
         "lengthMenu": [5, 10, 20],
         "paging": true
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
      <!-- <li>
        <a href="verification.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-shield-check-line text-xl"></i>
          <span class="ml-2">Verify Account</span>
        </a>
      </li> -->
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
        <!-- <li>
          <a
            href="verification.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-shield-check-line text-xl"></i>
            <span class="ml-2">Verify Account</span>
          </a>
        </li> -->
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
      <!-- Main Content -->
      <main class="p-6 space-y-8 bg-gray-50 min-h-screen">
        <!-- Withdrawal Form Section -->
        <div class="max-w-4xl mx-auto mt-8 p-6 bg-gray-800 rounded-xl shadow-xl">
          <h2 class="text-2xl font-bold mb-4 text-white">Withdrawal</h2>
          <?php if($message): ?>
            <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>
          <form id="withdrawForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- Withdrawal Type -->
            <div class="mb-4">
              <label for="crypto_type" class="block text-white mb-1">Select Withdrawal Type:</label>
              <select name="crypto_type" id="crypto_type" class="w-full p-2 text-white bg-gray-700 rounded" required>
                <option value="">-- Select Type --</option>
                <option value="BTC">BTC (Crypto)</option>
                <option value="ETH">ETH (Crypto)</option>
                <option value="USDT">USDT (Crypto)</option>
                <option value="SOLANA">SOLANA (Crypto)</option>
                <option value="TON">TON (Crypto)</option>
                <option value="XRP">XRP (Crypto)</option>
                <option value="BANK">Bank Withdrawal</option>
              </select>
            </div>
            <!-- Amount -->
            <div class="mb-4">
                <label for="amount" class="block text-white mb-1">Amount:</label>
                <div class="flex items-center justify-between">
                  <span class="text-white">User Balance:</span>
                  <span class="text-green-400">$<?= formatFx($usd_balance, $fxRate, $userCurrency) ?></span>
                </div>
              <input type="text" name="amount" id="amount" placeholder="Enter amount" class="w-full p-2 text-white bg-gray-700 rounded" required>
            </div>
            <!-- Wallet Address for Crypto Withdrawal -->
            <div class="mb-4" id="wallet_address_field">
              <label for="wallet_address" class="block text-white mb-1">Wallet Address:</label>
              <input type="text" name="wallet_address" id="wallet_address" placeholder="Enter your wallet address" class="w-full p-2 text-white bg-gray-700 rounded" required>
            </div>
            <!-- Bank Details for Bank Withdrawal (hidden by default) -->
            <div id="bank_details" class="hidden">
              <div class="mb-4">
                <label for="bank_name" class="block text-white mb-1">Bank Name:</label>
                <input type="text" name="bank_name" id="bank_name" placeholder="Enter your bank name" class="w-full p-2 text-white bg-gray-700 rounded">
              </div>
              <div class="mb-4">
                <label for="account_number" class="block text-white mb-1">Account Number:</label>
                <input type="text" name="account_number" id="account_number" placeholder="Enter your account number" class="w-full p-2 text-white bg-gray-700 rounded">
              </div>
              <div class="mb-4">
                <label for="routing_number" class="block text-white mb-1">Routing Number:</label>
                <input type="text" name="routing_number" id="routing_number" placeholder="Enter your routing number" class="w-full p-2 text-white bg-gray-700 rounded">
              </div>
            </div>
            <!-- Visible Submit Button to open confirmation modal -->
            <div>
              <button type="submit" id="openModal" class="w-full bg-blue-600 hover:bg-blue-500 py-2 rounded transition-colors">
                Request Withdrawal
              </button>
            </div>
          </form>
        </div>

 <!-- Withdrawal History Section -->
<section class="mt-8 max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4 text-white">Withdrawal History</h1>
        <div class="overflow-x-auto">
            <table id="withdrawalTable" class="min-w-full text-left text-sm text-white">
                <thead class="border-b border-gray-700">
                    <tr>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Withdrawal ID</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Type</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Amount</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Details</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Status</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm text-white">Requested At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($withdrawals)): ?>
                        <?php foreach($withdrawals as $withdrawal): ?>
                            <tr class="border-b border-gray-700 cursor-pointer hover:bg-gray-700">
                                <td class="py-3 px-4"><?= htmlspecialchars($withdrawal['withdrawal_id']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($withdrawal['crypto_type']) ?></td>
                                <td class="py-3 px-4">$<?= htmlspecialchars($withdrawal['amount']) ?></td>
                                <td class="py-3 px-4">
                                    <?= $withdrawal['crypto_type'] === "BANK" 
                                        ? "Bank: " . htmlspecialchars($withdrawal['bank_name']) . " | Acc#: " . htmlspecialchars($withdrawal['account_number'])
                                        : htmlspecialchars($withdrawal['wallet_address']) ?>
                                </td>
                                <td class="py-3 px-4"><?= htmlspecialchars($withdrawal['status']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($withdrawal['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(empty($withdrawals)): ?>
            <div class="text-center text-gray-300 mt-4">No withdrawals found.</div>
        <?php endif; ?>
        <div id="pagination" class="mt-4 flex justify-center space-x-2"></div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Disable DataTables error reporting
    if(typeof $.fn.DataTable !== 'undefined') {
        $.fn.dataTable.ext.errMode = 'none';
    }

    const table = document.getElementById('withdrawalTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const paginationDiv = document.getElementById('pagination');
    const hasData = rows.length > 0;

    // Only initialize DataTables if we have data
    if(hasData && typeof $.fn.DataTable !== 'undefined') {
        $('#withdrawalTable').DataTable({
            paging: false,
            info: false,
            searching: false,
            language: {
                emptyTable: "" // Disable default empty message
            }
        });
    }

    // Custom pagination logic
    if(hasData) {
        const rowsPerPage = 5;
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        function showPage(page) {
            rows.forEach((row, index) => {
                row.classList.toggle('hidden', 
                    index < (page - 1) * rowsPerPage || 
                    index >= page * rowsPerPage
                );
            });
        }

        function createPagination() {
            paginationDiv.innerHTML = '';
            for(let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = `px-4 py-2 rounded ${i === 1 ? 'bg-gray-700' : 'bg-gray-800'}`;
                btn.addEventListener('click', () => {
                    paginationDiv.querySelectorAll('button').forEach(b => 
                        b.className = 'px-4 py-2 rounded bg-gray-800'
                    );
                    btn.className = 'px-4 py-2 rounded bg-gray-700';
                    showPage(i);
                });
                paginationDiv.appendChild(btn);
            }
        }

        createPagination();
        showPage(1);
    } else {
        paginationDiv.style.display = 'none';
    }

    // Click handler for rows
    if (hasData) {
      rows.forEach(row => {
        row.addEventListener('click', () => {
          const cells = row.querySelectorAll('td');
          const withdrawalId = cells[0].innerText;
          const type = cells[1].innerText;
          const amount = cells[2].innerText;
          const details = cells[3].innerText;
          const status = cells[4].innerText;
          const requestedAt = cells[5].innerText;

          // Populate modal with the selected row's data
          document.getElementById('modalCrypto').innerText = type;
          document.getElementById('modalAmount').innerText = amount;
          document.getElementById('modalDetails').innerHTML = `<p>${details}</p>`;
          document.getElementById('modalStatus').innerText = status;
          document.getElementById('modalWithdrawalID').innerText = withdrawalId;
          document.getElementById('modalRequestedAt').innerText = requestedAt;

          // Show the modal
          document.getElementById('withdrawModal').classList.remove('hidden');
        });
      });
    }
});

    if(totalRows > 0) {
        createPagination();
        showPage(1);
        attachRowClickEvents();
    }
<!--});-->
<!--document.getElementById("downloadPdf").addEventListener("click", function() {-->
<!--  const { jsPDF } = window.jspdf;-->
<!--  const doc = new jsPDF({ unit: "pt", format: "a4" });-->
<!--  const pageWidth = doc.internal.pageSize.getWidth();-->
<!--  const pageHeight = doc.internal.pageSize.getHeight();-->
  const margin = 20; // Minimal margins
<!--  const usableWidth = pageWidth - 2 * margin;-->
  
  // --- Main Header Section ---
  // Draw the dark header rectangle
  doc.setFillColor(51, 51, 51);  // Dark gray (#333333)
<!--  doc.rect(margin, 30, usableWidth, 60, "F");-->
  
  // Set up white header text inside the rectangle
<!--  doc.setFont("Helvetica", "bold");-->
<!--  doc.setFontSize(24);-->
  doc.setTextColor(255, 255, 255);  // White text
  
  // Adjust the y coordinate to position the text within the header rectangle (centered vertically)
<!--  doc.text("Benefit Market Trade", margin + 20, 60);-->
<!--  doc.setFontSize(18);-->
<!--  doc.text("WITHDRAWAL", pageWidth - margin - 200, 60);-->
  
  // Divider below main header
<!--  doc.setLineWidth(1);-->
<!--  doc.setDrawColor(150, 150, 150);-->
<!--  doc.line(margin, 90, pageWidth - margin, 90);-->
  
  // --- Invoice Information Section ---
<!--  let sectionY = 110;-->
<!--  doc.setFillColor(51, 51, 51);-->
<!--  doc.rect(margin, sectionY - 14, usableWidth, 22, "F");-->
<!--  doc.setFont("Helvetica", "bold");-->
<!--  doc.setFontSize(14);-->
<!--  doc.setTextColor(255, 255, 255);-->
<!--  doc.text("Invoice Information", margin + 5, sectionY);-->
  sectionY += 20; // Gap before table
  
  // Generate a random 8-character transaction reference
<!--  let transactionRef = Math.random().toString(36).substring(2, 10).toUpperCase();-->
  
<!--  doc.setFont("Helvetica", "normal");-->
<!--  doc.setFontSize(12);-->
<!--  doc.autoTable({-->
<!--    startY: sectionY,-->
<!--    theme: "grid",-->
<!--    headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },-->
<!--    bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },-->
<!--    columnStyles: {-->
<!--      0: { halign: "right", fontStyle: "bold" },-->
<!--      1: { halign: "left" }-->
<!--    },-->
<!--    margin: { left: margin, right: margin },-->
<!--    tableWidth: usableWidth,-->
<!--    body: [-->
<!--      ["Withdrawal No", document.getElementById("modalWithdrawalID").innerText],-->
<!--      ["Date", document.getElementById("modalRequestedAt").innerText],-->
<!--      ["Withdrawal Method", document.getElementById("modalType").innerText],-->
<!--      ["Transaction Ref", transactionRef]-->
<!--    ]-->
<!--  });-->
  
  // --- Withdrawal Details Section ---
  let afterInvoiceY = doc.lastAutoTable.finalY + 100; // 100pt gap
<!--  doc.setFillColor(51, 51, 51);-->
<!--  doc.rect(margin, afterInvoiceY - 14, usableWidth, 22, "F");-->
<!--  doc.setFont("Helvetica", "bold");-->
<!--  doc.setFontSize(14);-->
<!--  doc.setTextColor(255, 255, 255);-->
<!--  doc.text("Withdrawal Details", margin + 5, afterInvoiceY);-->
  afterInvoiceY += 20; // Gap before table
  
<!--  doc.setFont("Helvetica", "normal");-->
<!--  doc.setFontSize(12);-->
<!--  doc.autoTable({-->
<!--    startY: afterInvoiceY,-->
<!--    theme: "grid",-->
<!--    headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },-->
<!--    bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },-->
<!--    columnStyles: {-->
<!--      0: { halign: "right", fontStyle: "bold" },-->
<!--      1: { halign: "left" }-->
<!--    },-->
<!--    margin: { left: margin, right: margin },-->
<!--    tableWidth: usableWidth,-->
<!--    body: [-->
<!--      ["Crypto Type", document.getElementById("modalType").innerText],-->
<!--      ["Withdrawal Amount", document.getElementById("modalAmount").innerText],-->
<!--      ["Status", document.getElementById("modalStatus").innerText]-->
<!--    ]-->
<!--  });-->
  
  // --- Additional Information Section ---
  let afterDetailsY = doc.lastAutoTable.finalY + 100; // 100pt gap
<!--  doc.setFillColor(51, 51, 51);-->
<!--  doc.rect(margin, afterDetailsY - 14, usableWidth, 22, "F");-->
<!--  doc.setFont("Helvetica", "bold");-->
<!--  doc.setFontSize(14);-->
<!--  doc.setTextColor(255, 255, 255);-->
<!--  doc.text("Additional Information", margin + 5, afterDetailsY);-->
  afterDetailsY += 20; // Gap before table
  
<!--  doc.setFont("Helvetica", "normal");-->
<!--  doc.setFontSize(12);-->
  // For withdrawals, the "Details" field may show bank info or wallet address
<!--  let detailsText = document.getElementById("modalDetails").innerText;-->
<!--  doc.autoTable({-->
<!--    startY: afterDetailsY,-->
<!--    theme: "grid",-->
<!--    headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },-->
<!--    bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },-->
<!--    columnStyles: {-->
<!--      0: { halign: "right", fontStyle: "bold" },-->
<!--      1: { halign: "left" }-->
<!--    },-->
<!--    margin: { left: margin, right: margin },-->
<!--    tableWidth: usableWidth,-->
<!--    body: [-->
<!--      ["Details", detailsText],-->
<!--      ["Note", "Please keep this receipt for your records. For discrepancies, contact support@benefitsmart.online."]-->
<!--    ]-->
<!--  });-->
  
  // --- Footer Section ---
<!--  doc.setFont("Helvetica", "italic");-->
<!--  doc.setFontSize(10);-->
  doc.setTextColor(0, 0, 0);  // Black text for footer
<!--  doc.text("Thank you for using Benefit Market Trade!", pageWidth / 2, pageHeight - 40, { align: "center" });-->
  
  // Save the PDF with a dynamic filename
<!--  doc.save("withdrawal_receipt_" + document.getElementById("modalWithdrawalID").innerText + ".pdf");-->
<!--});-->
</script>

      </main>
    </div>
  </div>
  
  <!-- Confirmation Modal (Tailwind CSS based) -->
  <div id="withdrawModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-gray-800 text-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
      <button id="modalClose" type="button" class="absolute top-3 right-3 text-gray-300 hover:text-white">
        <i class="ri-close-line text-2xl"></i>
      </button>
      <h2 class="text-2xl font-bold mb-4">Confirm Withdrawal</h2>
      <p class="mb-4">Please review your withdrawal details before confirming:</p>
      <p><strong>Type:</strong> <span id="modalCrypto"></span></p>
      <p><strong>Amount ($):</strong> <span id="modalAmount"></span></p>
      <div id="modalDetails"></div>
      <!-- Spinner inside the modal (hidden by default) -->
      <div id="modalSpinner" class="hidden flex items-center justify-center my-4">
        <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
      </div>
      <button id="modalConfirm" class="w-full bg-green-600 hover:bg-green-500 py-2 rounded transition-colors">
        Confirm Withdrawal
      </button>
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
