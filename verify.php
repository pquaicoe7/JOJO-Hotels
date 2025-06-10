<?php
session_start();
require_once 'config/config.php';

if (isset($_GET['token'])) {
    try {
        $token = $_GET['token'];
        
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $result = $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Email verified successfully! You can now login.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Invalid verification token or account already verified.";
            $_SESSION['message_type'] = "error";
        }
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error verifying email: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: login.html");
    exit();
}

$_SESSION['message'] = "Invalid verification request.";
$_SESSION['message_type'] = "error";
header("Location: login.html");
exit();
?>