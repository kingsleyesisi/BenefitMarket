<?php
session_start();
include_once 'config.php';  // provides a working $conn (PDO) for Neon Postgres

// 1. Ensure the user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Fetch deposit history for the logged-in user.
$user_id = $_SESSION['user_id'];

// Use a named placeholder — PDO will handle quoting & types for you.
$sql = "
    SELECT
        deposit_id,
        user_id,
        crypto_type,
        amount,
        status,
        created_at
    FROM deposits
    WHERE user_id = :user_id
    ORDER BY created_at DESC
";

try {
    $stmt = $conn->prepare($sql);
    // Bind and execute in one go:
    $stmt->execute([':user_id' => $user_id]);
    // Fetch all rows as associative arrays:
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production you’d log this instead of exposing details:
    die("Query failed: " . $e->getMessage());
}

// 3. Ensure a CSRF token exists.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -- At this point you have:
//    • $deposits: an array of the user's deposit rows
//    • $_SESSION['csrf_token']: your per-session CSRF token
// Pass these into your view/template for rendering below.
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Benefit Market Trade - Deposit</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Feather Icons -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js"></script>
  <!-- jsPDF & AutoTable (for receipt download) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  
    <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
<body class="bg-gray-100">
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
      <li>
  <a href="notifications.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
    <i class="ri-notification-line text-xl"></i>
    <span class="ml-2">Notifications</span>
  </a>
