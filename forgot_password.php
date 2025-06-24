<?php
session_start();
include_once 'config.php';

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        try {
            // Check if the email exists in the database using PDO
            $stmt = $conn->prepare("SELECT user_id, fname FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $user_id = $row['user_id'];
                $fname = $row['fname'];

                // Generate a reset token and set an expiry (1 hour from now)
                $token = bin2hex(random_bytes(16));
                $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

                // Update the user record with the reset token and expiry using PDO
                $update = $conn->prepare("UPDATE users SET reset_token = :token, token_expiry = :expiry WHERE user_id = :user_id");
                $update->execute([
                    ':token' => $token,
                    ':expiry' => $expiry,
                    ':user_id' => $user_id
                ]);

                // Send the reset email
                if (sendResetEmail($email, $fname, $token)) {
                    $message = "A password reset link has been sent to your email.";
                } else {
                    $error = "Failed to send reset email.";
                }
            } else {
                $error = "No account found with that email.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
    $conn = null; // Close the database connection
}


/**
 * Sends a password reset email using PHPMailer.
 *
 * @param string $toEmail Recipient email address.
 * @param string $fname Recipient first name.
 * @param string $token Password reset token.
 * @return bool Returns true if email is sent, false otherwise.
 */
function sendResetEmail($toEmail, $fname, $token) {
    $mail = new PHPMailer(true);
    try {
        // Define multiple SMTP accounts (adjust these values as needed)
        $smtp_accounts = [
            [
                'from_email' => 'support@benefitsmart.online',
                'username'   => 'support@benefitsmart.online',
                'password'   => 'Kingsley419.'
            ],
            [
                'from_email' => 'info@benefitsmart.online',
                'username'   => 'info@benefitsmart.online',
                'password'   => 'Kingsley419.'
            ],
         
        ];

        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.online';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Set sender and recipient
        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress($toEmail, $fname);

        // Prepare the reset link (update the domain as needed)
        $reset_link = "https://benefitsmart.online/reset_password.php?token=" . urlencode($token);

        // Email content using a blue-themed template
        $mail->isHTML(true);
        $mail->Subject = "Password Reset Request - Benefit Market Trade";
        $mail->Body = '
        <html>
            <head>
                <style>
                    .container { max-width:600px; margin:auto; padding:20px; font-family:Arial, sans-serif; background:#f7f7f7; border-radius:8px; }
                    .header { background:#4299e1; color:#fff; padding:10px; text-align:center; border-radius:8px 8px 0 0; }
                    .content { padding:20px; }
                    .button { background:#4299e1; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Password Reset Request</h2>
                    </div>
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($fname) . ',</p>
                        <p>You requested a password reset for your Benefit Market Trade account. Click the button below to reset your password:</p>
                        <p style="text-align:center;">
                            <a href="' . htmlspecialchars($reset_link) . '" class="button" style="text-decoration:none; color:white; background:#4299e1; padding:10px 20px; border-radius:5px; display:inline-block;">
                                Reset Password
                            </a>
                        </p>
                        <p>If you did not request a password reset, please ignore this email.</p>
                        <p>Regards,<br>Benefit Market Trade Team</p>
                    </div>
                </div>
            </body>
        </html>
        ';
        $mail->AltBody = "Hello $fname,\n\nYou requested a password reset for your Benefit Market Trade account. Please click the link below to reset your password:\n$reset_link\n\nIf you did not request this, please ignore this email.\n\nRegards,\nTradex Pro Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benefit Market Trade - Forgot Password</title>
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
        <!-- Left Column with Real Unsplash Image -->
        <div class="flex-1 relative hidden md:block">
            <img src="./login.jpg" alt="Secure banking" class="w-full h-full object-cover">
            <div class="absolute inset-0 flex items-center justify-center p-8 image-layer">
                <div class="text-white max-w-md">
                    <h2 class="text-4xl font-bold mb-4">Benefit Market Trade</h2>
                    <p class="text-xl mb-6">Your Number One Trading Platform</p>
                </div>
            </div>
        </div>
        <!-- Right Column with Forgot Password Form -->
        <div class="flex-1 flex items-center justify-center p-8 bg-gray-800 text-white">
            <div class="w-full max-w-md space-y-8">
                <div>
                    <a href="login.php" class="flex items-center">
                        <i class="ri-home-line text-4xl text-blue-500"></i>
                        <span class="text-2xl ml-2">Benefit Market Trade</span>
                    </a>
                    <br>
                    <h4 class="text-2xl font-bold">Forgot Password</h4>
                    <p class="mt-2 text-gray-200">Enter your email to receive a password reset link.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="POST" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
                            <input type="email" name="email" id="email" required class="w-full px-3 py-2 border rounded-md">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Send Reset Link
                    </button>
                </form>
            </div>
        </div>
    </div>
    
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
_smartsupp.key = '11a8ccf4bcac20b10ed32e93cb4201c86165ce96';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>

</body>
</html>
