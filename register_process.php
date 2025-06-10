<?php
session_start();
require_once 'config/config.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords match
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }

        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Store user in database with verification token
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_token, is_verified) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$username, $email, $hashed_password, $verification_token]);

        // Send verification email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        
        $verification_link = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $verification_token;
        
        $mail->Subject = 'Verify Your Email - ' . SITE_NAME;
        $mail->Body = "Hello $username,<br><br>
                      Thank you for registering with " . SITE_NAME . ".<br><br>
                      Please click the link below to verify your email address:<br>
                      <a href='$verification_link'>Verify Email</a><br><br>
                      If you didn't create this account, please ignore this email.<br><br>
                      Best regards,<br>
                      " . SITE_NAME . " Team";
        
        $mail->send();
        
        $_SESSION['message'] = "Registration successful! Please check your email to verify your account.";
        $_SESSION['message_type'] = "success";
        header("Location: login.html");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: register.html");
        exit();
    }
}
?>