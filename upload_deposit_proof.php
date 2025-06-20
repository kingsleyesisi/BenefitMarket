<?php
session_start();
include_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: deposit_confirm.php");
        exit();
    }

    // Retrieve deposit ID from session if not provided in POST
    if (!isset($_POST['deposit_id']) || !isset($_POST['user_id'])) {
        if (isset($_SESSION['last_deposit_id']) && isset($_SESSION['user_id'])) {
            $deposit_id = $_SESSION['last_deposit_id'];
            $user_id = $_SESSION['user_id'];
        } else {
            header("Location: deposit_confirm.php");
            exit();
        }
    } else {
        $deposit_id = (int) $_POST['deposit_id'];
        $user_id = (int) $_POST['user_id'];
    }

    // Process file upload
    if (isset($_FILES['depositProof']) && $_FILES['depositProof']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_name = $_FILES['depositProof']['name'];
        $file_tmp = $_FILES['depositProof']['tmp_name'];
        $file_size = $_FILES['depositProof']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type and size (5MB max)
        if (!in_array($file_ext, $allowed) || $file_size > 5 * 1024 * 1024) {
            header("Location: deposit_confirm.php");
            exit();
        }

        $uploads_dir = 'uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        // Create a unique file name
        $new_file_name = $deposit_id . '_' . time() . '.' . $file_ext;
        $destination = $uploads_dir . '/' . $new_file_name;

        if (!move_uploaded_file($file_tmp, $destination)) {
            header("Location: deposit_confirm.php");
            exit();
        }
        $deposit_proof = $destination;
    } else {
        header("Location: deposit_confirm.php");
        exit();
    }

    // Update the deposit record with the deposit proof file path
    $stmt = $conn->prepare("UPDATE deposits SET deposit_proof = ? WHERE deposit_id = ? AND user_id = ?");
    if (!$stmt) {
        header("Location: deposit_confirm.php");
        exit();
    }
    $stmt->bind_param("sii", $deposit_proof, $deposit_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Clear the stored deposit ID from session after successful update
    unset($_SESSION['last_deposit_id']);

    // Redirect to deposit_confirm.php
    header("Location: deposit_confirm.php");
    exit();
}
?>
