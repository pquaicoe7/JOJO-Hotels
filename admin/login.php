<?php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($u = $res->fetch_assoc()) {
        if (password_verify($pass, $u['password'])) {
            if (strtolower($u['role']) === 'admin') {
                session_regenerate_id(true);
                $_SESSION['admin'] = [
                    'id'    => $u['id'],
                    'name'  => $u['username'] ?? 'Admin',
                    'email' => $u['email']
                ];
                header('Location: dashboard.php'); exit;
            } else {
                flash('error', 'You are not authorized to access the admin panel.');
            }
        }
    }
    if (!headers_sent()) flash('error', 'Invalid credentials');
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="auth">
  <form method="post" class="login-card">
    <h2>JOJO Hotels — Admin</h2>
    <?php if ($m = flash('error')): ?><div class="alert"><?= e($m) ?></div><?php endif; ?>
    <?php csrf_field(); ?>
    <label>Email <input type="email" name="email" required></label>
    <label>Password <input type="password" name="password" required></label>
    <button type="submit">Sign in</button>
  </form>
</body>
</html>
