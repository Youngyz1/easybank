<?php

/*
 * Copyright (c) 2018 Barchampas Gerasimos <makindosx@gmail.com>
 * online-banking a online banking system for local businesses.
 *
 * online-banking is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * online-banking is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

session_start();

require_once('__SRC__/csrf.php');

if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$idletime = 898;

if (time() - $_SESSION['timestamp'] > $idletime) {
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
} else {
    $_SESSION['timestamp'] = time();
}

$success = false;
$error_msg = '';

if (isset($_POST['change_password'])) {
    verify_csrf_token();

    require_once('__SRC__/connect.php');
    require_once('__SRC__/secure_data.php');

    if (class_exists('DATABASE_CONNECT') && class_exists('SECURE_INPUT_DATA_AVAILABLE')) {

        $obj_conn = new DATABASE_CONNECT;
        $conn = $obj_conn->get_connection();

        $obj_secure_data = new SECURE_INPUT_DATA;

        $password        = $obj_secure_data->SECURE_DATA_ENTER($_POST['password']);
        $password_retype = $obj_secure_data->SECURE_DATA_ENTER($_POST['password_retype']);

        if ($password !== $password_retype) {
            $error_msg = "Password and Password retype do not match!";
        } else {
            // ✅ Secure password hash
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ✅ Prepared statement — no SQL injection
            $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $_SESSION['login']);
            $result = $stmt->execute();
            $stmt->close();

            if ($result) {
                $success = true;
            } else {
                $error_msg = "Password update failed. Please try again.";
            }
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Easybank</title>
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
    .alert { max-width: 550px; margin: auto; }
    </style>
</head>
<body>

<?php if ($success): ?>
    <br><br>
    <div class="alert alert-success" role="alert">
        <strong>Password changed successfully!</strong>
    </div>
    <br>
    <div class="alert alert-info" role="alert">
        <a href="account.php" target="_parent">
            <strong>I want to stay connected</strong>
        </a>
    </div>
    <br>
    <div class="alert alert-info" role="alert">
        <a href="logout.php" target="_parent">
            <strong>I want to disconnect</strong>
        </a>
    </div>

<?php elseif ($error_msg): ?>
    <br><br>
    <div class="alert alert-danger" role="alert">
        <button type="button" id="close1" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong><?= htmlspecialchars($error_msg) ?></strong>
    </div>
    <script>
    $(document).ready(function() {
        $("#close1").on("click", function() {
            window.open("account.php", "_parent");
        });
    });
    </script>

<?php else: ?>
    <!-- Change Password Form -->
    <div class="container" style="margin-top:50px; max-width:500px;">
        <h3>Change Password</h3>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Retype New Password</label>
                <input type="password" name="password_retype" class="form-control" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>