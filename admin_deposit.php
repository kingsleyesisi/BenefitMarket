<?php
session_start();
include_once 'config.php';  // your PDO $conn is created here

// Include PHPMailer classes (adjust paths as necessary)
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in and has the role 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// CSRF Token Setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";

// Process update if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_id'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    
    $deposit_id = (int) $_POST['deposit_id'];
    $status     = trim($_POST['status']);
    
    // Update the deposit status using a prepared statement
    $sql  = "UPDATE deposits
             SET status = :status
             WHERE deposit_id = :deposit_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':status',        $status,     PDO::PARAM_STR);
    $stmt->bindValue(':deposit_id',    $deposit_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Retrieve the updated deposit record along with user details
        $sql  = "SELECT
                    d.deposit_id,
                    d.user_id,
                    u.email,
                    u.fname,
                    u.lname,
                    d.crypto_type,
                    d.amount,
                    d.wallet_address,
                    d.status,
                    d.created_at,
                    d.deposit_proof
                 FROM deposits d
                 LEFT JOIN users u ON d.user_id = u.user_id
                 WHERE d.deposit_id = :deposit_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':deposit_id', $deposit_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Build an array of deposit details for the email
            $depositDetails = [
                ['label' => 'Deposit ID',     'value' => $row['deposit_id']],
                ['label' => 'Crypto Type',    'value' => $row['crypto_type']],
                ['label' => 'Amount',         'value' => $row['amount']],
                ['label' => 'Wallet Address', 'value' => $row['wallet_address']],
                ['label' => 'Status',         'value' => $row['status']],
                ['label' => 'Date',           'value' => $row['created_at']],
            ];
            
            // Send the deposit status update email to the user
            if (sendDepositStatusChangeEmail(
                $row['email'],
                $row['fname'],
                $row['lname'],
                $depositDetails,
                $status
            )) {
                $message = "Deposit status updated successfully and email sent.";
            } else {
                $message = "Deposit status updated, but failed to send email.";
            }
        } else {
            $message = "Deposit updated, but failed to retrieve user details.";
        }
    } else {
        $message = "No changes made or update failed.";
    }
}

// Retrieve all deposits along with user names and proof file info
$sql  = "SELECT
            d.deposit_id,
            d.user_id,
            u.fname,
            u.lname,
            d.crypto_type,
            d.amount,
            d.wallet_address,
            d.status,
            d.created_at,
            d.deposit_proof
         FROM deposits d
         LEFT JOIN users u ON d.user_id = u.user_id
         ORDER BY d.created_at DESC";
$stmt     = $conn->query($sql);
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
/**
 * Sends an email notification when the deposit status changes.
 *
 * @param string $toEmail   Recipient's email address.
 * @param string $fname     First name.
 * @param string $lname     Last name.
 * @param array  $details   Array of deposit details.
 * @param string $newStatus The new deposit status (e.g., 'completed', 'failed').
 * @param bool   $debug     Debug flag (set to false in production).
 * @return bool Returns true on success.
 */