</li>

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
        <a href="verification.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-shield-check-line text-xl"></i>
          <span class="ml-2">Verify Account</span>
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
            href="verification.php"
            class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors"
          >
            <i class="ri-shield-check-line text-xl"></i>
            <span class="ml-2">Verify Account</span>
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


  <main class="p-6 space-y-8">
    <!-- Deposit Form Section -->
    <div class="max-w-4xl mx-auto mt-8 p-6 bg-gray-800 rounded-xl shadow-xl">
      <h2 class="text-2xl font-bold mb-4 text-white">Deposit</h2>
      <form id="depositForm" class="space-y-4">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <!-- Crypto Type Dropdown -->
        <div>
          <label for="crypto_type" class="block mb-1 text-white">Select Crypto:</label>
          <select name="crypto_type" id="crypto_type" class="text-white w-full p-2 bg-gray-700 rounded" required>
            <option value="">-- Select Crypto --</option>
            <option value="BTC">BTC</option>
            <option value="ETH">ETH</option>
            <option value="USDT">USDT(ERC20)</option>
            <option value="SOLANA">SOLANA</option>
            <option value="TON">TON</option>
            <option value="XRP">XRP</option>
          </select>
        </div>
        <!-- Deposit Amount -->
        <div>
          <label for="amount" class="block mb-1 text-white">Amount($):</label>
          <input type="text" name="amount" id="amount" placeholder="Enter amount" class="w-full p-2 bg-gray-700 rounded text-white" required>
        </div>
        <!-- Submit Button -->
        <div>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-2 rounded text-white">
            Submit Payment
          </button>
        </div>
      </form>
    </div>
    
    <!--Links to purchase coins-->
    
  <div class="mt-10 pt-6 border-t border-gray-700">
  <h3 class="text-2xl font-extrabold mb-4 text-black tracking-wide">Purchase Crypto</h3>
  <p class="text-gray-400 text-sm mb-6">
    If you don’t have crypto yet, pick one of these trusted exchanges:
  </p>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
     <!-- Paybis Card -->
    <div class="p-1 bg-gradient-to-r from-indigo-400 to-blue-500 rounded-2xl hover:from-blue-500 hover:to-indigo-400 transition-colors duration-300">
      <a href="https://paybis.com/" target="_blank" rel="noopener noreferrer"
         class="flex items-center justify-center bg-gray-900 bg-opacity-30 backdrop-blur-sm rounded-xl p-5 transform hover:-translate-y-1 hover:scale-105 transition-all duration-300"
         aria-label="Buy Crypto on Paybis">
        <svg version="1.0" id="katman_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
          viewBox="0 0 600 400" style="enable-background:new 0 0 600 400;" xml:space="preserve" class="w-12 h-12 scale-[1.7]">
          <style type="text/css">
            .st0{fill-rule:evenodd;clip-rule:evenodd;fill:#5F70DB;}
            .st1{fill-rule:evenodd;clip-rule:evenodd;}
          </style>
          <symbol id="logo_new" viewBox="-50.1 -12.7 100.2 25.4">
            <path class="st0" d="M-37.9-12.7v25.4c-6.6,0-12.2-5.8-12.2-12.7C-50.1-6.9-44.5-12.7-37.9-12.7z"/>
            <path d="M7.8,1.3L5.1-6h-5L5.4,6.3l-2.2,6h4.4L14.8-6h-4.4L7.8,1.3z"/>
            <path d="M49.7-2c-0.3-3.3-2.6-4.4-5.9-4.4c-3.1,0-5.8,1.5-5.8,4.5c0,2.9,1.5,3.9,5.1,4.4c1.9,0.3,2.5,0.6,2.5,1.2
              c0,0.7-0.5,1.1-1.6,1.1c-1.3,0-1.8-0.6-1.9-1.5h-4.2C37.9,6.4,40.2,8,44,8c3.7,0,6.1-1.5,6.1-4.7c0-2.9-1.8-3.9-5.5-4.4
              c-1.6-0.2-2.3-0.5-2.3-1.1c0-0.6,0.5-1.1,1.5-1.1c1.1,0,1.5,0.4,1.7,1.4H49.7z"/>
            <path d="M36.5,7.7V-6h-4.6V7.7H36.5z"/>
            <path d="M36.7-9.8c0-1.4-1.1-2.4-2.5-2.4c-1.4,0-2.5,1.1-2.5,2.4c0,1.4,1.1,2.4,2.5,2.4C35.6-7.3,36.7-8.4,36.7-9.8z"/>
            <path class="st1" d="M-20.1-6.4c3.1,0,5.7,2.4,5.7,7.1v0.2c0,4.7-2.5,7.1-5.7,7.1c-2,0-3.5-1-4.2-2.3v6.6h-4.6V-6h4.6v2
              C-23.5-5.3-22.1-6.4-20.1-6.4z M-24.4,0.7c0-2.3,1-3.5,2.7-3.5c1.6,0,2.7,1.2,2.7,3.6v0.2c0,2.3-0.9,3.5-2.7,3.5
              c-1.7,0-2.7-1.2-2.7-3.5V0.7z"/>
            <path class="st1" d="M-6.4-6.4c3.7,0,6.1,1.5,6.1,5.2v8.9h-4.5V6.1c-0.6,1-1.8,1.9-4,1.9c-2.4,0-4.6-1.2-4.6-4.1
              c0-3.2,2.7-4.5,7.2-4.5h1.3v-0.3c0-1.3-0.3-2.2-1.8-2.2c-1.3,0-1.7,0.8-1.8,1.6h-4.3C-12.6-4.8-10-6.4-6.4-6.4z M-6.1,2.1h1.2v0.8
              c0,1.2-1,1.9-2.3,1.9c-1.1,0-1.6-0.5-1.6-1.3C-8.8,2.4-7.9,2.1-6.1,2.1z"/>
            <path class="st1" d="M20.6-12.1V-4c0.8-1.3,2.2-2.4,4.2-2.4c3.1,0,5.7,2.4,5.7,7.1v0.2c0,4.7-2.5,7.1-5.7,7.1c-2,0-3.5-1-4.2-2.4
              v2.1H16v-19.8H20.6z M20.5,0.7c0-2.3,1-3.5,2.7-3.5c1.6,0,2.7,1.2,2.7,3.6v0.2c0,2.3-0.9,3.5-2.7,3.5c-1.7,0-2.7-1.2-2.7-3.5V0.7z"/>
          </symbol>
          <g>
            <use xlink:href="#logo_new" width="100.2" height="25.4" x="-50.1" y="-12.7" transform="matrix(4.98 0 0 4.98 294.5 210.3528)" style="overflow:visible;"/>
          </g>
        </svg>
      </a>
    </div>

    <!-- Binance Card with inline SVG -->
    <div class="p-1 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl hover:from-orange-500 hover:to-yellow-400 transition-colors duration-300">
      <a href="https://www.binance.com/" target="_blank" rel="noopener noreferrer"
         class="flex items-center bg-gray-900 bg-opacity-30 backdrop-blur-sm rounded-xl p-5 transform hover:-translate-y-1 hover:scale-105 transition-all duration-300"
         aria-label="Buy Crypto on Binance">
        <!-- Inline Binance SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2500 2500" class="w-12 h-12 mr-4">
          <defs><style>.cls-1{fill:#f3ba2f;}</style></defs>
          <g><g>
            <path class="cls-1" d="M764.48,1050.52,1250,565l485.75,485.73,282.5-282.5L1250,0,482,768l282.49,282.5M0,1250,282.51,967.45,565,1249.94,282.49,1532.45Zm764.48,199.51L1250,1935l485.74-485.72,282.65,282.35-.14.15L1250,2500,482,1732l-.4-.4,282.91-282.12M1935,1250.12l282.51-282.51L2500,1250.1,2217.5,1532.61Z"/>
            <path class="cls-1" d="M1536.52,1249.85h.12L1250,963.19,1038.13,1175h0l-24.34,24.35-50.2,50.21-.4.39.4.41L1250,1536.81l286.66-286.66.14-.16-.26-.14"/>
          </g></g>
        </svg>
        <span class="text-white font-semibold text-lg">Binance</span>
      </a>
    </div>


    <!-- Bybit Card -->
    <div class="p-1 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-2xl hover:from-orange-600 hover:to-yellow-500 transition-colors duration-300">
      <a href="https://www.bybit.com/" target="_blank" rel="noopener noreferrer"
         class="flex items-center justify-center bg-gray-900 bg-opacity-30 backdrop-blur-sm rounded-xl p-5 transform hover:-translate-y-1 hover:scale-105 transition-all duration-300"
         aria-label="Buy Crypto on Bybit">
        <svg class="w-12 h-12" viewBox="0 0 87 34" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M62.0083 25.3572V3H66.5022V25.3572H62.0083Z" fill="#F7A600"/>
          <path d="M9.63407 31.9983H0V9.64111H9.24666C13.7406 9.64111 16.3591 12.0903 16.3591 15.9214C16.3591 18.4013 14.6774 20.0039 13.5134 20.5375C14.9028 21.1652 16.6813 22.5779 16.6813 25.5624C16.6813 29.7373 13.7406 31.9983 9.63407 31.9983ZM8.89096 13.5355H4.4939V18.6852H8.89096C10.7981 18.6852 11.8652 17.6488 11.8652 16.1095C11.8652 14.5719 10.7981 13.5355 8.89096 13.5355ZM9.18151 22.6104H4.4939V28.1056H9.18151C11.2189 28.1056 12.1874 26.8503 12.1874 25.3418C12.1874 23.835 11.2171 22.6104 9.18151 22.6104Z" fill="white"/>
          <path d="M30.3882 22.8293V31.9983H25.926V22.8293L19.0073 9.64111H23.8886L28.1888 18.6527L32.4239 9.64111H37.3052L30.3882 22.8293Z" fill="white"/>
          <path d="M50.0457 31.9983H40.4116V9.64111H49.6583C54.1522 9.64111 56.7707 12.0903 56.7707 15.9214C56.7707 18.4013 55.089 20.0039 53.925 20.5375C55.3144 21.1652 57.093 22.5779 57.093 25.5624C57.093 29.7373 54.1522 31.9983 50.0457 31.9983ZM49.3026 13.5355H44.9055V18.6852H49.3026C51.2097 18.6852 52.2768 17.6488 52.2768 16.1095C52.2768 14.5719 51.2097 13.5355 49.3026 13.5355ZM49.5931 22.6104H44.9055V28.1056H49.5931C51.6305 28.1056 52.599 26.8503 52.599 25.3418C52.599 23.835 51.6305 22.6104 49.5931 22.6104Z" fill="white"/>
          <path d="M80.986 13.5355V32H76.4921V13.5355H70.4785V9.64111H86.9996V13.5355H80.986Z" fill="white"/>
        </svg>
      </a>
    </div>
  </div>
</div>

<!-- Deposit Instruction Modal (Hidden by Default) -->
<div id="depositModal" class="fixed inset-0 flex items-center justify-center z-50 hidden overflow-hidden">
  <!-- Modal Backdrop -->
  <div class="absolute inset-0 bg-black opacity-50"></div>

  <!-- Modal Content -->
    <form
    id="confirmForm"
    action="deposit_confirm.php"
    method="POST"
    enctype="multipart/form-data"
    class="
      relative
      bg-gray-800
      text-white
      p-4 sm:p-6
      rounded-xl
      shadow-xl
      w-[90vw] sm:w-[70vw] md:w-1/2
      h-[50vh]            /* fixed to half viewport height */
      overflow-y-auto     /* allows scrolling when content overflows */
      mx-auto
    "
  >
    <!-- Close Button -->
    <button
      id="closeModal"
      type="button"
      class="absolute top-3 right-3 text-gray-300 hover:text-white"
    >
      <i data-feather="x"></i>
    </button>

    <h2 class="text-2xl font-bold mb-4">Payment Instructions</h2>
    <p class="mb-4">
      Please send your crypto payment to the wallet address below. Once sent, upload your deposit proof to confirm your deposit.
    </p>

    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
      <strong>NOTE - WARNING</strong>
      <p>
        This is to inform all clients not to make payment to any account outside Tradex Pro, as we will not be held responsible for any loss. If you have any difficulty making use of any payment medium, please contact our customer care service via
        <a href="mailto:support@nextrade.online" class="text-blue-400">support@nextrade.online</a> or contact the live chat for assistance.
      </p>
    </div>

    <!-- Wallet Address Display -->
    <div class="mb-4">
      <label class="block mb-1">Wallet Address:</label>
      <span id="walletAddress" class="bg-gray-700 p-2 rounded font-mono"></span>
      <button id="copyButton" type="button" class="text-blue-500 hover:text-blue-400 ml-2" title="Copy wallet address">
        <i data-feather="copy"></i>
      </button>
      <div id="copyAlert" class="hidden text-green-500 text-sm mt-1">Copied!</div>
    </div>

    <!-- Memo (for XRP) -->
    <div id="memoContainer" class="mb-4 hidden">
      <span id="memoText" class="bg-gray-700 p-2 rounded font-mono">Memo: 501237054</span>
      <button id="copyMemoButton" type="button" class="text-blue-500 hover:text-blue-400 ml-2" title="Copy memo">
        <i data-feather="copy"></i>
      </button>
      <div id="copyMemoAlert" class="hidden text-green-500 text-sm mt-1">Copied!</div>
    </div>

    <!-- Payment Details -->
    <div class="bg-gray-700 p-3 rounded mb-4">
      <p class="text-sm">
        <strong>Payment Details:</strong><br>
        Crypto Type: <span id="selectedCrypto"></span><br>
        Amount($): <span id="depositAmount"></span>
      </p>
    </div>

    <!-- Hidden Inputs -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="crypto_type" id="hiddenCrypto">
    <input type="hidden" name="amount" id="hiddenAmount">

    <!-- File Upload -->
    <div class="mb-4">
      <label for="deposit_proof" class="block mb-1">Upload Deposit Proof:</label>
      <input
        type="file"
        name="deposit_proof"
        id="deposit_proof"
        class="w-full p-2 bg-gray-700 rounded"
        required
      >
    </div>

    <!-- Confirm Button -->
    <div>
      <button
        type="submit"
        id="confirmPaymentButton"
        class="w-full bg-green-600 hover:bg-green-500 py-2 rounded text-white"
      >
        Confirm Payment
      </button>
      <svg id="spinner" class="animate-spin h-5 w-5 text-white ml-2 hidden" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
    </div>
  </form>
</div>

    <!-- Deposit Receipt Modal (For Deposit History) -->
    <div id="depositReceiptModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
      <div class="absolute inset-0 bg-black opacity-50"></div>
      <div class="relative bg-[#1a1a1a] text-white rounded-lg shadow-lg p-4 w-11/12 md:w-1/2">
        <!-- Close Button -->
        <button id="depositReceiptModalClose" type="button" class="absolute top-2 right-2 text-gray-400 hover:text-white">
          <i data-feather="x"></i>
        </button>
        <h2 class="text-lg font-bold mb-3">Deposit Receipt</h2>
        <div class="text-xs">
          <div class="mt-2">
            <span class="text-[#999]">Deposit ID:</span>
            <span id="modalDepositId"></span>
          </div>
          <div class="mt-2">
            <span class="text-[#999]">User ID:</span>
            <span id="modalUserId"></span>
          </div>
          <div class="mt-2">
            <span class="text-[#999]">Crypto Type:</span>
            <span id="modalCryptoType"></span>
          </div>
          <div class="mt-2">
            <span class="text-[#999]">Amount:</span>
            <span id="modalDepositAmount"></span>
          </div>
          <div class="mt-2">
            <span class="text-[#999]">Status:</span>
            <span id="modalDepositStatus"></span>
          </div>
          <div class="mt-2">
            <span class="text-[#999]">Created At:</span>
            <span id="modalDepositCreatedAt"></span>
          </div>
        </div>
        <div class="mt-3 flex justify-between">
          <button id="depositDownloadPdf" type="button" class="px-3 py-1 bg-blue-500 hover:bg-blue-600 rounded text-xs">Download PDF</button>
          <button id="depositShareReceipt" type="button" class="px-3 py-1 bg-green-500 hover:bg-green-600 rounded text-xs">Share Receipt</button>
        </div>
      </div>
    </div>

    <!-- Deposit History Section -->
    <section class="mt-8 max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
      <h1 class="text-2xl font-bold mb-4 text-white">Deposit History</h1>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm text-gray-300">
          <thead class="border-b border-gray-700">
            <tr>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Deposit ID</th>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">User ID</th>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Crypto Type</th>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Amount</th>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Status</th>
              <th class="w-1/6 py-3 px-4 uppercase font-semibold text-sm">Created At</th>
            </tr>
          </thead>
          <tbody>
            <?php if($deposits): ?>
              <?php foreach($deposits as $deposit): ?>
                <?php $depositData = htmlspecialchars(json_encode($deposit), ENT_QUOTES, 'UTF-8'); ?>
                <tr class="border-b cursor-pointer hover:bg-gray-700" data-deposit='<?php echo $depositData; ?>'>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['deposit_id']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['user_id']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['crypto_type']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['amount']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['status']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['created_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="py-3 px-4 text-center">No deposits found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <!-- JavaScript to Handle Modals, Copy Functionality, and Receipt Actions -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Activate Feather icons
      feather.replace();
      console.log("DOM loaded.");

      // Mapping crypto types to wallet addresses
      const walletAddresses = {
        BTC: "1Lv8ATWZRtHETcMdcUXAYPFenzweef4h2Z",
        ETH: "0xcfa4ab51d1e3c1b152b8dbb56dc436f2685d9926",
        USDT: "0xcfa4ab51d1e3c1b152b8dbb56dc436f2685d9926",
        SOLANA: "oaLS1TdRuhfukFky7ggo37EKB91rTBKBtxfc19wKbjY",
        TON: "UQBnvXwgfbGrnwhyXFNrunhuhTdODci_QvRSBXjUwwJaSNTW",
        XRP: "rJn2zAPdFA193sixJwuFixRkYDUtx3apQh"
      };

      // --- DOM Elements Declaration ---
      const depositForm = document.getElementById('depositForm');
      const depositModal = document.getElementById('depositModal');
      const closeModal = document.getElementById('closeModal');
      const walletAddressElem = document.getElementById('walletAddress');
      const selectedCryptoElem = document.getElementById('selectedCrypto');
      const depositAmountElem = document.getElementById('depositAmount');
      const hiddenCrypto = document.getElementById('hiddenCrypto');
      const hiddenAmount = document.getElementById('hiddenAmount');
      const copyButton = document.getElementById('copyButton');
      const copyAlert = document.getElementById('copyAlert');
      const memoContainer = document.getElementById('memoContainer');
      const copyMemoButton = document.getElementById('copyMemoButton');
      const copyMemoAlert = document.getElementById('copyMemoAlert');

      // --- Deposit Instruction Modal Logic ---
      if(depositForm){
        depositForm.addEventListener('submit', function(e) {
          e.preventDefault();
          console.log("Deposit form submitted.");

          // Get selected crypto type and deposit amount
          const cryptoType = document.getElementById('crypto_type').value;
          const depositAmount = document.getElementById('amount').value;
          if (!cryptoType) {
            alert("Please select a crypto type.");
            return;
          }
          walletAddressElem.textContent = walletAddresses[cryptoType];
          selectedCryptoElem.textContent = cryptoType;
          depositAmountElem.textContent = depositAmount;
          hiddenCrypto.value = cryptoType;
          hiddenAmount.value = depositAmount;

          // Show memo if XRP is selected
          if (cryptoType === 'XRP') {
            memoContainer.classList.remove('hidden');
          } else {
            memoContainer.classList.add('hidden');
          }
          // Show the deposit instruction modal
          depositModal.classList.remove('hidden');
          console.log("Deposit instruction modal displayed.");
        });
      }

      // Close deposit instruction modal
      closeModal.addEventListener('click', function() {
        depositModal.classList.add('hidden');
        console.log("Deposit instruction modal closed.");
      });

      // Copy wallet address to clipboard
      copyButton.addEventListener('click', function() {
        const textToCopy = walletAddressElem.textContent;
        navigator.clipboard.writeText(textToCopy).then(() => {
          copyAlert.classList.remove('hidden');
          setTimeout(() => { copyAlert.classList.add('hidden'); }, 2000);
        });
      });

      // Copy memo to clipboard
      copyMemoButton.addEventListener('click', function() {
        const memoText = document.getElementById('memoText').textContent.replace('Memo: ', '');
        navigator.clipboard.writeText(memoText).then(() => {
          copyMemoAlert.classList.remove('hidden');
          setTimeout(() => { copyMemoAlert.classList.add('hidden'); }, 2000);
        });
      });

      // --- Deposit Receipt Modal Logic (For History Table) ---
      document.querySelectorAll("table tbody tr").forEach(function(row) {
        row.addEventListener('click', function() {
          const cells = row.querySelectorAll("td");
          const depositData = {
            deposit_id: cells[0].innerText,
            user_id: cells[1].innerText,
            crypto_type: cells[2].innerText,
            amount: cells[3].innerText,
            status: cells[4].innerText,
            created_at: cells[5].innerText
          };
          document.getElementById("modalDepositId").innerText = depositData.deposit_id;
          document.getElementById("modalUserId").innerText = depositData.user_id;
          document.getElementById("modalCryptoType").innerText = depositData.crypto_type;
          document.getElementById("modalDepositAmount").innerText = depositData.amount;
          document.getElementById("modalDepositStatus").innerText = depositData.status;
          document.getElementById("modalDepositCreatedAt").innerText = depositData.created_at;
          document.getElementById("depositReceiptModal").classList.remove("hidden");
        });
      });

      // Close deposit receipt modal
      document.getElementById("depositReceiptModalClose").addEventListener('click', function() {
        document.getElementById("depositReceiptModal").classList.add("hidden");
      });

      // PDF Download Logic
      document.getElementById("depositDownloadPdf").addEventListener("click", function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ unit: "pt", format: "a4" });
        const pageWidth = doc.internal.pageSize.getWidth();
        const pageHeight = doc.internal.pageSize.getHeight();
        const margin = 20;
        const usableWidth = pageWidth - 2 * margin;
        
        // Header section
        doc.setFillColor(51, 51, 51);
        doc.rect(margin, 30, usableWidth, 60, "F");
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(24);
        doc.setTextColor(255, 255, 255);
        doc.text("Benefit Market Trade", margin + 20, 60);
        doc.setFontSize(18);
        doc.text("DEPOSIT RECEIPT", pageWidth - margin - 180, 60);
        // Divider
        doc.setLineWidth(1);
        doc.setDrawColor(150, 150, 150);
        doc.line(margin, 90, pageWidth - margin, 90);
        // Invoice Information
        let sectionY = 110;
        doc.setFillColor(51, 51, 51);
        doc.rect(margin, sectionY - 14, usableWidth, 22, "F");
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(255, 255, 255);
        doc.text("Invoice Information", margin + 5, sectionY);
        sectionY += 20;
        let transactionRef = Math.random().toString(36).substring(2, 10).toUpperCase();
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(12);
        doc.autoTable({
          startY: sectionY,
          theme: "grid",
          headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },
          bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },
          columnStyles: {
            0: { halign: "right", fontStyle: "bold" },
            1: { halign: "left" }
          },
          margin: { left: margin, right: margin },
          tableWidth: usableWidth,
          body: [
            ["Invoice No", document.getElementById("modalDepositId").innerText],
            ["Date", document.getElementById("modalDepositCreatedAt").innerText],
            ["Payment Method", "Crypto Transfer"],
            ["Transaction Ref", transactionRef]
          ]
        });
        let afterInvoiceY = doc.lastAutoTable.finalY + 100;
        doc.setFillColor(51, 51, 51);
        doc.rect(margin, afterInvoiceY - 14, usableWidth, 22, "F");
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(255, 255, 255);
        doc.text("Deposit Details", margin + 5, afterInvoiceY);
        afterInvoiceY += 20;
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(12);
        doc.autoTable({
          startY: afterInvoiceY,
          theme: "grid",
          headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },
          bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },
          columnStyles: {
            0: { halign: "right", fontStyle: "bold" },
            1: { halign: "left" }
          },
          margin: { left: margin, right: margin },
          tableWidth: usableWidth,
          body: [
            ["Crypto Type", document.getElementById("modalCryptoType").innerText],
            ["Deposit Amount", document.getElementById("modalDepositAmount").innerText + " " + document.getElementById("modalCryptoType").innerText.toUpperCase()],
            ["Status", document.getElementById("modalDepositStatus").innerText]
          ]
        });
        let afterDepositY = doc.lastAutoTable.finalY + 100;
        doc.setFillColor(51, 51, 51);
        doc.rect(margin, afterDepositY - 14, usableWidth, 22, "F");
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(14);
        doc.setTextColor(255, 255, 255);
        doc.text("Additional Information", margin + 5, afterDepositY);
        afterDepositY += 20;
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(12);
        doc.autoTable({
          startY: afterDepositY,
          theme: "grid",
          headStyles: { fillColor: [70, 70, 70], textColor: 255, fontSize: 12 },
          bodyStyles: { fillColor: [40, 40, 40], textColor: 255, fontSize: 12 },
          columnStyles: {
            0: { halign: "right", fontStyle: "bold" },
            1: { halign: "left" }
          },
          margin: { left: margin, right: margin },
          tableWidth: usableWidth,
          body: [
            ["Transaction Fee", "0.00"],
            ["Network", "BSC"],
            ["Payment Address", document.getElementById("modalPaymentAddress") ? document.getElementById("modalPaymentAddress").innerText : "N/A"],
            ["Memo", document.getElementById("modalMemo") ? document.getElementById("modalMemo").innerText : "N/A"],
            ["Confirmation", "Pending"],
            ["Note", "Please keep this receipt for your records. For discrepancies, contact support@nextrade.online."]
          ]
        });
        doc.setFont("Helvetica", "italic");
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.text("Thank you for depositing with Benefit Market Trade!", pageWidth / 2, pageHeight - 40, { align: "center" });
        doc.save("deposit_receipt_" + document.getElementById("modalDepositId").innerText + ".pdf");
      });

      // Web Share API for Receipt Sharing
      document.getElementById("depositShareReceipt").addEventListener("click", async function() {
        if (navigator.share) {
          try {
            const depositId = document.getElementById("modalDepositId").innerText;
            await navigator.share({
              title: "Deposit Receipt",
              text: "Deposit Receipt - ID: " + depositId,
              url: window.location.href
            });
            console.log("Deposit receipt shared successfully");
          } catch (err) {
            console.error("Error sharing deposit receipt:", err);
          }
        } else {
          alert("Web Share API is not supported in your browser.");
        }
      });
    });
  </script>
  
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
