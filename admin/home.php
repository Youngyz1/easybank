<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['login']) || $_SESSION['login'] !== 'easybank'){
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>EasyBank - Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .logout-btn {
            float: right;
            margin-top: 10px;
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-total { background: #3498db; color: white; }
        .stat-active { background: #27ae60; color: white; }
        .stat-pending { background: #e74c3c; color: white; }
        .list-group-item:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-header">
        <h1>EasyBank Admin Dashboard</h1>
        <p>Manage your banking system</p>
        <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
        <div style="clear: both;"></div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <h3><i class="glyphicon glyphicon-user"></i> User Management</h3>
                <p>Manage customer accounts and activation</p>
                <a href="activate-users.php" class="btn btn-primary btn-block">Manage Users</a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <h3><i class="glyphicon glyphicon-list"></i> Transactions</h3>
                <p>View all transactions</p>
                <button class="btn btn-primary btn-block" disabled>Coming Soon</button>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <h3><i class="glyphicon glyphicon-cog"></i> Settings</h3>
                <p>System configuration</p>
                <button class="btn btn-primary btn-block" disabled>Coming Soon</button>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <h3><i class="glyphicon glyphicon-signal"></i> Reports</h3>
                <p>View analytics</p>
                <button class="btn btn-primary btn-block" disabled>Coming Soon</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h3>Quick Statistics</h3>
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-box stat-total">
                            <strong>Total Users</strong>
                            <div class="stat-number">
                                <?php
                                require_once('../__SRC__/connect.php');
                                $obj_conn = new DATABASE_CONNECT;
                                $conn = $obj_conn->get_connection();
                                $result = $conn->query("SELECT COUNT(*) as count FROM customers");
                                $row = $result->fetch_assoc();
                                echo $row['count'];
                                $conn->close();
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box stat-active">
                            <strong>Active Users</strong>
                            <div class="stat-number">
                                <?php
                                $obj_conn = new DATABASE_CONNECT;
                                $conn = $obj_conn->get_connection();
                                $result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE is_active = 1");
                                $row = $result->fetch_assoc();
                                echo $row['count'];
                                $conn->close();
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box stat-pending">
                            <strong>Pending Activation</strong>
                            <div class="stat-number">
                                <?php
                                $obj_conn = new DATABASE_CONNECT;
                                $conn = $obj_conn->get_connection();
                                $result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE is_active = 0");
                                $row = $result->fetch_assoc();
                                echo $row['count'];
                                $conn->close();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <h3>Recent Registrations</h3>
                <?php
                $obj_conn = new DATABASE_CONNECT;
                $conn = $obj_conn->get_connection();
                $result = $conn->query("SELECT id, firstname, lastname, email, instant_register, is_active FROM customers ORDER BY instant_register DESC LIMIT 10");
                
                if($result->num_rows > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['instant_register']); ?></td>
                                    <td>
                                        <?php if($row['is_active'] == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
                else:
                ?>
                    <div class="alert alert-info">No registrations yet.</div>
                <?php
                endif;
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>