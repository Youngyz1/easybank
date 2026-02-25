<?php
session_start();

// Check if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] === 'easybank') {
    header('Location: home.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $admin_pass = getenv("ADMIN_PASSWORD") ?: "easybank";
    $password = $_POST['password'];
    
    // Simple plain text comparison
    if ($password === $admin_pass) {
        $_SESSION['login'] = "easybank";
        header('Location: home.php');
        exit;
    } else {
        $error = "Sign in control panel error";
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
