<?php
// Neon PostgreSQL connection details (from your connection string)
$host = 'ep-ancient-lab-a6oj1ka9-pooler.us-west-2.aws.neon.tech';
$dbname = 'benefits';
$user = 'benefits_owner';
$password = 'npg_btfWOsjDJ28a';
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
