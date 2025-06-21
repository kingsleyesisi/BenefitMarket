<?php
session_start();
include_once 'config.php'; // Ensure this uses PDO for Neon PostgreSQL

$error = ''; // Initialize error message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } else {
    $password = $_POST['password'];

    try {
      // Use PDO prepared statement for PostgreSQL
      $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email = :email");
      $stmt->bindParam(':email', $email);
      $stmt->execute();

      // Fetch the result
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user) {
        $db_password = $user['password'];
        $role = $user['role'];
        $user_id = $user['user_id'];

        // Verify password (allow both hashed and plaintext for now)
        if (password_verify($password, $db_password) || $password === $db_password) {
          $_SESSION['user_id'] = $user_id;
          $_SESSION['email'] = $email;
          $_SESSION['role'] = $role;

          // Set the timezone to UTC+1 (your local timezone)
          date_default_timezone_set('Etc/GMT-1');

          // Update last_login column to the current time in UTC+1
          $current_time = date('Y-m-d H:i:s');
          $updateStmt = $conn->prepare("UPDATE users SET last_login = :current_time WHERE user_id = :user_id");
          $updateStmt->bindParam(':current_time', $current_time);
          $updateStmt->bindParam(':user_id', $user_id);
          $updateStmt->execute();

          // Redirect based on role
          header('Location: ' . ($role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
          exit();
        } else {
          $error = "Incorrect password.";
        }
      } else {
        $error = "No account found with that email.";
      }
    } catch (PDOException $e) {
      $error = "Database error: " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefit Market Trade - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="tailwind.min.css" />
  <link rel="stylesheet" href="./remixicon/remixicon.css" />

  <style>
    .image-layer{
    background-color: rgb(0 0 0 / 0.7);
    }
    
  </style>
</head>

<body class="bg-black">

  <div class="min-h-screen flex">
    
    <div class="flex-1 relative hidden md:block">
      <img src="./login.jpg" alt="Secure banking" class="w-full h-full object-cover ">
      <!-- Dark overlay -->
      <div class="absolute inset-0 bg-black/70 flex items-center justify-center p-8 image-layer">
        <div class="text-white max-w-md">
          <h2 class="text-4xl font-bold mb-4">Welcome to Benefit Market Trade </h2>
          <p class="text-xl mb-6">Your AI powered Trading Platform</p>
        </div>
      </div>
    </div>

    <!-- Right Column with Login Form -->
    <div class="flex-1 flex items-center justify-center p-8 text-white bg-gray-800">
      <div class="w-full max-w-md space-y-8">
        <div>
              <a href="index.php" class="flex items-center">
            <i class="ri-home-line text-2xl text-blue-500"></i>
            <span class="text-2xl ml-2 text-white">Benefit Market Trade</span>
          </a>
          <br>
          <h4 class="text-2xl font-bold text-white">Sign in to your account</h4>
          <p class="mt-2 text-gray-200">
            Don't have an account?
            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">Get started</a>
          </p>
        </div>
        
        <?php if (isset($error)): ?>
          <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form class="mt-8 space-y-6 text-gray-700" action="" method="POST">
          <div class="space-y-4">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-300">Email address</label>
              <input id="email" name="email" type="email" required class="w-full px-3 py-2 border rounded-md">
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
              <input id="password" name="password" type="password" required class="w-full px-3 py-2 border rounded-md">
            </div>
          </div>
          <!-- Inside the form tag, add this before the submit button -->
          <!-- <div class="g-recaptcha mb-4" data-sitekey="6Lf3KUIrAAAAAPk1D6AQY-NXe175D3wogR0KHd8C"></div>
          <div id="recaptcha-error" class="text-red-500 text-sm mb-4 hidden"></div> -->

<!-- Add this script before closing </form> tag -->
<!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->
          <button type="submit" name="login" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            Sign in
          </button>
          <!-- Forgot Password Link -->
          <p class="text-center mt-4">
            <a href="forgot_password.php" class="font-medium text-blue-600 hover:text-blue-500">Forgot Password?</a>
          </p>
        </form>
        
        <div class="text-center">
          <!-- Additional content can go here -->
        </div>
      </div>
    </div>
  </div>
  
  <script>
function validateLoginForm() {
    const recaptchaResponse = grecaptcha.getResponse();
    if (recaptchaResponse.length === 0) {
        document.getElementById('recaptcha-error').textContent = 'Please complete the reCAPTCHA';
        document.getElementById('recaptcha-error').classList.remove('hidden');
        return true;
    }
    return true;
}
</script>
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
_smartsupp.key = 'dc15533bf1aa14311d8189fbfd7312a2d14486b5';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>

  <noscript>Powered by <a href="https://www.smartsupp.com" target="_blank">Smartsupp</a></noscript>

</body>


</html>
