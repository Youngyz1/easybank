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

// ✅ Load CSRF helper
require_once('__SRC__/csrf.php');
?>

<!doctype html>
<html class="no-js" lang="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Easybank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/scss/style.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <style>
        body {
            background-image: url("images/bg1.jpg");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }
        .alert { max-width: 550px; margin: auto; }
    </style>
</head>

<body>
<div class="sufee-login d-flex align-content-center flex-wrap">
    <div class="container">
        <div class="login-content">
            <div class="login-logo">
                <img src="images/logo4.png" height="130" width="27%">
                <img src="images/bg5.png" height="130" width="33%">
                <img src="images/logo5.png" height="130" width="27%">
            </div>
            <div class="login-form" style="background-color:white; border-style:solid; border-width:0.5em; border-radius:2em;">
                <form action="" method="post">
                    <h3 align="center">
                        <font color="black"><b><i>&dollar; &dollar; EASYBANK &euro; &euro;</i></b></font>
                    </h3>
                    <hr>

                    <!-- ✅ Real PHP CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="form-group">
                        <label>Email address</label>
                        <div class="input-group">
                            <span class="input-group-addon col-sm-1">
                                <i class="glyphicon glyphicon-user" style="font-size:20px;"></i>
                            </span>
                            <input type="email" name="email" class="form-control" style="font-size:17px; font-weight:bold;" placeholder="Email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-group-addon col-sm-1">
                                <i class="glyphicon glyphicon-lock" style="font-size:20px;"></i>
                            </span>
                            <input type="password" name="password" class="form-control" style="font-size:17px; font-weight:bold; color:red;" placeholder="Password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pin</label>
                        <div class="input-group">
                            <span class="input-group-addon col-sm-1">
                                <i class="glyphicon glyphicon-info-sign" style="font-size:20px;"></i>
                            </span>
                            <input type="password" name="pin" class="form-control" style="font-size:17px; font-weight:bold; color:red;" placeholder="Pin" required>
                        </div>
                    </div>

                    <div class="wrapper" align="center">
                        <button type="submit" name="submit_login" class="btn btn-success btn-flat m-b-30 m-t-30">
                            Sign in <i class="fa fa-sign-in"></i>
                        </button>
                    </div>

                    <br><br>
                    <div class="register-link m-t-15 text-center">
                        <p>Don't have account? <a href="page-register.php">Sign Up Here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/plugins.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>

<?php
error_reporting(0);
ini_set('display_errors', FALSE);

if (isset($_POST['submit_login'])) {

    // ✅ Verify CSRF token before processing anything
    verify_csrf_token();

    require_once('__SRC__/secure_data.php');

    if (class_exists('SECURE_INPUT_DATA_AVAILABLE')) {
        $obj_secure_data = new SECURE_INPUT_DATA;

        $email    = $obj_secure_data->SECURE_DATA_ENTER($_POST['email']);
        $password = $obj_secure_data->SECURE_DATA_ENTER($_POST['password']);
        $pin      = preg_replace('/[^0-9]/', '', $_POST['pin']);

        require_once('__SRC__/connect.php');

        if (class_exists('DATABASE_CONNECT')) {
            $obj_conn = new DATABASE_CONNECT;
            $conn     = $obj_conn->get_connection();

            $email           = $conn->real_escape_string($email);
            $password_hashed = md5($conn->real_escape_string($password));
            $pin_plain       = $conn->real_escape_string($pin);

            $sql    = "SELECT email, password, pin, is_active FROM customers WHERE email='$email'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if ($row['is_active'] != 1) {
                    echo "<div class='alert alert-danger' role='alert'>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                            <strong>Your account is not activated.</strong>
                          </div>";
                } elseif ($row['password'] != $password_hashed) {
                    echo "<div class='alert alert-danger' role='alert'>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                            <strong>Your login password is invalid.</strong>
                          </div>";
                } elseif ($row['pin'] != md5($pin_plain)) {
                    echo "<div class='alert alert-danger' role='alert'>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                            <strong>Your login PIN is invalid.</strong>
                          </div>";
                } else {
                    // ✅ Regenerate session ID on successful login — prevents session fixation
                    session_regenerate_id(true);

                    $_SESSION['login']     = $email;
                    $_SESSION['timestamp'] = time();

                    // ✅ Regenerate CSRF token after login for a fresh session
                    unset($_SESSION['csrf_token']);

                    echo "<script>location.href='home.php'</script>";
                }
            } else {
                echo "<div class='alert alert-danger' role='alert'>
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                        <strong>Email not found.</strong>
                      </div>";
            }

            $conn->close();
        }
    }
}
?>