<?php
session_start();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    require_once('__SRC__/connect.php');
    
    $obj_conn = new DATABASE_CONNECT;
    $conn = $obj_conn->get_connection();
    
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pin = md5($_POST['pin']);
    
    // Check if account exists, PIN matches, AND is active
    $sql = "SELECT id, firstname, email, account_number, IBAN, is_active FROM customers WHERE email='$email' AND pin='$pin'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        // Check if account is active
        if($row['is_active'] == 1){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['firstname'];
            $_SESSION['account_number'] = $row['account_number'];
            $_SESSION['IBAN'] = $row['IBAN'];
            
            $conn->close();
            header('Location: customer-dashboard.php');
            exit;
        } else {
            $error = "Your account is pending activation. Please wait for admin approval.";
        }
    } else {
        $error = "Invalid email or PIN. Please try again.";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>EasyBank - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        body {
            background-image: url("/images/bg1.jpg");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }
        .login-container {
            margin-top: 100px;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="login-box">
                <h2 align="center" style="color: #333; margin-bottom: 30px;">
                    <i class="glyphicon glyphicon-lock"></i> EasyBank Login
                </h2>
                
                <?php if($error): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Enter your email" required autocomplete="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="pin">PIN Code:</label>
                        <input type="password" class="form-control" id="pin" name="pin" 
                               placeholder="Enter your 4-digit PIN" required autocomplete="off" 
                               maxlength="4" pattern="[0-9]{4}">
                        <small class="form-text text-muted">PIN was sent to your email after registration</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;">
                        <i class="glyphicon glyphicon-log-in"></i> Login
                    </button>
                </form>
                
                <hr>
                
                <p align="center">
                    Don't have an account? 
                    <a href="page-register.php">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>