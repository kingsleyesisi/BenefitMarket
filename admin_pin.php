<?php
// Enable Error messages for Debugging 
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'config.php';

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Process Withdrawal PIN Generation/Update
$notification_pin = '';
$notificationType_pin = '';
$generated_pin = null;

if (isset($_POST['generate_pin'])) {
  $user_id_pin = filter_input(INPUT_POST, 'user_id_pin', FILTER_VALIDATE_INT);
  
  if ($user_id_pin) {
      try {
          // Generate a random 6-digit PIN
          $new_pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
          
          // Update the user's withdrawal PIN
          $stmt = $conn->prepare("UPDATE users SET withdrawal_pin = :pin WHERE user_id = :user_id");
          $stmt->execute([
              ':pin' => $new_pin,
              ':user_id' => $user_id_pin
          ]);
          
          if ($stmt->rowCount() > 0) {
              $notification_pin = "Withdrawal PIN generated successfully!";
              $notificationType_pin = "success";
              $generated_pin = $new_pin; // Store for display
          } else {
              $notification_pin = "No changes made - user might not exist.";
              $notificationType_pin = "warning";
          }
      } catch (PDOException $e) {
          $notification_pin = "Error generating PIN: " . $e->getMessage();
          $notificationType_pin = "error";
      }
  } else {
      $notification_pin = "Please select a user.";
      $notificationType_pin = "error";
  }
}

// Fetch users for PIN generation dropdown
$userListQuery = "SELECT user_id, fname, lname, withdrawal_pin FROM users WHERE role = 'user' ORDER BY fname, lname";
$stmt = $conn->query($userListQuery);
$pin_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Withdrawal PIN Management - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <style>
    .menu-items { padding: 1rem; }
    .nav-links li { margin: 1rem 0; }
    .nav-links li a { display: flex; align-items: center; gap: 0.5rem; }
    .logout-mode li { margin-top: 1rem; }
    @media (max-width: 1024px) {
      .desktop-nav { display: none; }
    }
  </style>
