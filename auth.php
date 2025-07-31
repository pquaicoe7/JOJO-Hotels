<?php
session_start();
require_once 'db.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function login($email, $password)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT id, username, password FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                return true;
            }
        }
        return false;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function logout()
    {
        session_destroy();
    }
}
?>