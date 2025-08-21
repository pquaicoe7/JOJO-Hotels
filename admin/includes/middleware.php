<?php
function require_auth(){
  if (empty($_SESSION['admin'])) {
    header('Location: login.php'); exit;
  }
}
