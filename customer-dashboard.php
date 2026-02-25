<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('Location: page-login-pin.php');
    exit;
}

$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$account_number = $_SESSION['account_number'];
$IBAN = $_SESSION['IBAN'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>EasyBank - Customer Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        body {
            background: #f4f4f4;
            padding-top: 20px;
        }
        .dashboard-header {
            background: linear-gradient(to right, #2c3e50, #34495e);
            color: white;
            padding: 30px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .card h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .logout-btn {
            float: right;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Your EasyBank Customer Dashboard</p>
        <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <h3>Account Information</h3>
                <table class="table table-striped">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?php echo htmlspecialchars($user_name); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo htmlspecialchars($user_email); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Number:</strong></td>
                        <td><?php echo htmlspecialchars($account_number); ?></td>
                    </tr>
                    <tr>
                        <td><strong>IBAN:</strong></td>
                        <td><?php echo htmlspecialchars($IBAN); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="list-group">
                    <a href="#" class="list-group-item">
                        <h4 class="list-group-item-heading">Transfer Money</h4>
                        <p class="list-group-item-text">Send funds to another account</p>
                    </a>
                    <a href="#" class="list-group-item">
                        <h4 class="list-group-item-heading">View Transactions</h4>
                        <p class="list-group-item-text">Check your transaction history</p>
                    </a>
                    <a href="#" class="list-group-item">
                        <h4 class="list-group-item-heading">Account Settings</h4>
                        <p class="list-group-item-text">Update your account details</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>