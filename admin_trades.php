<?php
session_start();
include_once 'config.php';  // your PDO $conn is created here

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// CSRF Token Setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trade_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $trade_id = (int) $_POST['trade_id'];
    $trading_results = $_POST['trading_results'];

    // Update the trade record (only trading_results)
    $sql = "UPDATE trades
            SET trading_results = :trading_results
            WHERE trade_id = :trade_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':trading_results', $trading_results, PDO::PARAM_STR);
    $stmt->bindValue(':trade_id',      $trade_id,      PDO::PARAM_INT);
    $stmt->execute();

    $message = ($stmt->rowCount() > 0)
        ? "Trade updated successfully."
        : "No changes made or update failed.";
}

// Retrieve all trades for display along with user names
$sql = "
    SELECT
        t.trade_id,
        t.user_id,
        u.fname,
        u.lname,
        t.trade_category,
        t.trade_type,
        t.asset,
        t.lot_size,
        t.entry_price,
        t.amount,
        t.trade_date,
        t.trading_results
    FROM trades t
    LEFT JOIN users u ON t.user_id = u.user_id
    ORDER BY t.trade_date DESC
";
$stmt  = $conn->query($sql);
$trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - Benefit Market Trade</title>
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
              <a href="admin_withdrawal.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
                <i class="ri-wallet-3-line text-xl"></i>
                <span class="ml-2">Withdrawal</span>
              </a>
            </li>
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
            <!-- Withdrawal Link -->
            <li>
              <a href="admin_withdrawal.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
                <i class="ri-wallet-3-line text-xl"></i>
                <span class="ml-2">Withdrawal</span>
              </a>
            </li>
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
            <span class="ml-2 text-2xl font-bold">Benefit  Admin</span>
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
                    <i class="ri-checkbox-circle-line mr-2"></i>Verifications
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
            <span class="ml-2 text-2xl font-bold">Benefit  Admin</span>
          </a>
          <button id="mobileAdminUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
            <i class="ri-user-line text-xl text-gray-600"></i>
          </button>
        </div>
        <!-- Mobile dropdown for admin can be added similarly -->
      </header>
      
      <!-- Main Dashboard Content -->
      <main class="p-6">
        <h1 class="text-2xl font-bold mb-4">Admin Trade Management</h1>
        <div class="container overflow-x-auto">
          <?php if(isset($message)): ?>
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>
          <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
              <tr>
                <th class="py-3 px-4">Trade ID</th>
                <th class="py-3 px-4">User ID</th>
                <th class="py-3 px-4">User Name</th>
                <th class="py-3 px-4">Category</th>
                <th class="py-3 px-4">Type</th>
                <th class="py-3 px-4">Asset</th>
                <th class="py-3 px-4">Lot Size</th>
                <th class="py-3 px-4">Entry Price</th>
                <th class="py-3 px-4">Amount</th>
                <th class="py-3 px-4">Trade Date</th>
                <th class="py-3 px-4">Trading Results</th>
                <th class="py-3 px-4">Actions</th>
              </tr>
            </thead>
            <tbody class="text-gray-700">
              <?php foreach($trades as $trade): ?>
                <tr class="border-b">
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['trade_id']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['user_id']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['fname'] . ' ' . $trade['lname']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['trade_category']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['trade_type']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['asset']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['lot_size']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['entry_price']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['amount']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['trade_date']); ?></td>
                  <td class="py-3 px-4"><?php echo htmlspecialchars($trade['trading_results']); ?></td>
                  <td class="py-3 px-4">
                    <!-- Update button to open the modal -->
                    <button 
                      class="bg-blue-500 text-white px-3 py-1 rounded update-btn" 
                      data-trade='<?php echo json_encode($trade); ?>'>
                      Update
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <!-- Modal for updating trade details -->
          <div id="updateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded p-6 w-1/3">
              <h2 class="text-xl font-bold mb-4">Update Trade</h2>
              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="trade_id" id="modal_trade_id">
                <div class="mb-4">
                  <label for="trading_results" class="block text-gray-700">Trading Results</label>
                  <textarea name="trading_results" id="modal_trading_results" rows="3" class="mt-1 block w-full border-gray-300 rounded"></textarea>
                </div>
                <div class="flex justify-end">
                  <button type="button" id="modalClose" class="mr-2 bg-gray-500 text-white px-3 py-1 rounded">Cancel</button>
                  <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <script>
          // Handle modal popup display and form population
          document.querySelectorAll('.update-btn').forEach(button => {
            button.addEventListener('click', function() {
              const trade = JSON.parse(this.getAttribute('data-trade'));
              document.getElementById('modal_trade_id').value = trade.trade_id;
              document.getElementById('modal_trading_results').value = trade.trading_results;
              document.getElementById('updateModal').classList.remove('hidden');
            });
          });
      
          document.getElementById('modalClose').addEventListener('click', function() {
            document.getElementById('updateModal').classList.add('hidden');
          });
        </script>
      </main>
    </div>
  </div>
</body>
</html>
