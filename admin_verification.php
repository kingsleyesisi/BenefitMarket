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

$message = '';

// Process update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verification_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    $verification_id = (int) $_POST['verification_id'];
    $status          = trim($_POST['status']);
    
    // Update the verification record
    $sql  = "UPDATE verification
             SET status = :status
             WHERE id = :verification_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status',           $status,          PDO::PARAM_STR);
    $stmt->bindValue(':verification_id',  $verification_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $message = ($stmt->rowCount() > 0)
        ? "Verification status updated successfully."
        : "No changes made or update failed.";
}

// Retrieve all verification records along with user names
$sql = "
    SELECT
        v.id,
        v.user_id,
        u.fname,
        u.lname,
        v.document_type,
        v.file_path,
        v.status,
        v.submission_date
    FROM verification v
    LEFT JOIN users u ON v.user_id = u.user_id
    ORDER BY v.submission_date DESC
";
$stmt           = $conn->query($sql);
$verifications  = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <span class="ml-2 text-2xl font-bold">nextrade Admin</span>
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
                    <i class="ri-settings-3-line mr-2"></i> Verification                  </a>
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
            <span class="ml-2 text-2xl font-bold">nextrade Admin</span>
          </a>
          <button id="mobileAdminUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
            <i class="ri-user-line text-xl text-gray-600"></i>
          </button>
        </div>
        <!-- Mobile dropdown for admin can be added similarly -->
      </header>
      
      <!-- Main Dashboard Content -->
      <main class="p-6">
        <!-- Tailwind Notification -->
        <?php if (isset($message)) : ?>
          <div id="notification" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold"><?php echo $message; ?></strong>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
              <svg class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" onclick="document.getElementById('notification').style.display='none';" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 5.652a1 1 0 00-1.414 0L10 8.586 7.066 5.652a1 1 0 10-1.414 1.414L8.586 10l-2.934 2.934a1 1 0 101.414 1.414L10 11.414l2.934 2.934a1 1 0 101.414-1.414L11.414 10l2.934-2.934a1 1 0 000-1.414z"/>
              </svg>
            </span>
          </div>
        <?php endif; ?>

        <!-- Verification Requests Table -->
        <section class="container mx-auto p-6">
          <h2 class="text-2xl font-bold mb-6">Verification Requests</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded-lg shadow-md">
              <thead class="bg-gray-200">
                <tr>
                  <th class="px-6 py-3 text-left">ID</th>
                  <th class="px-6 py-3 text-left">User ID</th>
                  <th class="px-6 py-3 text-left">User Name</th>
                  <th class="px-6 py-3 text-left">Document Type</th>
                  <th class="px-6 py-3 text-left">Image</th>
                  <th class="px-6 py-3 text-left">Status</th>
                  <th class="px-6 py-3 text-left">Submission Date</th>
                  <th class="px-6 py-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Loop through verification records -->
                <?php foreach ($verifications as $verification) : ?>
                  <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4"><?php echo htmlspecialchars($verification['id']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($verification['user_id']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($verification['fname'] . ' ' . $verification['lname']); ?></td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($verification['document_type']); ?></td>
                    <td class="px-6 py-4">
                      <a href="<?php echo htmlspecialchars($verification['file_path']); ?>" download class="text-blue-500 hover:underline">
                        Download
                      </a>
                    </td>
                    <td class="px-6 py-4">
                      <form method="POST" class="flex items-center">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="verification_id" value="<?php echo htmlspecialchars($verification['id']); ?>">
                        <select name="status" class="border rounded p-1">
                          <option <?php if($verification['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                          <option <?php if($verification['status'] === 'Approved') echo 'selected'; ?>>Approved</option>
                          <option <?php if($verification['status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                        </select>
                    </td>
                    <td class="px-6 py-4"><?php echo htmlspecialchars($verification['submission_date']); ?></td>
                    <td class="px-6 py-4">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded">
                          Update
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>
</body>
</html>
