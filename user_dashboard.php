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
    case 'connected': $dotColor = 'bg-emerald-500'; break;
    case 'pending':   $dotColor = 'bg-amber-500'; break;
    default:          $dotColor = 'bg-red-500'; break;
}

// Verification status styling
$statusLower = strtolower($verificationStatus);
if ($statusLower === 'unverified' || $statusLower == 'rejected' || $statusLower === 'not connected') {
    $statusClass = 'animate-pulse text-red-400';
} elseif ($statusLower === 'approved' || $statusLower === 'verified' || $statusLower === 'active') {
    $statusClass = 'text-emerald-400';
} elseif ($statusLower === 'pending') {
    $statusClass = 'animate-pulse text-amber-400';
} else {
    $statusClass = 'text-slate-400';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Benefits Smart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />

    <!-- Updated Tailwind Config with Dark Mode -->
     <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                            950: '#020617'
                        }
                    }
                }
            }
        }
    </script>

<script>
    // Theme management functions
    function getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    
    function getSavedTheme() {
        return localStorage.getItem('theme');
    }
    
    function setTheme(theme) {
        // Determine which theme to apply
        const actualTheme = theme === 'system' ? getSystemTheme() : theme;
        
        // Update the document
        document.documentElement.classList.toggle('dark', actualTheme === 'dark');
        
        // Save the preference
        localStorage.setItem('theme', theme);
        
        // Update the switcher UI
        updateThemeSwitcher(theme);
    }
    
    function updateThemeSwitcher(theme) {
        const buttons = document.querySelectorAll('.theme-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.theme === theme) {
                btn.classList.add('active');
            }
        });
    }
    
    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = getSavedTheme();
        const initialTheme = savedTheme || 'system';
        setTheme(initialTheme);
        
        // Add event listeners to theme buttons
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                setTheme(btn.dataset.theme);
            });
        });
    });
</script>

<style>


