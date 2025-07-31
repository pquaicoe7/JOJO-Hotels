<?php
require_once 'config.php';

class Database
{
    private $conn;

    public function connect()
    {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            return $this->conn;
        } catch (Exception $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }

    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>