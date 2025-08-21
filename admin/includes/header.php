<?php $me = $_SESSION['admin'] ?? null; ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($config['APP_NAME']) ?></title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
  <header class="topbar">
    <div class="brand">JOJO Hotels Admin</div>
    <nav class="topnav">
      <a href="dashboard.php">Dashboard</a>
      <a href="rooms.php">Rooms</a>
      <a href="bookings.php">Bookings</a>
      <a href="revenue.php">Revenue</a>
      <a href="pickups.php">Pickups</a>
    </nav>
    <div class="spacer"></div>
    <?php if ($me): ?>
      <div class="user">Hi, <?= e($me['name'] ?? 'Admin') ?> | <a href="logout.php">Logout</a></div>
    <?php endif; ?>
  </header>