/* Body background */
body {
    @apply bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800;
}


 /* Dark mode overrides */
 .dark body {
            background: linear-gradient(to bottom right, #0f172a, #1e293b);
            color: #e2e8f0;
        }
        
        .dark .gradient-bg {
            background: linear-gradient(135deg, #4b5563 0%, #1f2937 100%);
        }
        
        .dark .bg-white {
            background-color: #1e293b;
            border-color: #334155;
        }
        
        .dark .text-slate-800 {
            color: #e2e8f0;
        }
        
        .dark .text-slate-600 {
            color: #94a3b8;
        }
        
        .dark .text-slate-500 {
            color: #94a3b8;
        }
        
        .dark .border-slate-200 {
            border-color: #334155;
        }
        
        .dark .bg-slate-100 {
            background-color: #334155;
        }
        
        .dark .bg-slate-50 {
            background-color: #1e293b;
        }
        
        .dark header {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: #334155;
        }
        
        .dark .theme-switcher {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .dark .theme-btn {
            color: #94a3b8;
        }
        
        .dark .theme-btn.active {
            background-color: #3b82f6;
            color: white;
        }
    

/* Cards */
.bg-white {
    @apply dark:bg-slate-800;
}

/* Text colors */
.text-slate-800 {
    @apply dark:text-slate-200;
}

.text-slate-600 {
    @apply dark:text-slate-400;
}

.text-slate-500 {
    @apply dark:text-slate-500;
}

/* Headers */
header {
    @apply dark:bg-slate-900/80 dark:border-slate-700;
}

/* Buttons */
button {
    @apply dark:text-slate-300;
}
    .theme-switcher {
        position: relative;
        display: flex;
        align-items: center;
        padding: 0.25rem;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 9999px;
        gap: 0.25rem;
    }
    
    .dark .theme-switcher {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .theme-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        background: transparent;
        transition: all 0.2s ease;
    }
    
    .theme-btn:hover {
        background: rgba(0, 0, 0, 0.1);
    }
    
    .dark .theme-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .theme-btn.active {
        background: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    
    .dark .theme-btn.active {
        background: #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
</style>



    <style>
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .slide-in { animation: slideIn 0.3s ease-out; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-800">
    <!-- Mobile Sidebar Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden"></div>    
    <!-- Mobile Sidebar -->
    <aside id="mobileSidebar" class="fixed top-0 left-0 w-80 h-full bg-white dark:bg-slate-800 shadow-2xl transform -translate-x-full transition-transform duration-300 lg:hidden z-50 slide-in">        <div class="flex flex-col h-full">
            <!-- Mobile Sidebar Header -->
            <div class="flex items-center justify-between p-6 border-b border-slate-200">
                <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center">
                        <img src="img/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <span class="text-xl font-bold text-slate-800">Benefits Smart</span>
                </div>
                <button id="closeSidebar" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="ri-close-line text-xl text-slate-600"></i>
                </button>
            </div>
            
            <!-- Mobile Navigation -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto">
                <ul class="space-y-2">
                    <li><a href="user_dashboard.php" class="flex items-center px-4 py-3 text-slate-700 bg-primary-50 border-r-4 border-primary-500 rounded-l-lg font-medium"><i class="ri-dashboard-3-line text-xl mr-3 text-primary-600"></i>Dashboard</a></li>
                    <li><a href="profile.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-user-line text-xl mr-3"></i>Profile</a></li>
                    <li><a href="trades.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-bar-chart-line text-xl mr-3"></i>Trades</a></li>
                    <li><a href="deposit.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-wallet-3-line text-xl mr-3"></i>Deposit</a></li>
                    <li><a href="withdraw.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-bank-card-line text-xl mr-3"></i>Withdraw</a></li>
                    <li><a href="suscribption.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-vip-crown-line text-xl mr-3"></i>Subscriptions</a></li>
                    <li><a href="settings.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-settings-3-line text-xl mr-3"></i>Settings</a></li>
                    <li><a href="support.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-customer-service-line text-xl mr-3"></i>Support</a></li>
                </ul>
                
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="ri-logout-box-line text-xl mr-3"></i>Logout
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:block fixed left-0 top-0 w-72 h-full bg-white dark:bg-slate-800 shadow-xl z-30">        <div class="flex flex-col h-full">
            <!-- Desktop Sidebar Header -->
            <div class="p-6 border-b border-slate-200">
                <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center">
                        <img src="img/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800">Benefits Smart</h1>
                        <p class="text-sm text-slate-500">Trading Platform</p>
                    </div>
                </div>
            </div>
            
            <!-- Desktop Navigation -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto">
                <ul class="space-y-2">
                    <li><a href="user_dashboard.php" class="flex items-center px-4 py-3 text-slate-700 bg-primary-50 border-r-4 border-primary-500 rounded-l-lg font-medium"><i class="ri-dashboard-3-line text-xl mr-3 text-primary-600"></i>Dashboard</a></li>
                    <li><a href="profile.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-user-line text-xl mr-3"></i>Profile</a></li>
                    <li><a href="trades.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-bar-chart-line text-xl mr-3"></i>Trades</a></li>
                    <li><a href="deposit.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-wallet-3-line text-xl mr-3"></i>Deposit</a></li>
                    <li><a href="withdraw.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-bank-card-line text-xl mr-3"></i>Withdraw</a></li>
                    <li><a href="suscribption.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-vip-crown-line text-xl mr-3"></i>Subscriptions</a></li>
                    <li><a href="settings.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-settings-3-line text-xl mr-3"></i>Settings</a></li>
                    <li><a href="support.php" class="flex items-center px-4 py-3 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors"><i class="ri-customer-service-line text-xl mr-3"></i>Support</a></li>
                </ul>
                
                <div class="mt-8 pt-6 border-t border-slate-200">
                    <a href="logout.php" class="flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="ri-logout-box-line text-xl mr-3"></i>Logout
                    </a>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-72">
        <!-- Mobile Header -->
        <header class="lg:hidden sticky top-0 z-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-700">            <div class="flex items-center justify-between px-4 py-4">
        <div class="flex items-center justify-between px-4 py-4">
               
        <button id="menuToggle" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="ri-menu-line text-xl text-slate-600"></i>
                </button>
                <div class="flex items-center space-x-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center">
                        <img src="img/logo.png" alt="Logo" class="w-full h-full object-contain rounded-full">
                    </div>
                    <span class="font-semibold text-slate-800">Benefits Smart</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button id="mobileNotificationBtn" class="relative p-2 hover:bg-slate-100 rounded-lg transition-colors">
                        <i class="ri-notification-3-line text-xl text-slate-600"></i>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                    <button id="mobileUserBtn" class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center">
                        <i class="ri-user-line text-slate-600"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Dropdowns -->
            <div id="mobileNotificationDropdown" class="absolute top-full right-4 w-80 bg-white border border-slate-200 rounded-xl shadow-xl hidden z-50 mt-2">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-3 text-slate-800">Notifications</h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $sql = "
                            SELECT activity_date, activity FROM (
                                SELECT 
                                    trade_date AS activity_date,
                                    CONCAT('Placed a ', trade_category, ' ', trade_type, ' trade for ', INITCAP(asset), ' worth $', TO_CHAR(amount, 'FM999999999.00'), '.') AS activity
                                FROM trades WHERE user_id = $1
                                UNION ALL
                                SELECT activity_date, activity FROM activity_log WHERE user_id = $2
                                UNION ALL
                                SELECT 
                                    created_at AS activity_date,
                                    CONCAT('Made a deposit of $', TO_CHAR(amount, 'FM999999999.00'), ' ', crypto_type, ' (', status, ').') AS activity
                                FROM deposits WHERE user_id = $3
                            ) AS combined
                            ORDER BY activity_date DESC LIMIT 3
                        ";
                        $stmt_activity = $conn->prepare($sql);
                        $stmt_activity->execute([$user_id, $user_id, $user_id]);
                        $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($result_activity) > 0) {
                            foreach ($result_activity as $activity) {
                                echo '<div class="p-3 bg-slate-50 rounded-lg">';
                                echo '<p class="text-sm text-slate-700">' . htmlspecialchars($activity["activity"]) . '</p>';
                                echo '<span class="text-xs text-slate-500">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-center text-sm text-slate-500">No recent activity found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <div id="mobileUserDropdown" class="absolute top-full right-4 w-48 bg-white border border-slate-200 rounded-xl shadow-xl hidden z-50 mt-2">
                <div class="py-2">
                    <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-user-line mr-3"></i>Profile
                    </a>
                    <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        <i class="ri-settings-3-line mr-3"></i>Settings
                    </a>
                    <hr class="my-2 border-slate-200">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <i class="ri-logout-box-line mr-3"></i>Logout
                    </a>
                </div>
            </div>
        </header>

        <!-- Desktop Header -->
        <header class="hidden lg:flex sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200 px-8 py-6">
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                    <p class="text-slate-600">Welcome back, <?php echo htmlspecialchars($row['fname']); ?>!</p>
                </div>
        
                <!-- Theme Toggle -->
                <div class="flex items-center space-x-4">
                <div class="theme-switcher mr-4">
        <button class="theme-btn" data-theme="light" title="Light theme">
            <i class="ri-sun-line"></i>
        </button>
        <button class="theme-btn" data-theme="system" title="System preference">
            <i class="ri-computer-line"></i>
        </button>
        <button class="theme-btn" data-theme="dark" title="Dark theme">
            <i class="ri-moon-line"></i>
        </button>
    </div>
                    
                    <div class="relative">
                        <button id="notificationBtn" class="relative p-2 hover:bg-slate-100 rounded-xl transition-colors">
                            <i class="ri-notification-3-line text-xl text-slate-600"></i>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>
                        <div id="notificationDropdown" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-xl hidden z-50">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold mb-3 text-slate-800">Notifications</h3>
                                <div class="space-y-3 max-h-64 overflow-y-auto">
                                    <?php
                                    if (count($result_activity) > 0) {
                                        foreach ($result_activity as $activity) {
                                            echo '<div class="p-3 bg-slate-50 rounded-lg">';
                                            echo '<p class="text-sm text-slate-700">' . htmlspecialchars($activity["activity"]) . '</p>';
                                            echo '<span class="text-xs text-slate-500">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<p class="text-center text-sm text-slate-500">No recent activity found.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <button id="userBtn" class="flex items-center space-x-2 p-2 hover:bg-slate-100 rounded-xl transition-colors">
                            <div class="w-8 h-8 bg-gradient-to-r from-primary-500 to-primary-600 rounded-full flex items-center justify-center">
                                <i class="ri-user-line text-white text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-slate-700"><?php echo htmlspecialchars($row['fname']); ?></span>
                            <i class="ri-arrow-down-s-line text-slate-400"></i>
                        </button>
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl hidden z-50">
                            <div class="py-2">
                                <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <i class="ri-user-line mr-3"></i>Profile
                                </a>
                                <a href="trades.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <i class="ri-bar-chart-line mr-3"></i>Trades
                                </a>
                                <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    <i class="ri-settings-3-line mr-3"></i>Settings
                                </a>
                                <hr class="my-2 border-slate-200">
                                <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="ri-logout-box-line mr-3"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="p-4 lg:p-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <div class="gradient-bg rounded-2xl p-6 lg:p-8 text-white">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="mb-4 lg:mb-0">
                            <h2 class="text-2xl lg:text-3xl font-bold mb-2">Welcome, <?php echo htmlspecialchars($row['fname']); ?> <?php echo htmlspecialchars($row['lname']); ?>!</h2>
                            <p class="text-white/80">Here's what's happening with your trading account today.</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                              <p class="text-white/80 text-sm">Account Balance</p>
                              <p class="text-2xl font-bold"><?= formatFx($usd_balance + $profit, $fxRate, $userCurrency) ?></p>
                            </div>
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                                <i class="ri-wallet-3-line text-3xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile & Account Summary Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Profile Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 card-hover fade-in">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="ri-user-line text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-200 mb-1">
                                <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
                            </h3>
                            <p class="text-slate-600 dark:text-slate-400 mb-3"><?php echo htmlspecialchars($row['email']); ?></p>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Member since:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo date('M Y', strtotime($row['created_at'])); ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Account Type:</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-200"><?php echo htmlspecialchars($account_type); ?></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500 dark:text-slate-400">Currency:</span>
                                    <a href="/editprofile.php" class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-500"><?php echo htmlspecialchars($userCurrency); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            <!-- Trading Metrics Grid -->
            <div class="relative mb-8">
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                    <!-- Total Deposit -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <i class="ri-wallet-3-line text-emerald-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Total Deposit</p>
                                <p class="text-lg lg:text-xl font-bold text-slate-800"><?= formatFx($usd_balance, $fxRate, $userCurrency) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Withdrawal -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="ri-bank-card-line text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Total Withdrawal</p>
                                <p class="text-lg lg:text-xl font-bold text-slate-800"><?= formatFx($totalWithdrawal, $fxRate, $userCurrency) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Trades -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="ri-bar-chart-line text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Total Trades</p>
                                <p class="text-lg lg:text-xl font-bold text-slate-800"><?php echo $totalTrades; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Traded Amount -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                <i class="ri-money-dollar-circle-line text-amber-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Traded Amount</p>
                                <p class="text-lg lg:text-xl font-bold text-slate-800"><?= formatFx($totalAmount, $fxRate, $userCurrency) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Profit Amount -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="ri-line-chart-line text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Profit Amount</p>
                                <p class="text-lg lg:text-xl font-bold text-slate-800"><?= formatFx($profit, $fxRate, $userCurrency) ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Bot Status -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 lg:p-6 card-hover fade-in">
                        <div class="flex flex-col items-center text-center space-y-3">
                            <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center">
                                <i class="ri-robot-2-line text-slate-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-500 text-sm mb-1">Bot Status</p>
                                <div class="flex items-center justify-center space-x-2">
                                    <div class="w-2 h-2 rounded-full animate-pulse <?= $dotColor ?>"></div>
                                    <p class="text-sm font-medium text-slate-800"><?php echo htmlspecialchars($tradingBotStatus); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Central Trading Bot Circle -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-20 h-20 lg:w-24 lg:h-24 bg-gradient-to-r from-slate-800 to-slate-900 rounded-full flex flex-col items-center justify-center shadow-xl border-4 border-white">
                        <i class="ri-robot-2-line text-white text-2xl lg:text-3xl"></i>
                        <div class="w-3 h-3 rounded-full animate-pulse mt-1 <?= $dotColor ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Trades & Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Trades -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 fade-in">
                    <h4 class="text-xl font-bold text-slate-800 mb-4">Recent Trades</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-slate-200">
                                <tr class="text-left">
                                    <th class="pb-3 text-slate-600 font-medium">Date</th>
                                    <th class="pb-3 text-slate-600 font-medium">Asset</th>
                                    <th class="pb-3 text-slate-600 font-medium">Type</th>
                                    <th class="pb-3 text-slate-600 font-medium">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php
                                $stmt_trades = $conn->prepare("SELECT trade_date, asset, trade_category, amount FROM trades WHERE user_id = :user_id ORDER BY trade_date DESC LIMIT 5");
                                $stmt_trades->execute([':user_id' => $_SESSION['user_id']]);
                                $result_trades = $stmt_trades->fetchAll(PDO::FETCH_ASSOC);
                                if (count($result_trades) > 0) {
                                    foreach ($result_trades as $trade) {
                                        echo '<tr>';
                                        echo '<td class="py-3 text-slate-700">' . date("M d, Y", strtotime($trade["trade_date"])) . '</td>';
                                        echo '<td class="py-3 text-slate-700 capitalize font-medium">' . htmlspecialchars($trade["asset"]) . '</td>';
                                        echo '<td class="py-3"><span class="px-2 py-1 bg-primary-100 text-primary-700 rounded-lg text-xs font-medium">' . htmlspecialchars($trade["trade_category"]) . '</span></td>';
                                        echo '<td class="py-3 text-slate-700 font-medium">$' . number_format($trade["amount"], 2) . '</td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="py-8 text-center text-slate-500">No recent trades found.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 fade-in">
                    <h4 class="text-xl font-bold text-slate-800 mb-4">Recent Activity</h4>
                    <div class="space-y-4 max-h-64 overflow-y-auto">
                        <?php
                        $user_id = $_SESSION['user_id'];
                        $sql = "
                            SELECT activity_date, activity FROM (
                                SELECT 
                                    trade_date AS activity_date,
                                    CONCAT('Placed a ', trade_category, ' ', trade_type, ' trade for ', INITCAP(asset), ' worth $', TO_CHAR(amount, 'FM999999999.00'), '.') AS activity
                                FROM trades WHERE user_id = $1
                                UNION ALL
                                SELECT activity_date, activity FROM activity_log WHERE user_id = $2
                                UNION ALL
                                SELECT 
                                    created_at AS activity_date,
                                    CONCAT('Made a deposit of $', TO_CHAR(amount, 'FM999999999.00'), ' ', crypto_type, ' (', status, ').') AS activity
                                FROM deposits WHERE user_id = $3
                            ) AS combined
                            ORDER BY activity_date DESC LIMIT 5
                        ";
                        $stmt_activity = $conn->prepare($sql);
                        $stmt_activity->execute([$user_id, $user_id, $user_id]);
                        $result_activity = $stmt_activity->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($result_activity) > 0) {
                            foreach ($result_activity as $activity) {
                                echo '<div class="flex items-start space-x-3 p-3 bg-slate-50 rounded-lg">';
                                echo '<div class="w-2 h-2 bg-primary-500 rounded-full mt-2 flex-shrink-0"></div>';
                                echo '<div class="flex-1">';
                                echo '<p class="text-sm text-slate-700">' . htmlspecialchars($activity["activity"]) . '</p>';
                                echo '<span class="text-xs text-slate-500">' . date("M d, Y H:i", strtotime($activity["activity_date"])) . '</span>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-center text-slate-500 py-8">No recent activity found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Trading Charts -->
            <div class="space-y-6">
                <!-- Advanced Chart -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 fade-in">
                    <h4 class="text-xl font-bold text-slate-800 mb-4">Advanced Trading Chart</h4>
                    <div class="h-96 lg:h-[500px]">
                        <div class="tradingview-widget-container w-full h-full">
                            <div class="tradingview-widget-container__widget w-full h-full"></div>
                            <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js" async>
                            {
                              "autosize": true,
                              "symbol": "BINANCE:BTCUSDT",
                              "timezone": "exchange",
                              "theme": "light",
                              "style": "1",
                              "locale": "en",
                              "backgroundColor": "rgba(255, 255, 255, 1)",
                              "interval": "5",
                              "range": "ALL",
                              "hide_side_toolbar": false,
                              "allow_symbol_change": true,
                              "support_host": "https://www.tradingview.com"
                            }
                            </script>
                        </div>
                    </div>
                </div>

                <!-- Market Screener -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 fade-in">
                    <h4 class="text-xl font-bold text-slate-800 mb-4">Cryptocurrency Market</h4>
                    <div class="h-96 lg:h-[500px]">
                        <div class="tradingview-widget-container w-full h-full">
                            <div class="tradingview-widget-container__widget w-full h-full"></div>
                            <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-screener.js" async>
                            {
                                "width": "100%",
                                "height": "100%",
                                "defaultColumn": "performance",
                                "screener_type": "crypto_mkt",
                                "displayCurrency": "BTC",
                                "colorTheme": "light",
                                "locale": "en"
                            }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Mobile sidebar toggle
            const menuToggle = document.getElementById("menuToggle");
            const closeSidebar = document.getElementById("closeSidebar");
            const sidebar = document.getElementById("mobileSidebar");
            const overlay = document.getElementById("overlay");

            menuToggle?.addEventListener("click", function () {
                sidebar.classList.remove("-translate-x-full");
                overlay.classList.remove("hidden");
            });

            closeSidebar?.addEventListener("click", function () {
                sidebar.classList.add("-translate-x-full");
                overlay.classList.add("hidden");
            });

            overlay?.addEventListener("click", function () {
                sidebar.classList.add("-translate-x-full");
                overlay.classList.add("hidden");
            });

            // Desktop dropdowns
            const notificationBtn = document.getElementById("notificationBtn");
            const notificationDropdown = document.getElementById("notificationDropdown");
            const userBtn = document.getElementById("userBtn");
            const userDropdown = document.getElementById("userDropdown");

            notificationBtn?.addEventListener("click", function(e){
                e.stopPropagation();
                notificationDropdown.classList.toggle("hidden");
                userDropdown?.classList.add("hidden");
            });

            userBtn?.addEventListener("click", function(e){
                e.stopPropagation();
                userDropdown.classList.toggle("hidden");
                notificationDropdown?.classList.add("hidden");
            });

            // Mobile dropdowns
            const mobileNotificationBtn = document.getElementById("mobileNotificationBtn");
            const mobileNotificationDropdown = document.getElementById("mobileNotificationDropdown");
            const mobileUserBtn = document.getElementById("mobileUserBtn");
            const mobileUserDropdown = document.getElementById("mobileUserDropdown");

            mobileNotificationBtn?.addEventListener("click", function(e){
                e.stopPropagation();
                mobileNotificationDropdown.classList.toggle("hidden");
                mobileUserDropdown?.classList.add("hidden");
            });

            mobileUserBtn?.addEventListener("click", function(e){
                mobileUserDropdown.classList.toggle("hidden");
                mobileNotificationDropdown?.classList.add("hidden");
            });

            document.addEventListener("click", function(){
                notificationDropdown?.classList.add("hidden");
                userDropdown?.classList.add("hidden");
                mobileNotificationDropdown?.classList.add("hidden");
                mobileUserDropdown?.classList.add("hidden");
            });

            // Add fade-in animation to elements
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            document.querySelectorAll('.fade-in').forEach((el) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(el);
            });
        });
    </script>

    <!-- GTranslate Widget -->
    <div class="gtranslate_wrapper"></div>
    <script>
        window.gtranslateSettings = {"default_language":"en","wrapper_selector":".gtranslate_wrapper"};
    </script>
    <script src="https://cdn.gtranslate.net/widgets/latest/float.js" defer></script>

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
<nos
 <!-- Theme switcher for mobile -->
<footer class="mt-12 text-center text-gray-400 text-sm">
    <div class="py-6 border-t border-gray-600">
        <p>&copy; 2012 - <?php echo date('Y'); ?> Benefits Market Trade. All rights reserved.</p>
        <p>
            <a href="/privacy.php" class="text-primary-600 hover:underline">Terms of Service / Privacy Policy</a> |
            <a href="/contact.php" class="text-primary-600 hover:underline">Contact Us</a>
        </p>
    </div>
    <!-- Theme switcher for mobile -->
    <div class="theme-switcher flex justify-center items-center mt-4 p-2 rounded-full bg-slate-100 dark:bg-slate-700 space-x-2">
        <button class="theme-btn active" data-theme="system" title="System theme">
            <i class="ri-computer-line text-lg"></i>
        </button>
        <button class="theme-btn" data-theme="light" title="Light theme">
            <i class="ri-sun-line text-lg"></i>
        </button>
        <button class="theme-btn" data-theme="dark" title="Dark theme">
            <i class="ri-moon-line text-lg"></i>
        </button>
    </div>
</footer>

</body>
</html>
