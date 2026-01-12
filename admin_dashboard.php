<?php

session_start();
include_once 'config.php';

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch admin details from the users table (ensuring role is admin)
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin'");
$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($result) > 0) {
    $admin = $result[0];
} else {
    // If no admin record is found, handle accordingly
    die("Admin not found.");
}

// Activity Log Section: Query the activity_log table
$stmt_activity = $conn->prepare("SELECT * FROM activity_log ORDER BY activity_date DESC LIMIT 5");
$stmt_activity->execute();
$result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);

// Aggregated statistics
// Total Users
$result_total_users = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$row_total_users = $result_total_users->fetch(PDO::FETCH_ASSOC);
$total_users = $row_total_users['total_users'];

// Total Trades
$result_total_trades = $conn->query("SELECT COUNT(*) AS total_trades FROM trades");
$row_total_trades = $result_total_trades->fetch(PDO::FETCH_ASSOC);
$total_trades = $row_total_trades['total_trades'];

// Total Traded Volume (in USD)
$result_total_volume = $conn->query("SELECT COALESCE(SUM(amount), 0) AS total_volume FROM trades");
$row_total_volume = $result_total_volume->fetch(PDO::FETCH_ASSOC);
$total_volume = $row_total_volume['total_volume'];

// Close statements (PDO automatically closes when unset)
$stmt = null;
$stmt_activity = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard -Benefit Market Trade</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <!-- Optional: Link to your local Tailwind file -->
  <link rel="stylesheet" href="tailwind.min.css" />
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

      if(menuToggle){
        menuToggle.addEventListener("click", function () {
          sidebar.classList.remove("-translate-x-full");
          overlay.classList.remove("hidden");
        });
      }

      if(closeSidebar){
        closeSidebar.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
        });
      }

      if(overlay){
        overlay.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
        });
      }

      // Admin dropdowns
      const adminUserBtn = document.getElementById("adminUserBtn");
      const adminUserDropdown = document.getElementById("adminUserDropdown");
      if(adminUserBtn){
        adminUserBtn.addEventListener("click", function(e){
          e.stopPropagation();
          adminUserDropdown.classList.toggle("hidden");
        });
      }

      document.addEventListener("click", function(){
        adminUserDropdown && adminUserDropdown.classList.add("hidden");
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
    <ul class="nav-links space-y-1">
      <!-- Nav Logo -->
      <li class="navlogo flex items-center">
        <a href="admin_dashboard.php">
          <i class="ri-dashboard-line text-2xl"></i>
          <span class="text-2xl font-bold">Admin Panel</span>
        </a>
      </li>
      <hr class="my-2 border-gray-400">
      <!-- Navigation Items -->
      <li>
        <a href="admin_dashboard.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-dashboard-line text-xl"></i>
          <span class="ml-2">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="admin_users.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-user-line text-xl"></i>
          <span class="ml-2">Users</span>
        </a>
      </li>
      <li>
        <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-exchange-dollar-line text-xl"></i>
          <span class="ml-2">Trades</span>
        </a>
      </li>
      <!-- Added Deposits link -->
         <li>
        <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-wallet-3-line text-xl"></i>
          <span class="ml-2">Withdrawal</span>
        </a>
      </li>
      <!-- Added Deposits link -->
      <li>
        <a href="admin_deposit.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-bank-line text-xl"></i>
          <span class="ml-2">Deposits</span>
        </a>
      </li>
      <li>
        <a href="admin_activity.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-notification-3-line text-xl"></i>
          <span class="ml-2">Activity Log</span>
        </a>
      </li>
    
    <!-- send mail -->
    <li>
      <a target="_blank" href="https://mail.benefitsmart.xyz" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
        <i class="ri-mail-line text-xl"></i>
        <span class="ml-2">Send Mail</span>
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
    <aside id="mobileSidebar" class="fixed top-0 left-0 w-64 h-full bg-gray-700 text-white transform -translate-x-full transition-transform duration-300 lg:hidden z-50">
      <nav>
  <div class="p-4 flex justify-between items-center">
    <div class="flex items-center">
      <a href="admin_dashboard.php" class="flex items-center">
        <i class="ri-dashboard-line text-2xl"></i>
        <span class="text-2xl font-bold ml-2">Admin Panel</span>
      </a>
    </div>
    <button id="closeSidebar" class="text-white">
      <i class="ri-close-line text-2xl"></i>
    </button>
  </div>
  <div class="menu-items">
    <ul class="nav-links space-y-1">
      <li>
        <a href="admin_dashboard.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-dashboard-line text-xl"></i>
          <span class="ml-2">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="admin_users.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-user-line text-xl"></i>
          <span class="ml-2">Users</span>
        </a>
      </li>
      <li>
        <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-exchange-dollar-line text-xl"></i>
          <span class="ml-2">Trades</span>
        </a>
      </li>
         <li>
        <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-wallet-3-line text-xl"></i>
          <span class="ml-2">Withdrawal</span>
        </a>
      </li>
      <!-- Added Deposits link -->
      <li>
        <a href="admin_deposit.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-bank-line text-xl"></i>
          <span class="ml-2">Deposits</span>
        </a>
      </li>
      <li>
        <a href="admin_activity.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-notification-3-line text-xl"></i>
          <span class="ml-2">Activity Log</span>
        </a>
      </li>
    
      <!-- send mail -->
    <li>
      <a target="_blank" href="https://mail.benefitsmart.xyz" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
        <i class="ri-mail-line text-xl"></i>
        <span class="ml-2">Send Mail</span>
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
    
    <!-- Overlay for Mobile Sidebar -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden z-40"></div>
    
    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-auto">
      <!-- Desktop Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm hidden lg:flex items-center px-6 py-4 w-full">
        <div class="flex-1">
          <h1 class="text-xl font-semibold">Admin Dashboard</h1>
        </div>
        <div class="flex-1 flex justify-center">
          <a href="admin_dashboard.php" class="flex items-center">
            <i class="ri-dashboard-line text-2xl"></i>
            <span class="ml-2 text-2xl font-bold">Benefit Trade Admin</span>
          </a>
        </div>
        <div class="flex-1 flex items-center justify-end space-x-4">
          <div class="relative">
            <button id="adminUserBtn" class="text-gray-500 hover:text-gray-700 focus:outline-none">
              <i class="ri-user-line text-2xl"></i>
            </button>
            <div id="adminUserDropdown" class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg hidden z-50">
              <ul>
             
      <li>
                  <a href="admin_dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="ri-user-line mr-2"></i>Dashboard
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
      
      <!-- Mobile Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm lg:hidden w-full px-4 py-3">
        <div class="flex items-center justify-between">
          <button id="menuToggle" class="text-gray-500 focus:outline-none">
            <i class="ri-menu-line text-2xl"></i>
          </button>
          <a href="admin_dashboard.php" class="flex items-center">
            <i class="ri-dashboard-line text-2xl"></i>
            <span class="ml-2 text-2xl font-bold">Benefit Trade Admin</span>
          </a>
          <button id="mobileAdminUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
            <i class="ri-user-line text-xl text-gray-600"></i>
          </button>
        </div>
        <!-- Mobile dropdown for admin can be added similarly -->
      </header>
      
      <!-- Main Dashboard Content -->
      <main class="p-6">
        <!-- Top Grid: Admin Profile & Statistics -->
        <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Admin Profile Card -->
          <div class="bg-gray-800 shadow-xl rounded-xl p-6 flex items-center space-x-6">
            <div class="flex-shrink-0">
              <div class="w-20 h-20 rounded-full bg-gray-600 flex items-center justify-center">
                <i class="ri-user-line text-white text-4xl"></i>
              </div>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-100"><?php echo htmlspecialchars($admin['fname']); ?></h3>
              <p class="text-lg text-gray-300"><?php echo htmlspecialchars($admin['email']); ?></p>
              <p class="text-sm text-gray-400 mt-2">
                Admin since: <span class="font-medium"><?php echo date('M Y', strtotime($admin['created_at'])); ?></span>
              </p>
            </div>
          </div>
          
          <!-- Statistics Summary Card -->
          <div class="bg-gray-800 shadow-xl rounded-xl p-6">
            <h4 class="text-2xl font-bold text-gray-100 mb-4">Statistics</h4>
            <div class="grid grid-cols-1 gap-4">
              <div class="flex items-center space-x-4">
                <i class="ri-user-line text-green-400 text-4xl"></i>
                <div>
                  <p class="text-gray-300">Total Users</p>
                  <p class="text-2xl font-bold text-gray-100"><?php echo $total_users; ?></p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <i class="ri-exchange-dollar-line text-yellow-400 text-4xl"></i>
                <div>
                  <p class="text-gray-300">Total Trades</p>
                  <p class="text-2xl font-bold text-gray-100"><?php echo $total_trades; ?></p>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <i class="ri-money-dollar-circle-line text-blue-400 text-4xl"></i>
                <div>
                  <p class="text-gray-300">Total Volume</p>
                  <p class="text-2xl font-bold text-gray-100">$<?php echo number_format($total_volume, 2); ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <section class="mt-8 max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Recent Activity Log</h4>
  <ul class="space-y-4 text-gray-300">
    <?php
      if ($result_activity && $result_activity->num_rows > 0) {
          while ($activity = $result_activity->fetch_assoc()) {
              echo '<li class="border-b border-gray-700 pb-2">';
              echo '<p class="text-sm">' . htmlspecialchars($activity["activity"]) . '</p>';
              echo '<span class="text-xs text-gray-400">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
              echo '</li>';
          }
      } else {
          echo '<li class="text-center text-sm">No recent activity found.</li>';
      }
      // Only close the statement if it exists
      if ($stmt_activity) {
          $stmt_activity->close();
      }
    ?>
  </ul>
</section>
      </main>
    </div>
  </div>
</body>
</html>
