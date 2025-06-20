<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details using PDO
try {
    $stmt = $conn->prepare("SELECT fname FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $first_name = htmlspecialchars($result['fname']);
} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

// Dynamically generate the referral link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $protocol . $_SERVER['HTTP_HOST'];
$referral_link = $domain . "/register.php?ref=" . urlencode($first_name);

// Calculate total referrals dynamically using PDO
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS referral_count FROM referrals WHERE referrer_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $referral_count = $result['referral_count'];
} catch (PDOException $e) {
    die("Error calculating referral count: " . $e->getMessage());
}

// Fetch referral details using PDO
try {
    $stmt = $conn->prepare("SELECT u.email, r.referral_date FROM referrals r JOIN users u ON r.referred_id = u.user_id WHERE r.referrer_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching referral details: " . $e->getMessage());
}

// Function to mask email addresses
function maskEmail($email) {
    $parts = explode("@", $email);
    $name = $parts[0];
    $domain = $parts[1];

    if (strlen($name) <= 2) {
        $maskedName = substr($name, 0, 3) . str_repeat("*", strlen($name) - 1);
    } else {
        $maskedName = substr($name, 0, 3) . str_repeat("*", strlen($name) - 2) . substr($name, -1);
    }

    return $maskedName . "@" . $domain;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program - Benefit Market Trade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <!-- Optional: Link to your local Tailwind file -->
  <link rel="stylesheet" href="tailwind.min.css" />
  <link rel="stylesheet" href="./remixicon/remixicon.css" />


</head>

<style>
    .menu-items { padding: 1rem; }
    .nav-links li { margin: 1rem 0; }
    .nav-links li a { display: flex; align-items: center; gap: 0.5rem; }
    .logout-mode li { margin-top: 1rem; }
    @media (max-width: 1024px) {
      .desktop-nav { display: none; }
    }
    </style>
<body class="bg-gray-300 text-gray-900 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
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
      <a href="login.php" class="flex items-center">
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
                <i class="ri-share-line text-2xl"></i>
                <span class=" text-2xl ">Referal Program</span>
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

            <main class="p-6">
                <!-- Referral Section -->
                <div class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
                    <h2 class="text-2xl font-bold text-gray-100 mb-4">Your Referral Link</h2>
                    <div class="bg-gray-700 p-4 rounded mb-4">
                        <p class="text-gray-300">Share this link with your friends:</p>
                        <div class="flex items-center mt-2">
                            <input type="text" value="<?php echo $referral_link; ?>" readonly class="w-full bg-gray-600 text-gray-300 rounded px-3 py-2">
                            <button onclick="navigator.clipboard.writeText('<?php echo $referral_link; ?>')" class="ml-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Copy</button>
                        </div>
                    </div>

                    <div class="bg-gray-700 p-4 rounded mb-4">
                        <h3 class="text-xl font-semibold text-gray-100 mb-2">Referral Rewards</h3>
                        <p class="text-gray-300">For every friend you refer, you Both earn:</p>
                        <ul class="list-disc list-inside text-gray-300 mt-2">
                            <li>$5 for each successful referral.</li>
                            <li>2% of your friend's deposit amount and profit.</li>
                            <li> <strong> NOTE:</strong> Your Friend must complete KYC verification before any reward can be given</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-100 mb-2">Your Referrals</h3>
                    <?php if (empty($referrals)): ?>
                        <p class="text-gray-300">No referrals yet.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-gray-700 text-gray-300 rounded">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">Email</th>
                                        <th class="px-4 py-2 text-left">Date Referred</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($referrals as $referral): ?>
                                        <tr class="border-b border-gray-600">
                                            <td class="px-4 py-2"><?php echo htmlspecialchars(maskEmail($referral['email'])); ?></td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($referral['referral_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
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
</html>