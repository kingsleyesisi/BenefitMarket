<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'config.php'; // Contains the $conn connection

// PHPMailer setup
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$message = '';
$redirect = true;
// Remove reCAPTCHA verification and proceed with form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Then your existing input sanitization
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $currency = trim($_POST['currency']);
    $referral_code = isset($_POST['referral_code']) ? trim($_POST['referral_code']) : null;


    // Validate inputs

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 8 || $password !== $confirm) {
        $errors[] = "Passwords must match and be at least 8 characters long.";
    }
    if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
        $errors[] = "Invalid phone number.";
    }
  
    if (empty($country)) {
        $errors[] = "Country is required.";
    }
    if (empty($currency)) {
        $errors[] = "Currency is required.";
    }
    if (empty($errors)) {
      try {
          // Check if email exists
          $stmt = $conn->prepare("SELECT email FROM users WHERE email = :email");
          $stmt->bindParam(':email', $email);
          $stmt->execute();

          if ($stmt->rowCount() > 0) {
              $errors[] = "Email already registered.";
          } else {
              // Generate unique values
              $unique_id = bin2hex(random_bytes(16));
              $account_id = random_int(10000000, 2147483647);
              $account_type = "Basic";
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);

              // Insert user
              $stmt = $conn->prepare("
                  INSERT INTO users 
                  (unique_id, account_id, fname, lname, email, password, phone, country, currency, account_type) 
                  VALUES (:unique_id, :account_id, :fname, :lname, :email, :password, :phone, :country, :currency, :account_type)
              ");

              $stmt->execute([
                  ':unique_id' => $unique_id,
                  ':account_id' => $account_id,
                  ':fname' => $fname,
                  ':lname' => $lname,
                  ':email' => $email,
                  ':password' => $password,
                  ':phone' => $phone,
                  ':country' => $country,
                  ':currency' => $currency,
                  ':account_type' => $account_type
              ]);

              // Get new user ID (PostgreSQL specific)
              $stmt = $conn->query("SELECT lastval()");
              $user_id = $stmt->fetchColumn();

              // Handle referral
              if ($referral_code) {
                  $stmt = $conn->prepare("SELECT user_id FROM users WHERE fname = ?");
                  $stmt->execute([$referral_code]);
                  $referrer = $stmt->fetch();

                  if ($referrer) {
                      $stmt = $conn->prepare("
                          INSERT INTO referrals 
                          (referrer_id, referred_id, email) 
                          VALUES (?, ?, ?)
                      ");
                      $stmt->execute([
                          $referrer['user_id'],
                          $user_id,
                          $email
                      ]);
                  }
              }

                
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = 'user';
                $message = "Registration successful! Welcome, $fname!";
                $redirect = true;

                if ($redirect) {
                    header("Location: user_dashboard.php");
                    exit;
                }

                    // Build additional welcome details
                    $welcomeDetails = [
                        ['label'=>'Account ID',        'value'=>$account_id],
                        ['label'=>'Account Type',      'value'=>$account_type],
                        ['label'=>'Unique ID',         'value'=>$unique_id],
                        ['label'=>'Registered Email',  'value'=>$email],
                        ['label'=>'Phone Number',      'value'=>$phone],
                        ['label'=>'Country',           'value'=>$country],
                        ['label'=>'Currency',          'value'=>$currency],
                        ['label'=>'Registration Date', 'value'=>date('Y-m-d H:i:s')],
                    ];

                    // Send welcome email with additional details
                    sendWelcomeEmail($email, $fname, $lname, $welcomeDetails);
                  }
                } catch (PDOException $e) {
                    $errors[] = "Database error: " . $e->getMessage();
                }
            }
        
            // Error handling
            if (!empty($errors)) {
                $message = implode("<br>", array_map(function($e) {
                    return "<p class='error'>$e</p>";
                }, $errors));
            }
        }
        
        // Keep your generateUniqueTradeId function with PostgreSQL changes
        function generateUniqueTradeId($pdo) {
            do {
                $trade_id = random_int(10000000, 2127483647);
                $stmt = $pdo->prepare("SELECT trade_id FROM trades WHERE trade_id = ?");
                $stmt->execute([$trade_id]);
            } while ($stmt->rowCount() > 0);
            
            return $trade_id;
        }
        

