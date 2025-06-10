<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $auth = new Auth();
    if($auth->login($email, $password)) {
        $_SESSION['success'] = "Welcome back!";
        header('Location: home.html');
        exit();
    } else {
        $_SESSION['error'] = "Invalid email or password";
        header('Location: login.html');
        exit();
    }
}
?>