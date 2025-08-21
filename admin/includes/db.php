<?php
class Database {
  private $conn;
  public function __construct($config){
    $this->conn = new mysqli(
      $config['DB_HOST'],
      $config['DB_USER'],
      $config['DB_PASS'],
      $config['DB_NAME']
    );
    if ($this->conn->connect_error) {
      die('DB Connection failed: '.$this->conn->connect_error);
    }
    $this->conn->set_charset('utf8mb4');
  }
  public function pdo(){ return $this->conn; }
}
