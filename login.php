<?php
session_start();
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
// ... existing code ...

<body>
    <div class="mainform">
        <div class="form-box login">
            <h2>Login</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <form action="login_process.php" method="POST">
                // ... existing code ...
            </form>
        </div>
        
        <div class="form-box register">
            <h2>Registration</h2>
            <?php if(isset($_SESSION['reg_error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_SESSION['reg_error']); ?>
                    <?php unset($_SESSION['reg_error']); ?>
                </div>
            <?php endif; ?>
            <form action="register_process.php" method="POST">
                // ... existing code ...
            </form>
        </div>
        // ... existing code ...
    </div>

    // ... existing code ...
</body>
</html>