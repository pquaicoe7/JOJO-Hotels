<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default: show Login link
$userDisplay = "<a href='login.php' style='color:white; text-decoration:none;'>Login</a>";

// Logged-in: show greeting + logout button
if (!empty($_SESSION['username'])) {
    $firstName = htmlspecialchars($_SESSION['username']);
    $userDisplay = "
      <div style='display: flex; align-items: center; gap: 15px; font-size: 16px;'>
        <span style='color: #febb02;'>Hi, $firstName 👋</span>
        <form action='logout.php' method='POST' style='margin:0;'>
          <button type='submit' style='
            background-color: #febb02;
            color: #003580;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
          ' onmouseover=\"this.style.backgroundColor='#e0a800'\"
             onmouseout=\"this.style.backgroundColor='#febb02'\">
            Logout
          </button>
        </form>
      </div>
    ";
}
?>

<!-- ✅ Reusable Header -->
<div class="header" style="
  background: #003580;
  color: white;
  padding: 20px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
">
  <h1 style="margin: 0;">JOJO Hotels</h1>
  <?= $userDisplay ?>
</div>