/**
 * Sends a welcome email to the newly registered user with detailed information.
 *
 * @param string $toEmail Recipient's email address.
 * @param string $fname   Recipient's first name.
 * @param string $lname   Recipient's last name.
 * @param array  $details Array of details (each an associative array with keys 'label' and 'value').
 * @return bool Returns true on success, false on failure.
 */
function sendWelcomeEmail($toEmail, $fname, $lname, $details = []) {
    $mail = new PHPMailer(true);
    try {
        // Define multiple SMTP accounts
        $smtp_accounts = [
          [
              'from_email' => 'support@benefitsmart.xyz',
              'username'   => 'support@benefitsmart.xyz',
              'password'   => 'Kingsley419.'
          ],
          [
              'from_email' => 'info@benefitsmart.xyz',
              'username'   => 'info@benefitsmart.xyz',
              'password'   => 'Kingsley419.'
          ],
       
      ];

        // Select a random SMTP account
        $index = random_int(0, count($smtp_accounts) - 1);
        $selected_account = $smtp_accounts[$index];

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'mail.benefitsmart.xyz';
        $mail->SMTPAuth   = true;
        $mail->Username   = $selected_account['username'];
        $mail->Password   = $selected_account['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        // Enable verbose SMTP debug output for troubleshooting
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'error_log';
        
        // Set sender and recipient
        $mail->setFrom($selected_account['from_email'], 'Benefit Market Trade');
        $mail->addAddress($toEmail, "$fname $lname");

        // Build details rows by looping through the details array
        $detailsRows = '';
        if (!empty($details)) {
            foreach ($details as $detail) {
                $label = htmlspecialchars($detail['label']);
                $value = htmlspecialchars($detail['value']);
                $detailsRows .= "
                  <tr>
                    <th class='py-2 px-4 border-b bg-gray-100 text-gray-800 text-left'>$label</th>
                    <td class='py-2 px-4 border-b text-gray-700'>$value</td>
                  </tr>
                ";
            }
        }

        // Email content with additional details and Tailwind-inspired inline CSS with accent border
        $mail->isHTML(true);
        $mail->Subject = "Welcome to Benefit Market Trade!";
        $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Welcome to Benefit Market Trade!</title>
  <style>
    body { background-color: #f7fafc; font-family: sans-serif; margin: 0; padding: 0; }
    .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-top: 4px solid #4299e1; border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    h1 { color: #2d3748; font-size: 24px; margin-bottom: 20px; }
    p { color: #4a5568; font-size: 16px; line-height: 1.5; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { padding: 0.75rem; border: 1px solid #e2e8f0; }
    th { background-color: #edf2f7; text-align: left; color: #2d3748; }
    .footer { margin-top: 20px; font-size: 12px; color: #a0aec0; text-align: center; }
  </style>
</head>
<body>
  <div class='container'>
    <h1>Welcome to Benefit Market Trade, $fname!</h1>
    <p>Dear $fname $lname,</p>
    <p>Thank you for registering with Benefit Market Trade. We are excited to have you on board! Below you will find your account details. Your new Basic account gives you access to our essential features. You can always upgrade your account later by contacting our support team.</p>
    <table>
      $detailsRows
    </table>
    <p>For security reasons, please keep these details safe. If you have any questions or need assistance, feel free to reply to this email or contact our support team at <a href='mailto:support@benefitsmart.xyz'>support@benefitsmart.xyz</a>.</p>
    <p>Happy Trading!</p>
    <div class='footer'>© " . date('Y') . " Benefit Market Trade. All rights reserved.</div>
  </div>
</body>
</html>
";
        $mail->AltBody = "Welcome to Benefit Market Trade, $fname! Thank you for registering. Your account details are: " .
            implode(', ', array_map(function($detail) {
                return $detail['label'] . ': ' . $detail['value'];
            }, $details)) .
            ". For assistance, please contact support@benefitsmart.xyz";
        
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
  <title>Benefit Market Trade - Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css">
  <link rel="stylesheet" href="tailwind.min.css" />
  <link rel="stylesheet" href="./remixicon/remixicon.css" />
  <style>
    .image-layer {
      background-color: rgba(0, 0, 0, 0.7);
    }
  </style>
</head>
<body onload="showTab(0)" class="bg-gray-500">
  <div class="min-h-screen flex">
    <div class="flex-1 relative hidden md:block">
        <img src="./login.jpg" alt="Secure banking" class="w-full h-full object-cover ">
      <div class="absolute inset-0 bg-black/70 flex items-center justify-center p-8 image-layer">
        <div class="text-white max-w-md">
          <h2 class="text-4xl font-bold mb-4">Join Our Community</h2>
          <p class="text-xl mb-6">Start Trading and thrive with us</p>
        </div>
      </div>
    </div>
    <!-- Right Column with Registration Form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-gray-800">
      <div class="w-full max-w-md space-y-8">
        <div>
          <a href="login.php" class="flex items-center">
            <i class="ri-home-line text-2xl text-blue-500"></i>
            <span class="text-2xl ml-2 text-white">Benefit Market Trade</span>
          </a>  
          <br>
          <h4 class="text-2xl font-bold text-white">Register Now</h4>
          <p class="mt-2 text-gray-200">
            Already have an account?
            <a href="login.php" class="font-medium text-blue-400 hover:text-blue-300">Sign in</a>
          </p>
        </div>

        <!-- Notification Section -->
        <?php if (!empty($message)): ?>
          <div class="mb-4">
            <?php
            // Use red for errors and green for success
            if (stripos($message, 'error') !== false || stripos($message, 'already') !== false || stripos($message, 'Invalid') !== false) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">' . $message . '</div>';
            } else {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">' . $message . '</div>';
            }
            ?>
          </div>
        <?php endif; ?>

       
       <!--The form-->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<script>
  
    // Form validation: show errors inline and block submit
    function validateForm() {
      const errorDiv = document.getElementById('error-message');
      errorDiv.classList.add('hidden');
      errorDiv.textContent = '';

      const pw  = document.getElementById('password').value.trim();
      const cpw = document.getElementById('confirm_password').value.trim();

      if (pw.length < 8) {
        errorDiv.textContent = 'Password must be at least 8 characters long.';
        errorDiv.classList.remove('hidden');
        return false;
      }
      if (pw !== cpw) {
        errorDiv.textContent = 'Passwords do not match.';
        errorDiv.classList.remove('hidden');
        return false;
      }
      return true; // allow submit
    }
  function getFlagEmoji(cc) {
    return cc.toUpperCase()
             .split('')
             .map(c => String.fromCodePoint(127397 + c.charCodeAt(0)))
             .join('');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const countrySelect = document.getElementById('country');
    const currencySelect = document.getElementById('currency');

    countrySelect.innerHTML = `<option>Loading countries…</option>`;
    currencySelect.innerHTML = `<option>Loading currencies…</option>`;

    fetch("https://restcountries.com/v2/all?fields=name,currencies,alpha2Code")
      .then(r => r.json())
      .then(data => {
        data.sort((a, b) => a.name.localeCompare(b.name));

        countrySelect.innerHTML = `<option value="" disabled selected>Choose Country</option>`;
        currencySelect.innerHTML = `<option value="" disabled selected>Select Currency</option>`;

        const seen = new Set();

        data.forEach(c => {
          // Country dropdown
          const optC = document.createElement('option');
          optC.value = c.name;
          optC.textContent = `${getFlagEmoji(c.alpha2Code)} ${c.name}`;
          countrySelect.appendChild(optC);

          // Currency dropdown
          if (c.currencies) {
            c.currencies.forEach(currency => {
              if (!seen.has(currency.code)) {
                seen.add(currency.code);
                const optCur = document.createElement('option');
                optCur.value = currency.code;
                optCur.textContent = `${currency.code} – ${currency.name}`;
                currencySelect.appendChild(optCur);
              }
            });
          }
        });
      })
      .catch(() => {
        countrySelect.innerHTML = `<option disabled>Could not load countries</option>`;
        currencySelect.innerHTML = `<option disabled>Could not load currencies</option>`;
      });
  });


  // // Get reference to your dob input
  // const dobInput = document.querySelector('input[name="dob"]');

  // // Calculate today's date minus 18 years
  // const today = new Date();
  // today.setFullYear(today.getFullYear() - 16);
  // const maxDate = today.toISOString().split('T')[0];

  // // Set the max attribute so the picker won’t allow dates after this
  // dobInput.addEventListener('focus', () => {
  //   dobInput.setAttribute('max', maxDate);
  // });


  </script>
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4">
  <form action="register.php" method="post"
        onsubmit="return validateForm()"
        class="bg-white p-6 rounded-xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Open Your Account</h2>

    <!-- error message container -->
    <div id="error-message" class="text-red-500 text-sm mb-4 hidden"></div>

    <div class="space-y-4">
      <input type="text" name="fname" placeholder="First Name"
             required pattern="[a-zA-Z\s'-]+"
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

      <input type="text" name="lname" placeholder="Last Name"
             required pattern="[a-zA-Z\s'-]+"
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

      <input type="email" name="email" placeholder="name@example.com"
             required
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

      <input type="number" name="phone" placeholder="Phone number"
             required pattern="^\+?[0-9]{10,15}$"
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
<!-- 
             <input
    name="dob"
    type="text"
    placeholder="Date of Birth (16+)"
    onfocus="this.type='date';"
    onblur="if(!this.value) this.type='text';"
    required
    class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
  > -->
      <select id="country" name="country" required
              class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="" disabled selected>Choose Country</option>
      </select>

      <select id="currency" name="currency" required
              class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
        <option value="" disabled selected>Select Currency</option>
      </select>

      <input type="password" id="password" name="password" placeholder="Enter Password"
             required minlength="8"
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">

      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password"
             required minlength="8"
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
<!-- 
      <?php
      // Check if referral code is present in the URL
      $referral_code = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '';
      $is_readonly = !empty($referral_code) ? 'readonly' : ''; // Make field readonly if referral code is detected
      ?>
      <input type="text" name="referral_code" placeholder="Referral Code (Optional)"
             value="<?php echo $referral_code; ?>"
             <?php echo $is_readonly; ?>
             class="w-full px-3 py-2 text-sm bg-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"> -->

      <div class="flex items-center text-sm">
        <input type="checkbox" id="remember" name="remember" class="mr-2">
        <label for="remember" class="text-gray-600">
          Remember me <a href="/privacy.php" class="text-orange-500 hover:underline">Terms &amp; Privacy Policy</a>
        </label>
      </div>
      <!-- <div class="g-recaptcha mb-4" data-sitekey="6Lf3KUIrAAAAAPk1D6AQY-NXe175D3wogR0KHd8C"></div>
      <div id="recaptcha-error" class="text-red-500 text-sm mb-4 hidden"></div> -->
      <button type="submit"
              class="w-full py-2 text-sm bg-orange-500 text-white font-semibold rounded-md hover:bg-orange-600 transition">
        Sign Up
      </button>
    </div>
    <!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->
  </form>


  <script>
  // Check reCAPTCHA
  const recaptchaResponse = grecaptcha.getResponse();
    if (recaptchaResponse.length === 0) {
        document.getElementById('recaptcha-error').textContent = 'Please complete the reCAPTCHA';
        document.getElementById('recaptcha-error').classList.remove('hidden');
        return true;
    }
    
    return true;





    // Client-side form validation for the currently visible tab and display processing notification
    function validateForm() {
      var currentTab = document.querySelector('.tab:not([style*="display: none"])');
      var inputs = currentTab.querySelectorAll('input[required]');
      for (var i = 0; i < inputs.length; i++) {
        if (!inputs[i].value.trim()) {
          alert('Please complete all required fields before proceeding.');
          return false;
        }
      }
      // Show processing notification below the register button
      document.getElementById('processingNotification').classList.remove('hidden');
      return true;
    }

    // If registration was successful, redirect after a short delay
    <?php if ($redirect): ?>
      setTimeout(function() {
        window.location.href = "user_dashboard.php";
      }, 3000);

    <?php endif; ?>
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
