<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - EasyBank</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; width: 100%; }
        .login-container h2 { text-align: center; margin-bottom: 30px; color: #333; }
        .form-group label { font-weight: bold; }
        .btn-login { background-color: #667eea; border: none; width: 100%; padding: 10px; font-size: 16px; }
        .btn-login:hover { background-color: #764ba2; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2>Admin Login</h2>
            
            <?php
            session_start();
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $username = $_POST['username'];
                $password = $_POST['password'];
                
                // Simple hardcoded credentials (use database in production)
                if ($username === 'admin' && $password === 'easybank') {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $username;
                    header('Location: home.php');
                    exit;
                } else {
                    $error = "Invalid username or password";
                }
            }
            
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                header('Location: home.php');
                exit;
            }
            ?>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>