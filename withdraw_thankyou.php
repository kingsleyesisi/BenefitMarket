<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Withdrawal Request Received - Benefit Market Trade</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Remix Icon CDN -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="tailwind.min.css" />
</head>
<body class="bg-gray-300 text-gray-900">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 text-white p-8 rounded-xl shadow-xl max-w-md w-full text-center">
      <h1 class="text-3xl font-bold mb-4">Thank You!</h1>
      <p class="mb-6">Your withdrawal request has been received and is pending processing. We will notify you once it is completed.</p>
      <a href="user_dashboard.php" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded transition-colors">Return to Dashboard</a>
    </div>
  </div>
</body>

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

</html>
