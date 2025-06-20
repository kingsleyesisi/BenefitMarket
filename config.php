<?php
// Neon PostgreSQL connection details (from your connection string)
$host = 'ep-flat-moon-a87dv3zr-pooler.eastus2.azure.neon.tech';
$dbname = 'wallet';
$user = 'wallet_owner';
$password = 'npg_T8Izb3hWvAQk';
$port = '5432';

define('RECAPTCHA_SECRET', '6Lf3KUIrAAAAABXeTEBl01u496rzzg6MK8Ovu53e');

// Connect using PDO (recommended)
try {
  $conn = new PDO(
    "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
    $user,
    $password
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
?>