</head>
<body class="bg-gray-300 text-gray-900">
  <div class="flex h-screen">
    <!-- Desktop Sidebar -->
    <aside class="desktop-nav hidden lg:block w-64 bg-gray-700 text-white">
      <nav>
        <div class="menu-items">
          <ul class="nav-links space-y-1">
            <li class="navlogo flex items-center">
              <a href="admin_dashboard.php">
                <i class="ri-dashboard-line text-2xl"></i>
                <span class="text-2xl font-bold">Admin Panel</span>
              </a>
            </li>
            <hr class="my-2 border-gray-400">
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
              <a href="admin_withdrawal.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
                <i class="ri-wallet-3-line text-xl"></i>
                <span class="ml-2">Withdrawals</span>
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
              <a href="admin_pin.php" class="flex items-center p-1 bg-gray-600 rounded transition-colors">
                <i class="ri-lock-password-line text-xl"></i>
                <span class="ml-2">Withdrawal PINs</span>
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
              <a href="admin_withdrawal.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
                <i class="ri-wallet-3-line text-xl"></i>
                <span class="ml-2">Withdrawals</span>
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
              <a href="admin_pin.php" class="flex items-center p-1 bg-gray-600 rounded transition-colors">
                <i class="ri-lock-password-line text-xl"></i>
                <span class="ml-2">Withdrawal PINs</span>
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
      <!-- Mobile Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm lg:hidden w-full px-4 py-3">
        <div class="flex items-center justify-between">
          <button id="menuToggle" class="text-gray-500 focus:outline-none">
            <i class="ri-menu-line text-2xl"></i>
          </button>
          <a href="admin_dashboard.php" class="flex items-center">
            <i class="ri-dashboard-line text-2xl"></i>
            <span class="ml-2 text-2xl font-bold">Benefit Smart</span>
          </a>
          <div class="w-8"></div> <!-- Spacer for centering -->
        </div>
      </header>
      
      <!-- Desktop Header (Sticky) -->
      <header class="sticky top-0 z-20 bg-white shadow-sm hidden lg:flex items-center px-6 py-4 w-full">
        <div class="flex-1">
          <h1 class="text-xl font-semibold">Withdrawal PIN Management</h1>
        </div>
        <div class="flex-1 flex justify-center">
          <a href="admin_dashboard.php" class="flex items-center">
            <i class="ri-dashboard-line text-2xl"></i>
            <span class="ml-2 text-2xl font-bold">Benefit Market Trade Admin</span>
          </a>
        </div>
        <div class="flex-1 flex items-center justify-end space-x-4">
          <a href="admin_users.php" class="text-blue-600 hover:text-blue-800 flex items-center">
            <i class="ri-arrow-left-line mr-1"></i>Back to Users
          </a>
        </div>
      </header>
      
      <!-- Main Content -->
      <main class="p-6">

        <!-- Generate Withdrawal PIN Section -->
        <section class="max-w-4xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6">
          <h2 class="text-2xl font-bold text-gray-100 mb-4">
            <i class="ri-lock-password-line mr-2"></i>Generate Withdrawal PIN
          </h2>
          
          <!-- PIN Notification -->
          <?php if(!empty($notification_pin)): ?>
            <div id="notification_pin" class="p-4 rounded mb-4 <?php echo $notificationType_pin === 'success' ? 'bg-green-100 text-green-800' : 
                       ($notificationType_pin === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
              <?php echo htmlspecialchars($notification_pin); ?>
              <?php if(isset($generated_pin)): ?>
                <div class="mt-3 p-4 bg-gray-700 rounded-lg">
                  <p class="text-white font-semibold mb-2">Generated PIN:</p>
                  <div class="flex items-center gap-3">
                    <code class="flex-1 text-3xl font-mono text-center text-white bg-gray-900 py-4 rounded select-all" id="generated_pin_display">
                      <?php echo htmlspecialchars($generated_pin); ?>
                    </code>
                    <button onclick="copyGeneratedPin('<?php echo htmlspecialchars($generated_pin); ?>')" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-4 rounded-lg transition-colors">
                      <i class="ri-file-copy-line text-xl"></i>
                    </button>
                  </div>
                  <p class="mt-3 text-sm text-gray-300">
                    <i class="ri-information-line"></i> 
                    <strong>Important:</strong> Share this PIN securely with the user. They will need it to process withdrawals.
                  </p>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <form action="" method="POST" class="space-y-4">
            <!-- Search Input -->
            <div>
              <label for="user_search" class="block text-gray-300 mb-2 font-semibold">
                <i class="ri-search-line mr-1"></i>Search User:
              </label>
              <input 
                type="text" 
                id="user_search" 
                placeholder="Type to search by name..." 
                class="w-full p-3 rounded border-2 border-gray-600 bg-gray-700 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
              <p class="text-xs text-gray-400 mt-1">
                <i class="ri-information-line"></i> Search filters the dropdown below
              </p>
            </div>

            <!-- User Dropdown -->
            <div>
              <label for="user_id_pin" class="block text-gray-300 mb-2 font-semibold">
                <i class="ri-user-line mr-1"></i>Select User:
              </label>
              <select name="user_id_pin" id="user_id_pin" required 
                      class="w-full p-3 rounded border-2 border-gray-600 bg-gray-700 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Select User --</option>
                <?php
                  if (!empty($pin_users)) {
                      foreach ($pin_users as $user) {
                          $pinStatus = !empty($user['withdrawal_pin']) ? ' (PIN: ' . htmlspecialchars($user['withdrawal_pin']) . ')' : ' (No PIN)';
                          echo sprintf(
                              '<option value="%s" data-name="%s">%s%s</option>',
                              htmlspecialchars($user['user_id']),
                              htmlspecialchars(strtolower($user['fname'] . ' ' . $user['lname'])),
                              htmlspecialchars($user['fname'] . ' ' . $user['lname']),
                              $pinStatus
                          );
                      }
                  }
                ?>
              </select>
              <p class="text-xs text-gray-400 mt-2">
                <i class="ri-information-line"></i> Current PINs are shown next to each user's name
              </p>
            </div>

            <!-- Generate Button -->
            <div>
              <button type="submit" name="generate_pin" 
                      class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all duration-200 font-semibold text-lg">
                <i class="ri-refresh-line mr-2"></i>Generate / Regenerate PIN
              </button>
              <p class="text-xs text-gray-400 mt-2">
                <i class="ri-alert-line"></i> Generating a new PIN will replace any existing PIN for this user
              </p>
            </div>
          </form>
        </section>
        
        <!-- Users PIN Table Section -->
        <section class="max-w-6xl mx-auto bg-gray-800 shadow-xl rounded-xl p-6 mt-8">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-100">
              <i class="ri-shield-keyhole-line mr-2"></i>All Users & Withdrawal PINs
            </h2>
            <button onclick="location.reload()" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
              <i class="ri-refresh-line mr-2"></i>Refresh
            </button>
          </div>
          
          <!-- Search Bar -->
          <div class="mb-4">
            <input 
              type="text" 
              id="table_search" 
              placeholder="Search by name or email..." 
              class="w-full p-3 rounded-lg border-2 border-gray-600 bg-gray-700 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-300">
              <thead class="border-b-2 border-gray-700">
                <tr>
                  <th class="px-4 py-3">User ID</th>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Email</th>
                  <th class="px-4 py-3">Withdrawal PIN</th>
                  <th class="px-4 py-3">Action</th>
                </tr>
              </thead>
              <tbody id="pin_table_body">
                <?php
                  $allUsersQuery = "SELECT user_id, fname, lname, email, withdrawal_pin FROM users WHERE role = 'user' ORDER BY fname, lname";
                  $allUsersStmt = $conn->query($allUsersQuery);
                  $allUsers = $allUsersStmt->fetchAll(PDO::FETCH_ASSOC);
                  
                  if (!empty($allUsers)):
                    foreach($allUsers as $user):
                ?>
                  <tr class="border-b border-gray-700 hover:bg-gray-750 transition-colors user-row" 
                      data-name="<?php echo htmlspecialchars(strtolower($user['fname'] . ' ' . $user['lname'])); ?>"
                      data-email="<?php echo htmlspecialchars(strtolower($user['email'])); ?>">
                    <td class="px-4 py-3"><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($user['email']); ?></td>
                    <td class="px-4 py-3">
                      <?php if(!empty($user['withdrawal_pin'])): ?>
                        <div class="flex items-center gap-2">
                          <code class="bg-gray-900 px-3 py-2 rounded font-mono text-lg text-green-400 select-all" 
                                id="pin_<?php echo $user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['withdrawal_pin']); ?>
                          </code>
                        </div>
                      <?php else: ?>
                        <span class="inline-block bg-red-900 text-red-200 px-3 py-1 rounded text-xs font-semibold">
                          <i class="ri-close-circle-line mr-1"></i>Not Set
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                      <?php if(!empty($user['withdrawal_pin'])): ?>
                        <button onclick="copyPin('<?php echo htmlspecialchars($user['withdrawal_pin']); ?>', <?php echo $user['user_id']; ?>)" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg transition-colors flex items-center gap-1 text-sm">
                          <i class="ri-file-copy-line"></i>
                          <span class="copy-text-<?php echo $user['user_id']; ?>">Copy</span>
                        </button>
                      <?php else: ?>
                        <span class="text-gray-500 text-sm">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php 
                    endforeach;
                  else:
                ?>
                  <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No users found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination Controls -->
          <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-gray-400 text-sm">
              Showing <span id="current_range">0</span> of <span id="total_users">0</span> users
            </div>
            <div class="flex gap-2">
              <button id="prev_page" onclick="changePage(-1)" 
                      class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="ri-arrow-left-line"></i> Previous
              </button>
              <span id="page_info" class="text-gray-400 px-4 py-2">Page 1</span>
              <button id="next_page" onclick="changePage(1)" 
                      class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Next <i class="ri-arrow-right-line"></i>
              </button>
            </div>
          </div>
        </section>
      </main>
      
      <!-- Footer -->
      <footer class="bg-gray-800 text-gray-300 py-6 mt-8 border-t border-gray-700">
        <div class="max-w-6xl mx-auto px-6">
          <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-left">
              <p class="text-sm font-semibold">Benefit Market Trade</p>
              <p class="text-xs text-gray-500 mt-1">Admin Panel - Withdrawal PIN Management</p>
            </div>
            <div class="text-center">
              <p class="text-sm">&copy; <?php echo date('Y'); ?> All rights reserved.</p>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  
  <!-- Copy to Clipboard & Search functionality -->
  <script>
    // Pagination variables
    let currentPage = 1;
    const usersPerPage = 10;
    let allUserRows = [];
    let filteredUserRows = [];

    // Copy PIN function with visual feedback
    function copyPin(pin, userId) {
      navigator.clipboard.writeText(pin).then(() => {
        // Update button text
        const copyTextEl = document.querySelector(`.copy-text-${userId}`);
        const originalText = copyTextEl.innerHTML;
        copyTextEl.innerHTML = 'Copied!';
        
        // Show toast notification
        showToast('PIN copied to clipboard!', 'success');
        
        // Reset button text after 2 seconds
        setTimeout(() => {
          copyTextEl.innerHTML = originalText;
        }, 2000);
      }).catch(err => {
        showToast('Failed to copy PIN', 'error');
        console.error('Copy failed:', err);
      });
    }

    // Copy generated PIN function
    function copyGeneratedPin(pin) {
      navigator.clipboard.writeText(pin).then(() => {
        showToast('Generated PIN copied to clipboard!', 'success');
      }).catch(err => {
        showToast('Failed to copy PIN', 'error');
        console.error('Copy failed:', err);
      });
    }

    // Toast notification function
    function showToast(message, type = 'success') {
      const toast = document.createElement('div');
      toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2 transition-all ${
        type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
      }`;
      toast.innerHTML = `
        <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line text-xl"></i>
        <span>${message}</span>
      `;
      document.body.appendChild(toast);
      
      // Fade out and remove after 3 seconds
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Pagination function
    function updatePagination() {
      const totalUsers = filteredUserRows.length;
      const totalPages = Math.ceil(totalUsers / usersPerPage);
      const start = (currentPage - 1) * usersPerPage;
      const end = start + usersPerPage;

      // Hide all rows
      allUserRows.forEach(row => row.style.display = 'none');

      // Show only current page rows
      filteredUserRows.slice(start, end).forEach(row => row.style.display = '');

      // Update pagination info
      const currentRange = totalUsers > 0 ? `${start + 1}-${Math.min(end, totalUsers)}` : '0';
      document.getElementById('current_range').textContent = currentRange;
      document.getElementById('total_users').textContent = totalUsers;
      document.getElementById('page_info').textContent = `Page ${currentPage} of ${totalPages || 1}`;

      // Update button states
      document.getElementById('prev_page').disabled = currentPage === 1;
      document.getElementById('next_page').disabled = currentPage >= totalPages;
    }

    // Change page function
    function changePage(direction) {
      const totalPages = Math.ceil(filteredUserRows.length / usersPerPage);
      currentPage += direction;
      
      if (currentPage < 1) currentPage = 1;
      if (currentPage > totalPages) currentPage = totalPages;
      
      updatePagination();
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Initialize user rows
      allUserRows = Array.from(document.querySelectorAll('.user-row'));
      filteredUserRows = [...allUserRows];
      
      // Initial pagination setup
      updatePagination();

      // Table search functionality
      const tableSearch = document.getElementById('table_search');
      
      if (tableSearch) {
        tableSearch.addEventListener('input', function() {
          const searchTerm = this.value.toLowerCase();
          
          // Filter rows based on search
          filteredUserRows = allUserRows.filter(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            return name.includes(searchTerm) || email.includes(searchTerm);
          });
          
          // Reset to first page when searching
          currentPage = 1;
          updatePagination();
        });
      }
      
      // Form user search functionality
      const searchInput = document.getElementById('user_search');
      const userSelect = document.getElementById('user_id_pin');
      const allOptions = Array.from(userSelect.options);
      
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // Clear all options except the first (placeholder)
        userSelect.innerHTML = '';
        userSelect.appendChild(allOptions[0].cloneNode(true));
        
        // Filter and add matching options
        allOptions.slice(1).forEach(option => {
          const userName = option.getAttribute('data-name');
          if (userName && userName.includes(searchTerm)) {
            userSelect.appendChild(option.cloneNode(true));
          }
        });
        
        // If search is empty, show all options
        if (searchTerm === '') {
          userSelect.innerHTML = '';
          allOptions.forEach(option => {
            userSelect.appendChild(option.cloneNode(true));
          });
        }
      });
    });
  </script>
  
  <!-- Auto-hide success notification -->
  <?php if($notificationType_pin === 'success'): ?>
  <script>
    setTimeout(function(){
      const notification = document.getElementById('notification_pin');
      if (notification) {
        notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => notification.remove(), 500);
      }
    }, 15000); // Extended to 15 seconds to give time to copy generated PIN
  </script>
  <?php endif; ?>
  
  <!-- Mobile Sidebar Toggle Script -->
  <script>
    const menuToggle = document.getElementById('menuToggle');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const closeSidebar = document.getElementById('closeSidebar');
    const overlay = document.getElementById('overlay');

    menuToggle.addEventListener('click', () => {
      mobileSidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
    });

    closeSidebar.addEventListener('click', () => {
      mobileSidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });

    overlay.addEventListener('click', () => {
      mobileSidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    });
  </script>
</body>
</html>
