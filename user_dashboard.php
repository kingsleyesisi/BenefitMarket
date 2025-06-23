<?php
// Enable Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
include_once 'config.php';  // Contains $conn (PDO PostgreSQL connection)

// Your ExchangeRate-API key
define('FX_API_KEY', '98c2c6e024e9d0446a5c3c59');

// 1) Fetch user data
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

try {
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Pull fields out
    $userId          = $row['user_id'];
    $account_id      = $row['account_id']       ?? 0;
    $usd_balance     = $row['usd_balance']      ?? 0.0;
    $profit          = $row['profit']           ?? 0.0;
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

// Bot status dot color
switch (strtolower($tradingBotStatus)) {
    case 'connected': $dotColor = 'bg-green-500'; break;
    case 'pending':   $dotColor = 'bg-yellow-500'; break;
    default:          $dotColor = 'bg-red-500'; break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title> Dashboard</title>
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
          <span class="text-2xl">Benefit Smart</span>
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
      <!-- <li>
    <a href="referral.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
        <i class="ri-user-add-line text-xl"></i>
        <span class="ml-2">Referrals</span>
    </a>
</li> -->
       <li>
        
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

        <!-- <li>
    <a href="referral.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
        <i class="ri-user-add-line text-xl"></i>
        <span class="ml-2">Referrals</span>
    </a>
</li> -->

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
    
            <style>
        /* Blinking animation */
        @keyframes blinkingText {
            0% { opacity: 1; }
            50% { opacity: 0; }
            100% { opacity: 1; }
        }
        
        /* Apply blinking animation for unverified status */
        .blinking {
            animation: blinkingText 1.5s infinite;
        }
        </style>
    
    
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
            <a href="trades.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
              <i class="ri-bar-chart-line mr-2"></i>Trades
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

    <?php
// Normalize the verification status to lower case for comparison
$statusLower = strtolower($verificationStatus);

// Determine the class based on the status
if ($statusLower === 'unverified' || $statusLower == 'rejected' || $statusLower === 'not connected' ) {
    $statusClass = 'blinking text-red-500';
} elseif ($statusLower === 'approved' || $statusLower === 'verified' || $statusLower === 'active' ) {
    $statusClass = 'text-green-500';
} elseif ($statusLower === 'pending') {
    $statusClass = 'blinking text-yellow-500';
} else {
    $statusClass = 'text-gray-400';
}
?>

<main class="p-6 min-h-screen">
  <!-- Top Grid: Profile and Account Summary -->
  <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
    
    <!-- Profile Card (hidden on mobile, visible from md and up) -->
    <div class="hidden md:flex bg-gray-800 shadow-xl rounded-xl p-6 items-start space-x-4">
      <div class="flex-shrink-0">
        <div class="w-16 h-16 rounded-full bg-gray-600 flex items-center justify-center">
          <i class="ri-user-line text-white text-3xl"></i>
        </div>
      </div>
      <div>
        <h3 class="text-xl font-bold text-gray-100">
          <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
        </h3>
        <p class="text-gray-300">
          <?php echo htmlspecialchars($row['email']); ?>
        </p>
        <p class="text-sm text-gray-400 mt-2">
          Member since:
          <span class="font-medium"><?php echo date('M Y', strtotime($row['created_at'])); ?></span>
        </p>
        <!-- <p class="text-sm text-gray-400">
          Account Type:
          <span class="font-bold <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($account_type); ?>
          </span>
        </p> -->
      </div>
    </div>

    <!-- Account Summary Card (visible on all screens) -->
    <div class="bg-gray-800 shadow-xl rounded-xl p-6 flex items-start space-x-4">
      <div class="flex-shrink-0">
        <div class="w-16 h-16 rounded-full bg-gray-600 flex items-center justify-center">
          <i class="ri-wallet-3-line text-white text-3xl"></i>
        </div>
      </div>
      <div>
        <h4 class="text-xl font-bold text-gray-100 mb-2">Account Summary</h4>
                <h3 class="text-xl font-bold text-gray-100 mt-4 lg:hidden">
          <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
        </h3>
        <p class="text-gray-300">
          Account ID:
          <span class="font-medium"><?php echo htmlspecialchars($account_id); ?></span>
        </p>
        <p class="text-gray-300">
          Account Type:
          <span class="font-medium"><?php echo htmlspecialchars($account_type); ?></span>
          
        </p>
        
        <p class="text-gray-300">
          Currency:
         <a href="/editprofile.php"><span class="font-medium"><?php echo htmlspecialchars($userCurrency); ?></span></a>
          
        </p>
        <!-- Optional: If you want first name/email/status also visible on mobile inside Account Summary,
             uncomment these lines below -->
        
        <p class="text-gray-300 lg:hidden">
          <?php echo htmlspecialchars($row['email']); ?>
        </p>
        <!-- <p class="text-sm text-gray-400 mt-2 lg:hidden">
          Account Status:
          <span class="font-bold  <?php echo $statusClass; ?>">
            <?php echo htmlspecialchars($verificationStatus); ?>
          </span>
        </p> -->
        
      </div>
    </div>
  </div>

  <?php
  // … after you’ve connected ($conn) and started the session …

  // 1) Fetch the status from your table (adjust table/where clause as needed)
  $stmt = $conn->prepare("SELECT trading_bot FROM users WHERE user_id = $1");
  $stmt->execute([$_SESSION['user_id']]);
  $tradingBotStatus = $stmt->fetchColumn();

  // 2) Map status → Tailwind color
  switch (strtolower($tradingBotStatus)) {
    case 'connected':
      $dotColor = 'bg-green-500';
      break;
    case 'pending':
      $dotColor = 'bg-yellow-500';
      break;
    default:
      $dotColor = 'bg-red-500';
      break;
  }
  ?>



<!-- Trading Trends Widget Section -->
<section
  class="mt-8 w-full max-w-4xl mx-auto
         grid grid-cols-2 lg:grid-cols-3
         gap-4 px-2 relative"
>
  <!-- … -->

  <!-- Card template -->
  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-wallet-3-line text-green-400 text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Total Deposit</p>
    <p class="text-base font-bold text-gray-100 text-center">
     <?= formatFx($usd_balance, $fxRate, $userCurrency) ?>
    </p>
  </div>


      <!--<?php echo number_format($usd_balance, 2); ?>-->

<!-- 5) In your HTML dashboard, use formatFx() wherever you need a converted amount -->
<!--<div class="dashboard">-->
<!--  <div>Total USD Balance: <?= number_format($usd_balance, 2) ?> USD</div>-->
<!--  <div>Your Balance (<?= $userCurrency ?>): <?= formatFx($usd_balance, $fxRate, $userCurrency) ?></div>-->
<!--  <div>Profit (USD): <?= number_format($profit, 2) ?> USD</div>-->
<!--  <div>Profit (<?= $userCurrency ?>): <?= formatFx($profit, $fxRate, $userCurrency) ?></div>-->
  <!-- etc… -->
<!--</div>-->


  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-cash-line text-blue-400 text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Total Withdrawal</p>
    <p class="text-base font-bold text-gray-100 text-center">
      <?= formatFx($totalWithdrawal, $fxRate, $userCurrency) ?>
    </p>
  </div>

  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-exchange-dollar-line text-green-400 text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Total Trades</p>
    <p class="text-base font-bold text-gray-100 text-center">
      <?php echo $totalTrades; ?>
    </p>
  </div>
<!-- 5. TRADING BOT (center overlay) -->
<div
  class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-10
         w-20 h-20 bg-gray-900 rounded-full
         flex flex-col items-center justify-center
         shadow-lg border-4 border-gray-700"
>
  <i class="ri-robot-2-line text-white text-3xl"></i>
  <p class="text-gray-300 text-xs mt-1">Trading Bot</p>

  <!-- status dot: color comes from PHP, animate-pulse makes it blink -->
  <div class="w-3 h-3 rounded-full animate-pulse mt-1 <?= $dotColor ?>"></div>
</div>



  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-money-dollar-circle-line text-yellow-400 text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Total Traded Amount</p>
    <p class="text-base font-bold text-gray-100 text-center">
     <?= formatFx($totalAmount, $fxRate, $userCurrency) ?>
    </p>
  </div>

  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-bar-chart-line text-purple-400 text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Profit Amount</p>
    <p class="text-base font-bold text-gray-100 text-center">
     <?= formatFx($profit, $fxRate, $userCurrency) ?>
    </p>
  </div>

  <div class="bg-gray-800 shadow-xl rounded-xl p-4 flex flex-col items-center space-y-1">
    <i class="ri-robot-2-line text-white text-2xl"></i>
    <p class="text-gray-300 text-xs text-center">Bot Status</p>
    <p class="text-base font-bold text-gray-100 text-center">
          <?php echo htmlspecialchars($row['trading_bot']); ?>
    </p>
  </div>

</section>

  
  <!-- Recent Trades Section -->
  <section class="mt-8 max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
    <h4 class="text-2xl font-bold text-gray-100 mb-4">Recent Trades</h4>
    <div class="overflow-x-auto">
      <table class="min-w-full text-left text-sm text-gray-300">
        <thead class="border-b border-gray-700">
          <tr>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Asset</th>
            <th class="px-4 py-2">Type</th>
            <th class="px-4 py-2">Amount ($)</th>
          </tr>
        </thead>
        <tbody>
          <?php
            // Fetch recent trades for the logged in user (adjust query as needed)
            $stmt_trades = $conn->prepare("SELECT trade_date, asset, trade_category, amount FROM trades WHERE user_id = :user_id ORDER BY trade_date DESC LIMIT 5");
            $stmt_trades->execute([':user_id' => $_SESSION['user_id']]);
            $result_trades = $stmt_trades->fetchAll(PDO::FETCH_ASSOC);
            if (count($result_trades) > 0) {
                foreach ($result_trades as $trade) {
                    echo '<tr class="border-b border-gray-700">';
                    echo '<td class="px-4 py-2">' . date("M d, Y", strtotime($trade["trade_date"])) . '</td>';
                    echo '<td class="px-4 py-2 capitalize">' . htmlspecialchars($trade["asset"]) . '</td>';
                    echo '<td class="px-4 py-2">' . htmlspecialchars($trade["trade_category"]) . '</td>';
                    echo '<td class="px-4 py-2">$' . number_format($trade["amount"], 2) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" class="px-4 py-2 text-center">No recent trades found.</td></tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
  </section>
  
  
  
<!-- Recent Activity Section (Combined from trades, activity_log, and deposits tables) -->
<section class="mt-8 max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Recent Activity</h4>
  <ul class="space-y-4 text-gray-300">
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
      LIMIT 5
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
</section>

<!-- First Trade Chart -->
<!-- TradingView Widget BEGIN -->
<section class="w-full bg-gray-800 shadow-xl rounded-xl p-2 mt-6" style="height: 100vh;">
  <div class="tradingview-widget-container" style="width: 100%; height: 100%; overflow: hidden;">
    <div class="tradingview-widget-container__widget" style="width: 100%; height: 100%;"></div>
    <script
      type="text/javascript"
      src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js"
      async
    >
    
    {
      "autosize": true,
      "symbol": "BINANCE:BTCUSDT",
      "timezone": "exchange",
      "theme": "dark",
      "style": "1",
      "locale": "en",
      "backgroundColor": "rgba(0, 0, 0, 1)",
      "range": "ALL",
      "hide_side_toolbar": false,
      "allow_symbol_change": true,
      "support_host": "https://www.tradingview.com"
    }
    </script>
  </div>
</section>
<!-- TradingView Widget END -->


<!-- TradingView Widget BEGIN -->
<section class="w-full bg-gray-800 shadow-xl rounded-xl p-6 mt-6" style="height: 90vh;">
  <!-- Styled Heading -->
  <h1 class="text-white text-center font-bold text-2xl mb-4">
    Crypto Currency Market Overview
  </h1>

  <div class="tradingview-widget-container" style="width: 100%; height: calc(100% - 2rem); overflow: hidden; border-radius: 0.5rem;">
    <div class="tradingview-widget-container__widget" style="width: 100%; height: 100%;"></div>
    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-screener.js" async>
    {
      "width": "100%",
      "height": "100%",
      "defaultColumn": "performance",
      "screener_type": "crypto_mkt",
      "displayCurrency": "BTC",
      "colorTheme": "dark",
      "locale": "en"
    }
    </script>
  </div>
</section>
<!-- TradingView Widget END -->


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


</main>



</body>
</html>