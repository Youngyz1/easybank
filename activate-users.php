<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['login']) || $_SESSION['login'] !== 'easybank'){
    header('Location: index.php');
    exit;
}

require_once('./__SRC__/connect.php');

$obj_conn = new DATABASE_CONNECT;
$conn = $obj_conn->get_connection();

$message = '';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate CSRF token for any action
$csrf_valid = false;
if (isset($_GET['activate']) || isset($_GET['deactivate'])) {
    if (isset($_GET['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        $csrf_valid = true;
    }
}

// Activate user if requested
if(isset($_GET['activate']) && $csrf_valid){
    $user_id = intval($_GET['activate']); // Sanitize as integer
    $stmt = $conn->prepare("UPDATE customers SET is_active = 1, account_type = 'active' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if($stmt->execute()){
        $message = '<div class="alert alert-success">User activated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error activating user!</div>';
    }
    $stmt->close();
}

// Deactivate user if requested
if(isset($_GET['deactivate']) && $csrf_valid){
    $user_id = intval($_GET['deactivate']); // Sanitize as integer
    $stmt = $conn->prepare("UPDATE customers SET is_active = 0, account_type = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if($stmt->execute()){
        $message = '<div class="alert alert-warning">User deactivated!</div>';
    }
    $stmt->close();
}

// Get all inactive users
$sql = "SELECT id, firstname, lastname, email, instant_register FROM customers WHERE is_active = 0 ORDER BY instant_register DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>EasyBank - Activate Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        body { 
            padding: 20px; 
            background: #f4f4f4;
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        table { 
            margin-top: 20px; 
        }
        .btn-group-sm {
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="admin/home.php" class="btn btn-secondary" style="margin-bottom: 20px;">
        <i class="glyphicon glyphicon-arrow-left"></i> Back to Admin Home
    </a>
    
    <h2><i class="glyphicon glyphicon-user"></i> Activate User Accounts</h2>
    
    <?php echo $message; ?>
    
    <?php if($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['instant_register']); ?></td>
                            <td>
                                <a href="?activate=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-success btn-sm">
                                    <i class="glyphicon glyphicon-ok"></i> Activate
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="glyphicon glyphicon-info-sign"></i> No pending accounts to activate!
        </div>
    <?php endif; ?>
    
    <hr>
    
    <h3 style="margin-top: 40px;">Active Users</h3>
    
    <?php 
    // Get active users
    $active_sql = "SELECT id, firstname, lastname, email, instant_register FROM customers WHERE is_active = 1 ORDER BY instant_register DESC";
    $active_result = $conn->query($active_sql);
    ?>
    
    <?php if($active_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Activated On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $active_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['instant_register']); ?></td>
                            <td>
                                <a href="?deactivate=<?php echo $row['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deactivate this user?');">
                                    <i class="glyphicon glyphicon-remove"></i> Deactivate
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $conn->close(); ?>
</body>
</html>
