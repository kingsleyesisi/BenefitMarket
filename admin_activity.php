<?php
session_start();
include_once 'config.php';  // your PDO $conn is created here

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$notification_activity     = '';
$notificationType_activity = '';

if (isset($_POST['update_activity_log'])) {
    // Grab & trim inputs (PDO prepared statements will handle sanitization)
    $user_id       = trim($_POST['user_id']);
    $activity      = trim($_POST['activity']);
    $activity_date = trim($_POST['activity_date']);

    // Prepare and execute the insert
    $sql  = "INSERT INTO activity_log (user_id, activity, activity_date)
             VALUES (:user_id, :activity, :activity_date)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':user_id',       $user_id,       PDO::PARAM_INT);
    $stmt->bindValue(':activity',      $activity,      PDO::PARAM_STR);
    $stmt->bindValue(':activity_date', $activity_date, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $notification_activity     = "Activity log inserted successfully.";
        $notificationType_activity = "success";
    } catch (PDOException $e) {
        // You could log $e->getMessage() somewhere
        $notification_activity     = "Error inserting activity log: " . $e->getMessage();
        $notificationType_activity = "error";
    }
}
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
      <!-- <li>
        <a href="admin_verification.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-checkbox-circle-line text-xl"></i>
          <span class="ml-2">Verifications</span>
        </a>
      </li>
    </ul> -->
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
    <li>
       
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
            <span class="ml-2 text-2xl font-bold">Benefit Admin</span>
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
            <span class="ml-2 text-2xl font-bold">Benefit Admin</span>
          </a>
          <button id="mobileAdminUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
            <i class="ri-user-line text-xl text-gray-600"></i>
          </button>
        </div>
        <!-- Mobile dropdown for admin can be added similarly -->
      </header>
      
      <!-- Main Dashboard Content -->
      <main class="p-6">
      <section class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6 mt-8">
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Update Activity Log</h4>
  
  <!-- Activity Log Notification -->
  <?php if(isset($notification_activity)): ?>
    <?php if($notificationType_activity == "success"): ?>
      <div id="notification_activity" class="bg-green-100 text-green-800 p-3 rounded mb-4">
        <?php echo htmlspecialchars($notification_activity); ?>
      </div>
    <?php else: ?>
      <div id="notification_activity" class="bg-red-100 text-red-800 p-3 rounded mb-4">
        <?php echo htmlspecialchars($notification_activity); ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  
  <!-- Update Activity Log Form -->
  <form action="" method="POST" class="space-y-4">
    <div>
      <label for="user_id" class="block text-gray-300">Select User:</label>
      <select name="user_id" id="user_id" required 
              class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">-- Select User --</option>
        <?php
          // Populate dropdown with users (only those with role 'user')
          $userListQuery = "SELECT user_id, fname, lname FROM users WHERE role = 'user' ORDER BY fname, lname";
          $stmt = $conn->prepare($userListQuery);
          $stmt->execute();
          $result_user_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if ($result_user_list) {
              foreach ($result_user_list as $userRow) {
            echo "<option value='" . htmlspecialchars($userRow['user_id']) . "'>" 
            . htmlspecialchars($userRow['fname'] . " " . $userRow['lname'])
            . "</option>";
              }
          }
        ?>
      </select>
    </div>
    <div>
      <label for="activity" class="block text-gray-300">Activity:</label>
      <textarea name="activity" id="activity" rows="3" required
                class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
    </div>
    <div>
      <label for="activity_date" class="block text-gray-300">Activity Date:</label>
      <input type="datetime-local" name="activity_date" id="activity_date" required
             class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    </div>
    <div>
      <button type="submit" name="update_activity_log" 
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none">
        Update Activity Log
      </button>
    </div>
  </form>
</section>

<?php if(isset($notification_activity) && $notificationType_activity=="success"): ?>
<script>
  // Auto-clear the success notification after 5 seconds
  setTimeout(function(){
    var notificationDiv = document.getElementById("notification_activity");
    if(notificationDiv){
        notificationDiv.remove();
    }
  }, 5000);
</script>
<?php endif; ?>

      </main>
    </div>
  </div>
</body>
</html>
