<?php
session_start();

// Check if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] === 'easybank') {
    header('Location: home.php');
    exit;
}

// Rate limiting: Track failed login attempts
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

if (!isset($_SESSION['admin_login_attempts'])) {
    $_SESSION['admin_login_attempts'] = 0;
    $_SESSION['admin_login_lockout'] = 0;
}

// Check if locked out
if (time() < $_SESSION['admin_login_lockout']) {
    $remaining = $_SESSION['admin_login_lockout'] - time();
    $error = "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minutes.";
} else {
    $error = '';

if (isset($_POST['submit'])) {
    $admin_pass = getenv("ADMIN_PASSWORD") ?: "easybank";
    $password = $_POST['password'];
    
    // Simple plain text comparison
    // Support both hashed and plain text passwords for backward compatibility
        $admin_pass_hash = getenv("ADMIN_PASSWORD_HASH");
        $admin_pass_plain = getenv("ADMIN_PASSWORD") ?: "easybank";
        
        $password_valid = false;
        if ($admin_pass_hash && password_verify($password, $admin_pass_hash)) {
            $password_valid = true;
        } elseif ($password === $admin_pass_plain) {
            $password_valid = true;
        }
        
        if ($password_valid) {
        $_SESSION['login'] = "easybank";
        header('Location: home.php');
        exit;
    } else {
        // Increment failed attempts
        $_SESSION['admin_login_attempts']++;
        
        if ($_SESSION['admin_login_attempts'] >= $max_attempts) {
            $_SESSION['admin_login_lockout'] = time() + $lockout_time;
            $error = "Too many failed attempts. Locked for 15 minutes.";
        } else {
            $remaining = $max_attempts - $_SESSION['admin_login_attempts'];
            $error = "Sign in control panel error. $remaining attempts remaining.";
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EasyBank</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: bold;
        }
        .form-group label {
            font-weight: bold;
            color: #333;
        }
        .btn-login {
            background-color: #667eea;
            border: none;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #764ba2;
        }
        .error-message {
            color: #d9534f;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f2dede;
            border: 1px solid #ebcccc;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>Admin Login</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required autofocus>
                </div>
                <button type="submit" name="submit" class="btn btn-primary btn-login">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>
