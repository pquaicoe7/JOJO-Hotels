<?php
session_start();
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $auth = new Auth();
    $user = $auth->login($email, $password); // now returns full user info

    if ($user) {
        // ✅ These are already set in the Auth class, but just in case:
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['success'] = "Welcome back!";
        header('Location: home.php');
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header('Location: login.php');
        exit();
    }
}
