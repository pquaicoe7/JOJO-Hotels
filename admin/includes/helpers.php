<?php
function start_session($name){
  if (session_status() === PHP_SESSION_NONE){
    session_name($name);
    session_start();
  }
}
function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function csrf_token(){
  if (empty($_SESSION['csrf'])) { $_SESSION['csrf']=bin2hex(random_bytes(32)); }
  return $_SESSION['csrf'];
}
function csrf_field(){
  $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
  echo "<input type='hidden' name='csrf' value='{$t}'>";
}
function verify_csrf(){
  if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
    http_response_code(419); die('Invalid CSRF token');
  }
}
function flash($key,$val=null){
  if($val!==null){ $_SESSION['flash'][$key]=$val; return; }
  $v = $_SESSION['flash'][$key] ?? null; unset($_SESSION['flash'][$key]); return $v;
}
