<?php
// Enable Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'config.php';

// Ensure user is logged in and has the correct role
if (!isset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Grab the email from session
$email = $_SESSION['email'];

// Prepare & execute using a named placeholder
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
if (!$stmt) {
    // PDO::prepare failed
    die("Prepare failed: " . implode(" | ", $conn->errorInfo()));
}

// Bind & execute
if (!$stmt->execute(['email' => $email])) {
    // Execution error
    die("Execute failed: " . implode(" | ", $stmt->errorInfo()));
}

// Fetch a single row
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $user_id            = $row['user_id'];
    $bitcoin_balance    = $row['bitcoin_balance'];
    $ethereum_balance   = $row['ethereum_balance'];
    // other fields...
} else {
    // No user found; initialize defaults
    $row = [
        'fname'            => '',
        'lname'            => '',
        'email'            => '',
        'phone'            => '',
        'dateofbirth'      => '',
        'country'          => '',
        'address'          => '',
        'account_id'       => '',
        'account_type'     => '',
        'trading_bot'      => '',
        'membership_level' => '',
        'currency'         => '',
        'last_login'       => '',
    ];
    $bitcoin_balance  = 0;
    $ethereum_balance = 0;
}

// Conversion rates
$btc_to_usd = 86613.40;
$eth_to_usd = 3382.13;

// Calculate balances in USD
$bitcoin_usd   = $bitcoin_balance * $btc_to_usd;
$ethereum_usd  = $ethereum_balance * $eth_to_usd;
$total_usd     = $bitcoin_usd + $ethereum_usd;

// Ensure balances are non-negative
$bitcoin_balance  = max(0, $bitcoin_balance);
$ethereum_balance = max(0, $ethereum_balance);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Benefit Market Trade - Profile</title>
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
          <a href="login.php">
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
  <a href="login.php">
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

<main class="p-3 space-y-6">
<div class="max-w-4xl mx-auto p-6">
  <!-- Profile Summary -->
  <div class="bg-white shadow rounded-lg p-6 mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Profile Summary</h2>
  </div>

  <!-- Profile Cards -->
  <div class="grid md:grid-cols-2 gap-6">
    <!-- Personal Information -->
    <div class="bg-gray-600 shadow rounded-lg p-6">
      <div class="flex items-center space-x-4 mb-4">
        <?php $initials = strtoupper(substr($row['fname'], 0, 1) . substr($row['lname'], 0, 1)); ?>
        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
          <span class="text-xl font-bold text-gray-800"><?php echo $initials; ?></span>
        </div>
      </div>
<div  class="space-y-2 mb-6">
<h3 class="text-xl font-semibold text-white">Personal Information</h3>

</div>
      <div class="space-y-2 text-white">
        <div><span class="font-medium">First Name:</span> <?php echo $row['fname'] ?></div>
        <div><span class="font-medium">Last Name:</span> <?php echo $row['lname'] ?></div>
        <div><span class="font-medium">Email:</span> <?php echo $row['email'] ?></div>
        <div><span class="font-medium">Phone:</span> <?php echo $row['phone'] ?></div>
        <div><span class="font-medium">Date of Birth:</span> <?php echo $row['dateofbirth'] ?></div>
               <div><span class="font-medium">Country:</span> <?php echo $row['country'] ?></div>

        <div><span class="font-medium">Address:</span> <?php echo $row['address'] ?></div>
      </div>
    </div>

    <!-- Account Information -->
    <div class="bg-gray-600 shadow rounded-lg p-6">
      <h3 class="text-xl font-semibold text-white mb-4">Account Information</h3>
      <div class="space-y-2 text-white">
        <div><span class="font-medium">Account ID:</span> <?php echo $row['account_id'] ?></div>
        <div><span class="font-medium">Account Type:</span> <?php echo $row['account_type'] ?></div>
        <div><span class="font-medium">Bot Status:</span> <?php echo $row['trading_bot'] ?></div>
        <div><span class="font-medium">Subscription Plan:</span> <?php echo $row['membership_level'] ?></div>
                <div><span class="font-medium">Currency</span> <?php echo $row['currency'] ?></div>

        <div><span class="font-medium">Last Login:</span> <?php echo $row['last_login'] ?></div>
      </div>
    </div>
  </div>

  <!-- Edit Profile Button -->
  <div class="mt-6 text-center">
    <a href="editprofile.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded">
      Edit Profile
    </a>
  </div>
</div>

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
