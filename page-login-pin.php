<?php
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    require_once('__SRC__/connect.php');
    $obj_conn = new DATABASE_CONNECT;
    $conn = $obj_conn->get_connection();
    
    $email = $_POST['email'];
    $pin = md5($_POST['pin']);
    
    $sql = "SELECT * FROM customers WHERE email='$email' AND pin='$pin'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0){
        $_SESSION['user'] = $email;
        header('Location: customer-dashboard.php');
        exit;
    } else {
        $error = "Invalid email or PIN";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EasyBank - Login with PIN</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <h2>Login with PIN</h2>
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" class="form-control" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label>PIN:</label>
                    <input type="password" name="pin" class="form-control" required autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>