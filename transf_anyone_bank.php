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
 *
 * online-banking is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

session_start();

// Regenerate session ID on each request (mitigates fixation attacks)
session_regenerate_id(true);

// Set secure session cookies
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict'
]);

if(!isset($_SESSION['login']))
{
    header('Location: index.php');
    exit;
}

$idletime=900;//after 15 minutes the user gets logged out

if (time()-$_SESSION['timestamp']>$idletime)
{
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
}
else
{
    $_SESSION['timestamp']=time();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>

<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>

<meta HTTP-EQUIV="REFRESH" content="600; url=/logout.php">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <title> Easybank </title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

     <link rel="shortcut icon" href="favicon.png" type="image/png">

    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/scss/style.css">
    <link href="assets/css/lib/vector-map/jqvmap.min.css" rel="stylesheet">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

<script>
function bigImg(x) {
    x.style.height = "36px";
    x.style.width = "36px";
}

function normalImg(x) {
    x.style.height = "32px";
    x.style.width = "32px";
}
</script>

<style>
.modal {
  text-align: center;
  padding: 0!important;
}

.modal:before {
  content: '';
  display: inline-block;
  height: 80%;
  vertical-align: middle;
  margin-right: -4px;
}

.modal-dialog {
  display: inline-block;
  text-align: left;
  vertical-align: middle;
}

.modal-content {
  background-color: silver;
}
.modal-body {
  background-color: white;
}

.modal-backdrop {
  background-color: grey !important;
  opacity: 1;
}

.form-control{
  border-color:;
  box-shadow: inset 0 1px 1px grey, 0 0 8px grey;
}

.alert {
  max-width: 550px;
  margin: auto;
}

.spacing {
  margin-left: 10px;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script type="text/javascript" language="javascript">
function ClearForm()
{
    document.MyForm.reset();
}

function clearForm(form) {
    var $f = $(form);
    var $f = $f.find(':input').not(':button, :submit, :reset, :hidden');
    $f.val('').attr('value','').removeAttr('checked').removeAttr('selected');
}

function countdown( elementName, minutes, seconds )
{
    var element, endTime, hours, mins, msLeft, time;

    function twoDigits( n )
    {
        return (n <= 9 ? "0" + n : n);
    }

    function updateTimer()
    {
        msLeft = endTime - (+new Date);
        if ( msLeft < 1000 ) {
            element.innerHTML = "i-code has expired. To complete the transaction, you have a new code";
        } else {
            time = new Date( msLeft );
            hours = time.getUTCHours();
            mins = time.getUTCMinutes();
            element.innerHTML = (hours ? hours + ':' + twoDigits( mins ) : mins) + ':' + twoDigits( time.getUTCSeconds() );
            setTimeout( updateTimer, time.getUTCMilliseconds() + 500 );
        }
    }

    element = document.getElementById( elementName );
    endTime = (+new Date) + 1000 * (60*minutes + seconds) + 500;
    updateTimer();
}
</script>

</head>

<body onload="ClearForm()">

        <!-- Left Panel -->

    <aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">

            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="home.php"> EasyBank </a>
                <a class="navbar-brand active" href="home.php"><img src="images/logo5.png" alt="Logo"></a>
            </div>

            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">

                    <h3 class="menu-title"> statements </h3>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-handshake-o"></i> Transactions </a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa fa-money"></i><a href="transac_deposits.php"> Deposits </a></li>
                            <li><i class="fa fa fa fa-credit-card"></i><a href="transac_withdrawals.php"> Withdrawals  </a></li>
                        </ul>
                    </li>

                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-credit-card-alt"></i> Transfers</a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-credit-card-alt"></i><a href="transf_easy_bank.php?i_code_true"> Easy Bank </a></li>
                            <li><i class="fa fa-credit-card"></i><a href="transf_anyone_bank.php?i_code_true"> Anyone Bank  </a></li>
                        </ul>
                    </li>

                    <h3 class="menu-title"> Settings </h3>

                    <li>
                        <a href="account.php"> <i class="menu-icon fa fa-user"></i> Account </a>
                    </li>

                    <li>
                        <a href="notifications.php"> <i class="menu-icon ti-bell"></i> Notifications </a>
                    </li>

                    <li>
                        <a href="statics.php"> <i class="menu-icon fa fa-bar-chart"></i> Statics </a>
                    </li>

                    <h3 class="menu-title"> Extras </h3>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="menu-icon fa fa-cogs"></i> Services </a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-info"></i><a href="informations.php"> Informations </a></li>
                            <li><i class="menu-icon fa fa-comments"></i><a href="support.php"> Support </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- Right Panel -->

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        <header id="header" class="header">

            <div class="header-menu">

                <div class="col-sm-7">
                    <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa fa-tasks"></i></a>
                    <div class="header-left">
                       <?php echo date("d.m.Y"); ?>
                        <div class="form-inline">
                            <form class="search-form">
                                <input class="form-control mr-sm-2" type="text" placeholder="Search ..." aria-label="Search">
                                <button class="search-close" type="submit"><i class="fa fa-close"></i></button>
                            </form>
                        </div>

                        <span class="spacing"><?php echo "Auto logout in ";  ?></span>
                        <span id="countdown"></span>

                        <script>
                            countdown( "countdown", 15, 0 );
                        </script>

                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="user-area dropdown float-right">

                        <a href="#" data-toggle="modal" data-target="#logoutModal">
                           <img onmouseover="bigImg(this)" onmouseout="normalImg(this)" class="user-avatar rounded-circle" src="images/menu/logout.png" title="Logout">
                        </a>

                        <!-- Modal -->
                        <div class="modal" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
                          <div class="modal-dialog modal-md">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4>Log Out <i class="fa fa-lock"></i></h4>
                              </div>
                              <div class="modal-body">
                                <p><i class="fa fa-question-circle"></i> Are you sure you want to Logout? <br /></p>
                                <div class="actionsBtns">
                                  <form>
                                   <button class="btn btn-default btn-lg" data-dismiss="modal"> Cancel
                                     <i class="fa fa-close"></i>
                                   </button>
                                   <span class="spacing"></span>
                                   <button type="submit" class="btn btn-default btn-primary btn-lg" data-dismiss="modal"  onclick="window.location.href='logout.php'"/> Logout
                                    <i class="fa fa-check"></i>
                                  </button>
                                    </form>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                    </div>

                </div>
            </div>

        </header>

<?php

require_once('__SRC__/connect.php');

if (!class_exists('DATABASE_CONNECT')) {
    error_log("Database class not found");
    die("System error. Please contact support.");
}

$obj_conn = new DATABASE_CONNECT;
$conn = $obj_conn->get_connection();

if (!$conn) {
    error_log("Database connection failed");
    die("System error. Please contact support.");
}

$email = filter_var($_SESSION['login'], FILTER_SANITIZE_EMAIL);

// Get user details with prepared statement
$stmt_user = $conn->prepare("SELECT lastname, firstname FROM customers WHERE email = ?");
if (!$stmt_user) {
    error_log("Prepare failed: " . $conn->error);
    die("System error");
}

$stmt_user->bind_param("s", $email);
if (!$stmt_user->execute()) {
    error_log("Execute failed: " . $stmt_user->error);
    die("System error");
}

$result_details_user = $stmt_user->get_result();
if (!$result_details_user) {
    error_log("Get result failed: " . $stmt_user->error);
    die("System error");
}

$row_details_user = $result_details_user->fetch_assoc();
$stmt_user->close();

if (!$row_details_user) {
    error_log("User not found: $email");
    die("User not found");
}

// SECURE: Escape output to prevent XSS
$lastname = htmlspecialchars(ucfirst($row_details_user['lastname']), ENT_QUOTES, 'UTF-8');
$firstname = htmlspecialchars(ucfirst($row_details_user['firstname']), ENT_QUOTES, 'UTF-8');

echo "<div class='breadcrumbs'>
    <div class='col-sm-4'>
        <div class='page-header float-left'>
            <div class='page-title'>
                <h1><b> $lastname $firstname </b></h1>
            </div>
        </div>
    </div>";

// Get balance with prepared statement
$stmt0 = $conn->prepare("SELECT total_balance FROM accounts WHERE email = ?");
if (!$stmt0) {
    error_log("Prepare failed: " . $conn->error);
    die("System error");
}

$stmt0->bind_param("s", $email);
if (!$stmt0->execute()) {
    error_log("Execute failed: " . $stmt0->error);
    die("System error");
}

$result0 = $stmt0->get_result();
$row0 = $result0->fetch_assoc();
$stmt0->close();

if (!$row0) {
    error_log("Account not found for: $email");
    die("Account not found");
}

// SECURE: Escape output to prevent XSS
$balance = htmlspecialchars($row0['total_balance'], ENT_QUOTES, 'UTF-8');

echo "<div class='col-sm-8'>
    <div class='page-header float-right'>
        <div class='page-title'>
            <ol class='breadcrumb text-right'>
                <li class='active'> Your balance: {$balance} <i class='fa fa-euro'></i> </li>
            </ol>
        </div>
    </div>
</div>";

// HANDLE i_code REQUEST
if (strpos($_SERVER['REQUEST_URI'], "?i_code_true") !== false) {

    // SECURE: Use cryptographically secure random generation
    $i_code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

    // SECURE: Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("UPDATE accounts SET i_code = ?, i_code_time = NOW() WHERE email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo '<script type="text/javascript">alert("Error generating code. Please try again.");</script>';
        echo "<script>location.href='transf_anyone_bank.php'</script>";
        exit;
    }

    $stmt->bind_param("ss", $i_code, $email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo '<script type="text/javascript">alert("Error generating code. Please try again.");</script>';
        echo "<script>location.href='transf_anyone_bank.php'</script>";
        exit;
    }
    $stmt->close();

    // SECURE: Sanitize email for headers
    $safe_email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Send i_code via email with proper header sanitization
    $msg = "Your i_code for transaction confirmation is: " . htmlspecialchars($i_code, ENT_QUOTES, 'UTF-8');

    $headers = "From: Easybank <noreply@ofiliyoungyz.site>\r\n";
    $headers .= "Reply-To: " . $safe_email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

    if (@mail($safe_email, "Easybank Verification Code", $msg, $headers)) {
        echo '<script type="text/javascript">alert("Check your email for i_code");</script>';
        echo "<script>location.href='transf_anyone_bank.php?i_code_one'</script>";
    } else {
        error_log("Failed to send mail to: $safe_email");
        echo '<script type="text/javascript">alert("Failed to send code. Please try again.");</script>';
        echo "<script>location.href='transf_anyone_bank.php'</script>";
    }
    exit;
}

$conn->close();

?>

  <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo">
                     <img class="align-content" style="position: relative; left: -10%;" src="images/menu/transf0.png" height="70" width="80" alt=""> 
                      <h2 style="position: relative; left: -10%;"> <font color="grey"> <b> Anyone Bank Transfer </b> </font> </h2>
                </div>

                <div class="login-form"  style="width: 550px; position: relative; left: -10%;">

                <form action="" method="post" name="MyForm">

                    <!-- SECURE: CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group form-inline">

                          <i class="fa fa-user-o" style="font-size:16px;"></i> &nbsp;
                          <label> Recipient  </label> &nbsp; &nbsp;

                        <input type="text" class="form-control col-sm-4" name="firstname" placeholder="FirstName" pattern="[A-Za-z]{1,32}" title="Only Characters (up to 32 characters)" required>
                             &nbsp; &nbsp;
                        <input type="text" class="form-control col-sm-4" name="lastname" placeholder="LastName" pattern="[A-Za-z]{1,48}" title="Only Characters (up to 48 characters)" required>

                    </div>

                    <br>

                      <div class="form-group form-inline">
                 
                          <i class="fa fa-credit-card" style="font-size:17px;"></i> &nbsp;
                          <label> IBAN (xxxx) </label> 
                            &nbsp; &nbsp; 

                     <input type="text" class="form-control col-sm-8" name="IBAN" placeholder="e.g.: EA#########################" 
                       pattern="[A-Z]{2}[0-9]{13,32}" title="The first two uppercase letters and then up to 32 digits" required>

                      </div>

                       <br>

                   <div class="form-group form-inline">

                    <i class="fa fa-money" style="font-size:18px;"></i> &nbsp;
                           <label> Amount </label> <br>
                            &nbsp; &nbsp;
                 
                          <input type="text" class="form-control col-sm-3" name="main_amount" placeholder="####" pattern="[0-9]{1,7}" title="Only Numbers (up to 7 digits)" required> &nbsp;
                          
                       <input type="text" class="form-control col-sm-3" name="secondary_amount" placeholder="##" pattern="[0-9]{0,2}" title="Only Numbers (up to 2 digits)"> &nbsp;
                         
                       <input type="text" class="form-control col-sm-2" style="text-align:center;" name="currency" value="EUR" disabled> 
                        
                    </div>

                      <br>

                   <div class="form-group fom-inline">
                  
                          <i class="fa fa-envelope-open-o" style="font-size:16px;"></i> &nbsp;
                          <label> Transfer reason </label> &nbsp; &nbsp;
              
                       <textarea class="form-control" name="reason" rows="2" id="reason" maxlength="500" style="width:95%"></textarea>

                    </div>

                     <br>

                   <div class="form-group form-inline">

                         <i class="fa fa-camera-retro" style="font-size:18px;"></i> &nbsp;
                         <label> Transfer code? </label>
                          &nbsp;
                       
                         <a href="transf_anyone_bank.php?i_code_true"> 
                               <font color="black"> Resend </font>  </a>
                              &nbsp; 

                      
                       <input type="text" class="form-control col-sm-1" name="i_code1" placeholder="#" pattern="[0-9]{1}" maxlength="1" title="Only Digit (1 digit required)" required>
                      &nbsp; - &nbsp; 

                       <input type="text" class="form-control col-sm-1" name="i_code2" placeholder="#" pattern="[0-9]{1}" maxlength="1" title="Only Digit (1 digit required)">
                      &nbsp; - &nbsp; 

                       <input type="text" class="form-control col-sm-1" name="i_code3" placeholder="#" pattern="[0-9]{1}" maxlength="1" title="Only Digit (1 digit required)" required>
                      &nbsp; - &nbsp;

                      <input type="text" class="form-control col-sm-1" name="i_code4" placeholder="#" pattern="[0-9]{1}" maxlength="1" title="Only Digit (1 digit required)" required>
                         &nbsp; &nbsp; &nbsp;
                      
                       <?php 
                            if (strpos($_SERVER['REQUEST_URI'], "?i_code_one") !== false) {
                                echo '<i class="fa fa-clock-o" style="font-size:18px;"></i> &nbsp; &nbsp';
                                echo '<span id="countdown2"></span>';
                                ?>
                                <script>
                                    countdown( "countdown2", 10, 0 );
                                </script>
                                <?php
                            }
                       ?>

                    </div>

                     <div class="wrapper">
                        <span class="group-btn">     
                          <button type="submit" name="transfer_anyone_bank" class="btn btn-primary btn-flat m-b-30 m-t-30" style="width:95%"> 
                           Transfer &nbsp; &nbsp; 
                           <i class="fa fa-send"></i>
                          </button>
                        </span>
                     </div>

                 </form>

              </div>
             </div>
            </div>
          </div>

           <?php require_once ('transf_anyone_bank_check_recipient.php'); ?>
           <?php require_once ('transf_anyone_bank_balance.php'); ?>
           <?php require_once ('transf_anyone_bank_limit.php'); ?> 
           <?php require_once ('transf_anyone_bank_i_code.php'); ?>
           <?php require_once ('transf_anyone_bank_send.php'); ?>
           <?php require_once ('transf_anyone_bank_transac.php');?>

        </div>
    </div>

    <script src="assets/js/vendor/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/main.js"></script>

    <script src="assets/js/lib/chart-js/Chart.bundle.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/widgets.js"></script>
    <script src="assets/js/lib/vector-map/jquery.vmap.js"></script>
    <script src="assets/js/lib/vector-map/jquery.vmap.min.js"></script>
    <script src="assets/js/lib/vector-map/jquery.vmap.sampledata.js"></script>
    <script src="assets/js/lib/vector-map/country/jquery.vmap.world.js"></script>
    <script>
        ( function ( $ ) {
            "use strict";

            jQuery( '#vmap' ).vectorMap( {
                map: 'world_en',
                backgroundColor: null,
                color: '#ffffff',
                hoverOpacity: 0.7,
                selectedColor: '#1de9b6',
                enableZoom: true,
                showTooltip: true,
                values: sample_data,
                scaleColors: [ '#1de9b6', '#03a9f5' ],
                normalizeFunction: 'polynomial'
            } );
        } )( jQuery );
    </script>

</body>
</html>