function sendDepositStatusChangeEmail($toEmail, $fname, $lname, $details = [], $newStatus, $debug = false) {
    $mail = new PHPMailer(true);
    try {
        // Define multiple SMTP accounts (choose one at random)
        $smtp_accounts =  [
          [
              'from_email' => 'support@benefitsmart.online',
              'username'   => 'support@benefitsmart.online',
              'password'   => 'mF(UO8Ls!F'
          ],
          [
              'from_email' => 'info@benefitsmart.online',
              'username'   => 'info@benefitsmart.online',
              'password'   => 'Kingsley419.'
          ],
       
      ];
        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];
        
        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.online';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        if ($debug) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'error_log';
        }
        
        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress($toEmail, "$fname $lname");
        
        // Build HTML table rows from the deposit details
        $detailsRows = '';
        foreach ($details as $detail) {
            $label = htmlspecialchars($detail['label']);
            $value = htmlspecialchars($detail['value']);
            $detailsRows .= "<tr>
                <th style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'>$label</th>
                <td style='padding: 10px; border: 1px solid #ddd;'>$value</td>
            </tr>";
        }
        
        $mail->isHTML(true);
        $mail->Subject = "Deposit Status Update - " . ucfirst($newStatus);
        $mail->Body = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <title>Deposit Status Update</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7fafc; }
    .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border: 1px solid #ddd; }
    th { background-color: #f9f9f9; text-align: left; }
  </style>
</head>
<body>
  <div class='container'>
    <h2>Deposit Status Update</h2>
    <p>Dear $fname $lname,</p>
    <p>Your deposit status has been updated to <strong>" . ucfirst($newStatus) . "</strong>. Below are the updated details of your deposit:</p>
    <table>$detailsRows</table>
    <p>Thank you for your patience. For further inquiries, please contact our support team.</p>
    <p>Best regards,<br>Benefit Market Trade Team</p>
  </div>
</body>
</html>
";
        $mail->AltBody = "Dear $fname $lname, your deposit status has been updated to " . ucfirst($newStatus) . ". Please contact support for more details.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Deposit Status Update Email Error: " . $mail->ErrorInfo);
        if ($debug) {
            echo "Deposit Status Update Email Error: " . $mail->ErrorInfo;
        }
        return false;
    }
}
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

      if (menuToggle) {
        menuToggle.addEventListener("click", function () {
          sidebar.classList.remove("-translate-x-full");
          overlay.classList.remove("hidden");
        });
      }

      if (closeSidebar) {
        closeSidebar.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
        });
      }

      if (overlay) {
        overlay.addEventListener("click", function () {
          sidebar.classList.add("-translate-x-full");
          overlay.classList.add("hidden");
        });
      }

      // Admin dropdowns
      const adminUserBtn = document.getElementById("adminUserBtn");
      const adminUserDropdown = document.getElementById("adminUserDropdown");
      if (adminUserBtn) {
        adminUserBtn.addEventListener("click", function(e) {
          e.stopPropagation();
          adminUserDropdown.classList.toggle("hidden");
        });
      }
      document.addEventListener("click", function(){
        adminUserDropdown && adminUserDropdown.classList.add("hidden");
      });

      // Handle modal popup display and form population for deposits
      document.querySelectorAll('.update-btn').forEach(button => {
        button.addEventListener('click', function() {
          const deposit = JSON.parse(this.getAttribute('data-deposit'));
          document.getElementById('modal_deposit_id').value = deposit.deposit_id;
          // Set the text input value to the current status; if empty, default to "pending"
          document.getElementById('modal_status').value = deposit.status !== "" ? deposit.status : "pending";
          document.getElementById('updateModal').classList.remove('hidden');
        });
      });

      document.getElementById('modalClose').addEventListener('click', function() {
        document.getElementById('updateModal').classList.add('hidden');
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
              <a href="admin_trades.php" class="flex items-center p-1 hover:bg-gray-500 rounded transition-colors">
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
            <span class="ml-2 text-2xl font-bold">nextrade Admin</span>
          </a>
          <button id="mobileAdminUserBtn" class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center focus:outline-none">
            <i class="ri-user-line text-xl text-gray-600"></i>
          </button>
        </div>
      </header>
      
      <!-- Main Dashboard Content -->
      <main class="p-6">
        <div class="container mx-auto">
          <h1 class="text-2xl font-bold mb-4">Admin Deposit Management</h1>
          <?php if(isset($message)): ?>
            <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>

          <!-- Wrap the table in a div to prevent overflow -->
          <div class="overflow-x-auto">
<table class="min-w-full bg-white">
  <thead class="bg-gray-800 text-white">
    <tr>
      <th class="py-3 px-4">Deposit ID</th>
      <th class="py-3 px-4">User ID</th>
      <th class="py-3 px-4">User Name</th>
      <th class="py-3 px-4">Crypto Type</th>
      <th class="py-3 px-4">Amount</th>
      <th class="py-3 px-4">Status</th>
      <th class="py-3 px-4">Created At</th>
      <th class="py-3 px-4">Deposit Proof</th>
      <th class="py-3 px-4">Actions</th>
    </tr>
  </thead>
  <tbody class="text-gray-700">
    <?php foreach($deposits as $deposit): ?>
      <tr class="border-b">
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['deposit_id']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['user_id']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['fname'] . ' ' . $deposit['lname']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['crypto_type']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['amount']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['status']); ?></td>
        <td class="py-3 px-4"><?php echo htmlspecialchars($deposit['created_at']); ?></td>
        <td class="py-3 px-4">
          <?php if (!empty($deposit['deposit_proof'])): ?>
            <a href="<?php echo urlencode($deposit['deposit_proof']); ?>" 
               download="<?php echo htmlspecialchars($deposit['deposit_proof']); ?>" 
               class="text-blue-500 underline">
              Download Proof
            </a>
          <?php else: ?>
            No Proof Uploaded
          <?php endif; ?>
        </td>
        <td class="py-3 px-4">
          <!-- Update button to open the modal -->
          <button 
            class="bg-blue-500 text-white px-3 py-1 rounded update-btn" 
            data-deposit='<?php echo json_encode($deposit); ?>'>
            Update
          </button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


          </div>

          <!-- Modal for updating deposit status -->
          <div id="updateModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded p-6 w-1/3">
              <h2 class="text-xl font-bold mb-4">Update Deposit Status</h2>
              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="deposit_id" id="modal_deposit_id">
                <div class="mb-4">
                  <label for="status" class="block text-gray-700">Status</label>
                  <input type="text" name="status" id="modal_status" class="mt-1 block w-full border-gray-300 rounded" placeholder="Enter status" />
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
          // Handle modal popup display and form population for deposits
          document.querySelectorAll('.update-btn').forEach(button => {
            button.addEventListener('click', function() {
              const deposit = JSON.parse(this.getAttribute('data-deposit'));
              document.getElementById('modal_deposit_id').value = deposit.deposit_id;
              document.getElementById('modal_status').value = deposit.status !== "" ? deposit.status : "pending";
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
