<?php
session_start();
include_once 'config.php';  // provides $conn (PDO)

// Check if the user is logged in and has the role 'user'
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$activePlan = null;

try {
    // 1) Check if the user already has an active subscription
    $sqlActive = "
      SELECT 
        s.*, 
        p.name AS plan_name, 
        p.price_range, 
        p.monthly_price, 
        p.daily_roi, 
        p.total_expected_profit 
      FROM subscriptions s
      JOIN subscription_plans p 
        ON s.plan_id = p.plan_id
      WHERE s.user_id = :uid
        AND s.status IN ('pending', 'completed')
      ORDER BY s.created_at DESC
      LIMIT 1
    ";
    $stmt = $conn->prepare($sqlActive);
    $stmt->execute([':uid' => $user_id]);
    $activePlan = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // 2) Fetch all subscription plans
    $sqlPlans = "SELECT * FROM subscription_plans";
    $stmt = $conn->query($sqlPlans);
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // In production, you might log this instead of dying
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> Subscription</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <!-- Optional: Link to your local Tailwind file -->
  <link rel="stylesheet" href="tailwind.min.css" />
  <link rel="stylesheet" href="./remixicon/remixicon.css" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">


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

<main class="p-6 min-h-screen">
  <div class="container mx-auto py-12">
    <!-- Current Plan Card (if exists) -->
    <?php if ($activePlan): ?>
      <div class="bg-blue-800 rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-white mb-2">
          <i class="ri-coin-line mr-2"></i>Current Subscription Plan
        </h2>
        <p class="text-gray-300 text-sm mb-1"><strong>Plan:</strong> <?php echo htmlspecialchars($activePlan['plan_name']); ?></p>
        <p class="text-gray-300 text-sm mb-1"><i class="ri-tag-line mr-1"></i><strong>Price Range:</strong> <?php echo htmlspecialchars($activePlan['price_range']); ?></p>
        <p class="text-gray-300 text-sm mb-1"><i class="ri-line-chart-line mr-1"></i><strong>Daily ROI:</strong> <?php echo htmlspecialchars($activePlan['daily_roi']); ?>%</p>
        <p class="text-gray-300 text-sm mb-1"><i class="ri-percent-line mr-1"></i><strong>Profit:</strong> <?php echo htmlspecialchars($activePlan['total_expected_profit']); ?>%</p>
        <p class="text-gray-300 text-sm mb-1"><i class="ri-money-dollar-circle-line mr-1"></i><strong>Monthly Price:</strong> $<?php echo htmlspecialchars($activePlan['monthly_price']); ?></p>
        <p class="text-gray-200 mt-2">You are currently subscribed.</p>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach ($plans as $plan): ?>
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
          <h2 class="text-xl font-bold text-white mb-2">
            <i class="ri-coin-line mr-2"></i><?php echo htmlspecialchars($plan['name']); ?>
          </h2>
          <p class="text-gray-300 text-sm mb-1">
            <i class="ri-tag-line mr-1"></i><strong>Price Range:</strong> <?php echo htmlspecialchars($plan['price_range']); ?>
          </p>
          <p class="text-gray-300 text-sm mb-1">
            <i class="ri-line-chart-line mr-1"></i><strong>Daily ROI:</strong> <?php echo htmlspecialchars($plan['daily_roi']); ?>%
          </p>
          <p class="text-gray-300 text-sm mb-1">
            <i class="ri-percent-line mr-1"></i><strong>Profit:</strong> <?php echo htmlspecialchars($plan['total_expected_profit']); ?>%
          </p>
          <?php if ($activePlan): ?>
            <button disabled class="mt-4 inline-block bg-gray-500 text-white py-2 px-4 rounded">
              <i class="ri-checkbox-circle-line mr-2"></i>Already Subscribed
            </button>
          <?php else: ?>
            <button onclick="openModal(
              <?php echo $plan['plan_id']; ?>,
              '<?php echo addslashes($plan['name']); ?>',
              '<?php echo addslashes($plan['price_range']); ?>',
              '<?php echo $plan['monthly_price']; ?>',
              '<?php echo $plan['daily_roi']; ?>',
              '<?php echo $plan['total_expected_profit']; ?>'
            )" class="mt-4 inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
              <i class="ri-arrow-right-line mr-2"></i>Subscribe Now
            </button>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Subscription Confirmation Modal -->
  <div id="subscribeModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-lg relative">
      <!-- Modal Header: Plan Details -->
      <div id="modalHeader" class="mb-4 border-b border-gray-700 pb-2">
        <h2 id="modalPlanName" class="text-2xl font-bold text-white"></h2>
        <p id="modalPriceRange" class="text-gray-300 text-sm"><i class="ri-tag-line mr-1"></i></p>
        <p id="modalMonthlyPrice" class="text-gray-300 text-sm"><i class="ri-money-dollar-circle-line mr-1"></i></p>
        <p id="modalDailyROI" class="text-gray-300 text-sm"><i class="ri-line-chart-line mr-1"></i></p>
        <p id="modalProfit" class="text-gray-300 text-sm"><i class="ri-percent-line mr-1"></i></p>
      </div>
      <!-- Confirmation Step -->
      <div id="modalConfirmation">
        <h3 class="text-2xl font-bold text-white mb-4">
          <i class="ri-wallet-line mr-2"></i>Confirm Subscription
        </h3>
        <p class="text-gray-300 mb-4">Your subscription amount will be deducted from your USD balance.</p>
        <form id="subscriptionForm" method="post" action="subscribe_process.php">
          <!-- Hidden fields to pass plan id and monthly price -->
          <input type="hidden" name="plan_id" id="planIdField">
          <input type="hidden" name="monthly_price" id="monthlyPriceField">
          <div class="flex justify-between">
            <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
              <i class="ri-arrow-left-line mr-2"></i>Cancel
            </button>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
              Confirm &amp; Deduct <i class="ri-check-line ml-2"></i>
            </button>
          </div>
        </form>
      </div>
      <!-- Close Button -->
      <div class="absolute top-2 right-2">
        <button onclick="closeModal()" class="text-red-500 text-2xl">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Result Modal for Success/Error -->
  <div id="resultModal" class="fixed inset-0 hidden items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md relative">
      <h3 id="resultModalTitle" class="text-2xl font-bold text-white mb-4"></h3>
      <p id="modalMessageContent" class="text-gray-300 mb-4"></p>
      <button onclick="closeResultModal()" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">OK</button>
      <div class="absolute top-2 right-2">
        <button onclick="closeResultModal()" class="text-red-500 text-2xl">
          <i class="ri-close-line"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Open subscription confirmation modal and populate plan details
    function openModal(planId, planName, priceRange, monthlyPrice, dailyROI, profit) {
      document.getElementById("modalPlanName").innerText = planName;
      document.getElementById("modalPriceRange").innerText = "Price Range: " + priceRange;
      document.getElementById("modalMonthlyPrice").innerText = "Monthly Price: $" + monthlyPrice;
      document.getElementById("modalDailyROI").innerText = "Daily ROI: " + dailyROI + "%";
      document.getElementById("modalProfit").innerText = "Total Expected Profit: " + profit + "%";
      document.getElementById("planIdField").value = planId;
      document.getElementById("monthlyPriceField").value = monthlyPrice;
      
      const modal = document.getElementById("subscribeModal");
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    }

    function closeModal() {
      const modal = document.getElementById("subscribeModal");
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }

    // Result modal functions
    function openResultModal(type) {
      const resultModal = document.getElementById("resultModal");
      const title = document.getElementById("resultModalTitle");
      title.innerText = type === 'success' ? "Success" : "Error";
      resultModal.classList.remove("hidden");
      resultModal.classList.add("flex");
    }

    function closeResultModal() {
      const resultModal = document.getElementById("resultModal");
      resultModal.classList.add("hidden");
      resultModal.classList.remove("flex");
      // Optionally, refresh the page if successful subscription:
      // window.location.reload();
    }

    // Handle the subscription form submission via AJAX
    document.getElementById("subscriptionForm").addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('subscribe_process.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        // Close the confirmation modal
        closeModal();
        // Display the result modal with the appropriate message
        document.getElementById("modalMessageContent").innerText = data.message;
        if(data.status === 'success'){
          openResultModal('success');
        } else {
          openResultModal('error');
        }
      })
      .catch(error => {
        closeModal();
        document.getElementById("modalMessageContent").innerText = "An unexpected error occurred. Please try again.";
        openResultModal('error');
      });
    });
  </script>
</main>

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
