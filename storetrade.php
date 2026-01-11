<?php
session_start();
include_once 'config.php';

// Check if the user is logged in and has the role 'user'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $trade_type = $_POST['trade_type'];
    $asset = $_POST['asset'];
    $lot_size = $_POST['lot_size'];
    $entry_price = $_POST['entry_price'];
    $amount = $_POST['amount'];
    $trade_date = date('Y-m-d H:i:s');

    if (is_numeric($lot_size) && is_numeric($entry_price) && is_numeric($amount)) {
        $lot_size = (float)$lot_size;
        $entry_price = (float)$entry_price;
        $amount = (float)$amount;

        try {
            $stmt = $conn->prepare("
                INSERT INTO trades (user_id, trade_type, asset, lot_size, entry_price, amount, trade_date) 
                VALUES (:user_id, :trade_type, :asset, :lot_size, :entry_price, :amount, :trade_date)
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':trade_type' => $trade_type,
                ':asset' => $asset,
                ':lot_size' => $lot_size,
                ':entry_price' => $entry_price,
                ':amount' => $amount,
                ':trade_date' => $trade_date
            ]);
            echo "Trade successfully added.";
            header("Refresh: 2;");
        } catch (PDOException $e) {
            echo "Error adding trade: " . $e->getMessage();
        }
    } else {
        echo "Invalid input values.";
    }
}
?>
