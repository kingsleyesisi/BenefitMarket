<?php 
session_start();
include_once 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$message = '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    $error = "Invalid password reset token.";
} else {
    // Verify token and check expiry
    try {
        $stmt = $conn->prepare("SELECT user_id, token_expiry FROM users WHERE reset_token = :token");
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user_id = $result['user_id'];
            $token_expiry = $result['token_expiry'];

            if (strtotime($token_expiry) < time()) {
                $error = "Token expired. Please request a new password reset.";
            }
        } else {
            $error = "Invalid token.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            // Update the password and clear the reset token and expiry
            $update = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, token_expiry = NULL WHERE user_id = :user_id");
            $update->execute([
                ':password' => $$new_password,
                ':user_id' => $user_id
            ]);
            $message = "Password has been reset successfully. You may now log in.";
        } catch (PDOException $e) {
            $error = "Failed to reset password: " . $e->getMessage();
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Benefit Market Trade - Reset Password</title>
  <!-- Remix Icon CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="tailwind.min.css" />
  <style>
    .image-layer { background-color: rgba(0, 0, 0, 0.7); }
  </style>
</head>
<body class="bg-black">
  <div class="min-h-screen flex">
    <!-- Left Column with Unsplash Image -->
    <div class="flex-1 relative hidden md:block">
       <img src="./login.jpg" alt="Secure banking" class="w-full h-full object-cover ">

      <div class="absolute inset-0 flex items-center justify-center p-8 image-layer">
        <div class="text-white max-w-md">
          <h2 class="text-4xl font-bold mb-4">Benefit Market Trade</h2>
          <p class="text-xl mb-6">Reset Your Password</p>
        </div>
      </div>
    </div>
    <!-- Right Column with Reset Password Form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-gray-800 text-white">
      <div class="w-full max-w-md space-y-8">
        <div>
          <a href="login.php" class="flex items-center">
            <i class="ri-home-line text-4xl text-blue-500"></i>
            <span class="text-2xl ml-2">Benefit Market Trade</span>
          </a>
          <br>
          <h4 class="text-2xl font-bold">Reset Password</h4>
          <p class="mt-2 text-gray-200">Enter your new password.</p>
        </div>
        
        <?php if (!empty($error)): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            <?php echo $message; ?>
            <p class="mt-4 text-center">
              <a href="login.php" class="text-blue-500 hover:text-blue-400">Back to Login</a>
            </p>
          </div>
          <!-- Auto redirect to login after 5 seconds -->
          <script>
            setTimeout(function(){
              window.location.href = 'login.php';
            }, 5000);
          </script>
        <?php endif; ?>
        
        <?php if (empty($message)): ?>
        <form action="" method="POST" class="mt-8 space-y-6">
          <div class="space-y-4">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-300">New Password</label>
              <input type="password" name="password" id="password" required class="w-full px-3 py-2 border rounded-md text-black">
            </div>
            <div>
              <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" required class="w-full px-3 py-2 border rounded-md text-black">
            </div>
          </div>
          <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">Reset Password</button>
          <p class="mt-4 text-center">
            <a href="login.php" class="text-blue-500 hover:text-blue-400">Back to Login</a>
          </p>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
