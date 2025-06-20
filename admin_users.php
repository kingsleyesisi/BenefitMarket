<?php
// Enable Error messages for Debugging 
// Enable error reporting (for debugging purposes; remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
$stmt->bindParam(':email', $email);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found.");
}

// Query to fetch all users
$query = "SELECT user_id, fname, lname, email, country, password, usd_balance, profit, last_login 
          FROM users 
          WHERE role = 'user' 
          ORDER BY created_at DESC";
$result_users = $conn->query($query);
$users = $result_users->fetchAll(PDO::FETCH_ASSOC);


// Process Balance Update
if (isset($_POST['update_balance'])) {
  $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
  $usd_balance = filter_input(INPUT_POST, 'usd_balance', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

  if ($user_id && $usd_balance !== false) {
      try {
          $stmt = $conn->prepare("UPDATE users SET usd_balance = :usd_balance WHERE user_id = :user_id");
          $stmt->execute([
              ':usd_balance' => $usd_balance,
              ':user_id' => $user_id
          ]);
          
          $notification = "User balance updated successfully.";
          $notificationType = "success";
      } catch (PDOException $e) {
          $notification = "Error updating balance: " . $e->getMessage();
          $notificationType = "error";
      }
  } else {
      $notification = "Invalid input data";
      $notificationType = "error";
  }
}

// Process Profit Update
if (isset($_POST['update_profit'])) {
  $user_id_profit = filter_input(INPUT_POST, 'user_id_profit', FILTER_VALIDATE_INT);
  $profit = filter_input(INPUT_POST, 'profit', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

  if ($user_id_profit && $profit !== false) {
      try {
          $stmt = $conn->prepare("UPDATE users SET profit = :profit WHERE user_id = :user_id");
          $stmt->execute([
              ':profit' => $profit,
              ':user_id' => $user_id_profit
          ]);
          
          $notification_profit = "User profit updated successfully.";
          $notificationType_profit = "success";
      } catch (PDOException $e) {
          $notification_profit = "Error updating profit: " . $e->getMessage();
          $notificationType_profit = "error";
      }
  } else {
      $notification_profit = "Invalid input data";
      $notificationType_profit = "error";
  }
}

// Process Trading Bot Update
if (isset($_POST['update_trading_bot'])) {
  $user_id_bot = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
  $trading_bot_status = trim($_POST['trading_bot']);

  if ($user_id_bot && !empty($trading_bot_status)) {
      try {
          $stmt = $conn->prepare("UPDATE users SET trading_bot = :trading_bot WHERE user_id = :user_id");
          $stmt->execute([
              ':trading_bot' => $trading_bot_status,
              ':user_id' => $user_id_bot
          ]);
          
          $notification_bot = "Trading bot status updated successfully.";
          $notificationType_bot = "success";
      } catch (PDOException $e) {
          $notification_bot = "Error updating trading bot status: " . $e->getMessage();
          $notificationType_bot = "error";
      }
  } else {
      $notification_bot = "Please select a user and trading bot status.";
      $notificationType_bot = "error";
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
      <!-- Withdrawal Link -->
      <li>
        <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-wallet-3-line text-xl"></i>
          <span class="ml-2">Withdrawal</span>
        </a>
      </li>
      <!-- Deposits Link -->
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
      <!-- Verifications Link -->
      <li>
        <a href="admin_verification.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-checkbox-circle-line text-xl"></i>
          <span class="ml-2">Verifications</span>
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
      <li>
        <a href="admin_verification.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
          <i class="ri-checkbox-circle-line text-xl"></i>
          <span class="ml-2">Verifications</span>
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
            <span class="ml-2 text-2xl font-bold">Benefit Market Trade Admin</span>
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
                  <a href="admin_verification.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class=" class="ri-checkbox-circle-line mr-2"></i> Verifications
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
            <span class="ml-2 text-2xl font-bold">Benefit Market Trade Admin</span>
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
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Users</h4>
  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-300">
      <thead class="border-b border-gray-700">
        <tr>
          <th class="px-4 py-2">User ID</th>
          <th class="px-4 py-2">Name</th>
          <th class="px-4 py-2">Email</th>
          <th class="px-4 py-2">Country</th>
          <th class="px-4 py-2">Password</th>
          <th class="px-4 py-2"> Balance</th>
          <th class="px-4 py-2">Profit </th>
           <th class="px-4 py-2 whitespace-nowrap">Last Login</th>
    </tr>
      </thead>
      <tbody>
    <?php if (!empty($users)): ?>
        <?php foreach($users as $user): ?>
            <tr class="border-b border-gray-700">
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['user_id']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['country']); ?></td>
                <td class="px-4 py-2">
                    <?php
                    $pwd = $user['password'];
                    if (strlen($pwd) > 15) {
                        $displayPwd = substr($pwd, 0, 15) . '…';
                    } else {
                        $displayPwd = $pwd;
                    }
                    echo htmlspecialchars($displayPwd);
                    ?>
                </td>
                <td class="px-4 py-2">$<?php echo htmlspecialchars($user['usd_balance']); ?></td>
                <td class="px-4 py-2"><?php echo htmlspecialchars($user['profit']); ?></td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <?php
                    $datetime = new DateTime($user['last_login'], new DateTimeZone('UTC'));
                    $datetime->setTimezone(new DateTimeZone('Africa/Lagos'));
                    echo $datetime->format('M d, Y h:i A');
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="px-4 py-2 text-center">No users found.</td>
        </tr>
    <?php endif; ?>
</tbody>
    </table>
  </div>
</section>
<section class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6 mt-8">
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Update User Balance</h4>
  
  <!-- Balance Notification -->
  <?php if(isset($notification)): ?>
    <div id="notification_balance" class="<?php echo ($notificationType == 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-3 rounded mb-4">
      <?php echo htmlspecialchars($notification); ?>
    </div>
  <?php endif; ?>
  
  <form action="" method="POST" class="space-y-4">
    <div>
      <label for="user_id" class="block text-gray-300">Select User:</label>
      <select name="user_id" id="user_id" required 
              class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">-- Select User --</option>
        <?php
          $userListQuery = "SELECT user_id, fname, lname FROM users WHERE role = 'user' ORDER BY fname, lname";
          $stmt = $conn->prepare($userListQuery);
          $stmt->execute();
          $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (!empty($users)) {
              foreach ($users as $user) {
                  echo sprintf(
                      '<option value="%s">%s</option>',
                      htmlspecialchars($user['user_id']),
                      htmlspecialchars($user['fname'] . ' ' . $user['lname'])
                  );
              }
          }
        ?>
      </select>
    </div>
    <div>
      <label for="usd_balance" class="block text-gray-300">New USD Balance:</label>
      <input type="number" step="0.01" name="usd_balance" id="usd_balance" required 
             class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
    </div>
    <div>
      <button type="submit" name="update_balance" 
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none">
        Update Balance
      </button>
    </div>
  </form>
</section>
<?php
// Assuming session and database connection are already started
$notification = "";
$notificationType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_trading_bot'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $trading_bot = trim($_POST['trading_bot']);

    if (!$user_id || empty($trading_bot)) {
        $notification = "Please select a user and trading bot status.";
        $notificationType = "error";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET trading_bot = :trading_bot WHERE user_id = :user_id");
            $stmt->execute([
                ':trading_bot' => $trading_bot,
                ':user_id' => $user_id
            ]);
            
            if ($stmt->rowCount() > 0) {
                $notification = "Trading bot status updated successfully.";
                $notificationType = "success";
            } else {
                $notification = "No changes made - user might not exist or data is identical.";
                $notificationType = "warning";
            }
        } catch (PDOException $e) {
            $notification = "Failed to update trading bot status: " . $e->getMessage();
            $notificationType = "error";
        }
    }
}
?>

<section class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6 mt-8">
  <h4 class="text-2xl font-bold text-gray-100 mb-4">Update Trading Bot</h4>
  
  <?php if(!empty($notification)): ?>
    <div id="notification_balance" class="p-3 rounded mb-4 
        <?php echo $notificationType === 'success' ? 'bg-green-100 text-green-800' : 
               ($notificationType === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
      <?php echo htmlspecialchars($notification); ?>
    </div>
  <?php endif; ?>
  
  <form action="" method="POST" class="space-y-4">
    <!-- User Dropdown -->
    <div>
      <label for="user_id" class="block text-gray-300">Select User:</label>
      <select name="user_id" id="user_id" required 
              class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">-- Select User --</option>
        <?php
          $userListQuery = "SELECT user_id, fname, lname FROM users WHERE role = 'user' ORDER BY fname, lname";
          $stmt = $conn->query($userListQuery);
          $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (!empty($users)) {
              foreach ($users as $user) {
                  echo sprintf(
                      '<option value="%s">%s</option>',
                      htmlspecialchars($user['user_id']),
                      htmlspecialchars($user['fname'] . ' ' . $user['lname'])
                  );
              }
          }
        ?>
      </select>
    </div>

    <!-- Trading Bot Status -->
    <div>
      <label for="trading_bot" class="block text-gray-300">Trading Bot Status:</label>
      <select name="trading_bot" id="trading_bot" required
              class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <option value="">-- Select Status --</option>
        <option value="connected">Connected</option>
        <option value="Not connected">Not connected</option>
        <option value="Processing">Processing</option>
      </select>
    </div>

    <!-- Submit Button -->
    <div>
      <button type="submit" name="update_trading_bot" 
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none">
        Update Bot Status
      </button>
    </div>
  </form>
</section>







<!-- Update Profit Form -->
    <section class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6 mt-8">
        <h4 class="text-2xl font-bold text-gray-100 mb-4">Update User Profit</h4>
        
        <?php if(!empty($notification_profit)): ?>
            <div id="notification_profit" class="<?php echo ($notificationType_profit === 'success') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-3 rounded mb-4">
                <?php echo htmlspecialchars($notification_profit); ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label for="user_id_profit" class="block text-gray-300">Select User:</label>
                <select name="user_id_profit" id="user_id_profit" required 
                        class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">-- Select User --</option>
                    <?php
                    $userListQuery = "SELECT user_id, fname, lname FROM users WHERE role = 'user' ORDER BY fname, lname";
                    $stmt = $conn->query($userListQuery);
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($users)) {
                        foreach ($users as $user) {
                            echo sprintf(
                                '<option value="%s">%s</option>',
                                htmlspecialchars($user['user_id']),
                                htmlspecialchars($user['fname'] . ' ' . $user['lname'])
                            );
                        }
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="profit" class="block text-gray-300">New Profit:</label>
                <input type="number" step="0.01" name="profit" id="profit" required 
                       class="w-full p-2 rounded border border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <button type="submit" name="update_profit" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none">
                    Update Profit
                </button>
            </div>
        </form>
    </section>

    <!-- Notification Clear Scripts -->
    <?php if(isset($notification) && $notificationType === 'success'): ?>
    <script>
        setTimeout(function(){
            document.getElementById('notification_balance')?.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <?php if(isset($notification_profit) && $notificationType_profit === 'success'): ?>
    <script>
        setTimeout(function(){
            document.getElementById('notification_profit')?.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <?php if(isset($notification_bot) && $notificationType_bot === 'success'): ?>
    <script>
        setTimeout(function(){
            document.getElementById('notification_balance')?.remove();
        }, 5000);
    </script>
    <?php endif; ?>
      </main>
    </div>
  </div>
</body>
</html>
