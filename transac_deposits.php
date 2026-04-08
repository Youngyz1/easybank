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

if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$idletime = 900; // 15 minutes
if (time() - $_SESSION['timestamp'] > $idletime) {
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
} else {
    $_SESSION['timestamp'] = time();
}

require_once('__SRC__/connect.php');

if (!class_exists('DATABASE_CONNECT')) {
    die("Database class not found.");
}

$obj_conn = new DATABASE_CONNECT;
$conn = $obj_conn->get_connection();

$email = $_SESSION['login'];

// ✅ Fixed SQL injection — get user details
$stmt = $conn->prepare("SELECT lastname, firstname FROM customers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result_user = $stmt->get_result();
$row_user = $result_user->fetch_assoc();
$stmt->close();

$lastname  = ucfirst($row_user['lastname'] ?? '');
$firstname = ucfirst($row_user['firstname'] ?? '');

// ✅ Fixed SQL injection — get balance
$stmt2 = $conn->prepare("SELECT total_balance FROM accounts WHERE email = ?");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result_balance = $stmt2->get_result();
$row_balance = $result_balance->fetch_assoc();
$stmt2->close();

$balance = $row_balance['total_balance'] ?? 0;

// ✅ Fixed SQL injection — get account details
$stmt3 = $conn->prepare("SELECT account_no, IBAN FROM accounts WHERE email = ?");
$stmt3->bind_param("s", $email);
$stmt3->execute();
$result_account = $stmt3->get_result();
$row_account = $result_account->fetch_assoc();
$stmt3->close();

$account_no = $row_account['account_no'] ?? '';
$IBAN       = $row_account['IBAN'] ?? '';

$conn->close();
?>

<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta HTTP-EQUIV="REFRESH" content="900; url=/logout.php">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Easybank</title>
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
    function bigImg(x) { x.style.height = "36px"; x.style.width = "36px"; }
    function normalImg(x) { x.style.height = "32px"; x.style.width = "32px"; }
    </script>

    <style>
    .modal { text-align: center; padding: 0!important; }
    .modal:before { content: ''; display: inline-block; height: 80%; vertical-align: middle; margin-right: -4px; }
    .modal-dialog { display: inline-block; text-align: left; vertical-align: middle; }
    .modal-content { background-color: silver; }
    .modal-body { background-color: white; }
    .modal-backdrop { background-color: grey !important; opacity: 1; }
    .panel { margin: 5%; background: transparent; }
    tr { transition: all 0.5s; }
    tr:hover { background-color: #f0ad4e; transition: 0.5s; }
    .btn-warning { transition: all 0.8s; }
    .btn-warning:hover, .btn-warning:focus { transition: 0.8s; background-color: #428bca; border-color: #428bca; }
    .panel-footer { background-color: #5bc0de; color: white; }
    .list { height: 40px; width: 98%; background-color: #D4EDDA; text-align-last: center; }
    select { width: 400px; text-align-last: center; }
    option { width: 400px; text-align-last: center; }
    </style>

    <script>
    function showTransaction(str) {
        if (str == "") {
            document.getElementById("txtHint").innerHTML = "";
            return;
        }
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        }
        xmlhttp.open("GET", "transac_deposits_all.php?q=" + encodeURIComponent(str), true);
        xmlhttp.send();
    }
    </script>
</head>

<body>

    <!-- Left Panel -->
    <aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">
            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="home.php">EasyBank</a>
                <a class="navbar-brand active" href="home.php"><img src="images/logo5.png" alt="Logo"></a>
            </div>

            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <h3 class="menu-title">statements</h3>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="menu-icon fa fa-handshake-o"></i> Transactions
                        </a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-money"></i><a href="transac_deposits.php"> Deposits</a></li>
                            <li><i class="fa fa-credit-card"></i><a href="transac_withdrawals.php"> Withdrawals</a></li>
                        </ul>
                    </li>

                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="menu-icon fa fa-credit-card-alt"></i> Transfers
                        </a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="fa fa-credit-card-alt"></i><a href="transf_easy_bank.php?i_code_true"> Easy Bank</a></li>
                            <li><i class="fa fa-credit-card"></i><a href="transf_anyone_bank.php?i_code_true"> Anyone Bank</a></li>
                        </ul>
                    </li>

                    <h3 class="menu-title">Settings</h3>
                    <li><a href="account.php"><i class="menu-icon fa fa-user"></i> Account</a></li>
                    <li><a href="notifications.php"><i class="menu-icon ti-bell"></i> Notifications</a></li>
                    <li><a href="statics.php"><i class="menu-icon fa fa-bar-chart"></i> Statics</a></li>

                    <h3 class="menu-title">Extras</h3>
                    <li class="menu-item-has-children dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="menu-icon fa fa-cogs"></i> Services
                        </a>
                        <ul class="sub-menu children dropdown-menu">
                            <li><i class="menu-icon fa fa-info"></i><a href="informations.php"> Informations</a></li>
                            <li><i class="menu-icon fa fa-comments"></i><a href="support.php"> Support</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>

    <!-- Right Panel -->
    <div id="right-panel" class="right-panel">

        <header id="header" class="header">
            <div class="header-menu">
                <div class="col-sm-7">
                    <a id="menuToggle" class="menutoggle pull-left"><i class="fa fa-tasks"></i></a>
                    <div class="header-left">
                        <?php echo date("d.m.Y"); ?>
                        <div class="form-inline">
                            <form class="search-form">
                                <input class="form-control mr-sm-2" type="text" placeholder="Search ..." aria-label="Search">
                                <button class="search-close" type="submit"><i class="fa fa-close"></i></button>
                            </form>
                        </div>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo "Auto logout in "; ?> <span id="countdown"></span>

                        <script>
                        function countdown(elementName, minutes, seconds) {
                            var element, endTime, hours, mins, msLeft, time;
                            function twoDigits(n) { return (n <= 9 ? "0" + n : n); }
                            function updateTimer() {
                                msLeft = endTime - (+new Date);
                                if (msLeft < 1000) {
                                    element.innerHTML = "countdown's over!";
                                } else {
                                    time = new Date(msLeft);
                                    hours = time.getUTCHours();
                                    mins = time.getUTCMinutes();
                                    element.innerHTML = (hours ? hours + ':' + twoDigits(mins) : mins) + ':' + twoDigits(time.getUTCSeconds());
                                    setTimeout(updateTimer, time.getUTCMilliseconds() + 500);
                                }
                            }
                            element = document.getElementById(elementName);
                            endTime = (+new Date) + 1000 * (60 * minutes + seconds) + 500;
                            updateTimer();
                        }
                        countdown("countdown", 15, 0);
                        </script>
                    </div>
                </div>

                <div class="col-sm-5">
                    <div class="user-area dropdown float-right">
                        <a href="#" data-toggle="modal" data-target="#logoutModal">
                            <img onmouseover="bigImg(this)" onmouseout="normalImg(this)" class="user-avatar rounded-circle" src="images/menu/logout.png" title="Logout">
                        </a>

                        <div class="modal" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-md">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4>Log Out <i class="fa fa-lock"></i></h4>
                                    </div>
                                    <div class="modal-body">
                                        <p><i class="fa fa-question-circle"></i> Are you sure you want to Logout?</p>
                                        <div class="actionsBtns">
                                            <form>
                                                <button class="btn btn-default btn-lg" data-dismiss="modal">Cancel <i class="fa fa-close"></i></button>
                                                &nbsp;
                                                <button type="submit" class="btn btn-primary btn-lg" data-dismiss="modal" onclick="window.location.href='logout.php'">Logout <i class="fa fa-check"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                        function logout() {
                            if (confirm("Do you want logout?")) { location.href = 'logout.php'; }
                        }
                        </script>
                    </div>
                </div>
            </div>
        </header>

        <!-- Breadcrumbs -->
        <div class="breadcrumbs">
            <div class="col-sm-4">
                <div class="page-header float-left">
                    <div class="page-title">
                        <h1>Welcome to Easy Bank <b><?= htmlspecialchars($lastname) ?> <?= htmlspecialchars($firstname) ?></b></h1>
                    </div>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="page-header float-right">
                    <div class="page-title">
                        <ol class="breadcrumb text-right">
                            <li class="active">Your balance: <?= htmlspecialchars($balance) ?> <i class="fa fa-euro"></i></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content mt-3">
            <div class="col-sm-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h3 align="center">Your transactions <i class="menu-icon fa fa-handshake-o"></i></h3>
                </div>
            </div>

            <div align="center">
                <form>
                    <select name="transactions" class="list" onchange="showTransaction(this.value)">
                        <option selected disabled>Select Transactions</option>
                        <option value="All_banks">All Banks</option>
                        <option value="<?= htmlspecialchars($account_no) ?>">Easy Bank</option>
                        <option value="<?= htmlspecialchars($IBAN) ?>">Anyone Bank</option>
                    </select>
                </form>
                <div id="txtHint"></div>
            </div>
        </div>

        </div><!-- /#right-panel -->

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
    (function($) {
        "use strict";
        jQuery('#vmap').vectorMap({
            map: 'world_en',
            backgroundColor: null,
            color: '#ffffff',
            hoverOpacity: 0.7,
            selectedColor: '#1de9b6',
            enableZoom: true,
            showTooltip: true,
            values: sample_data,
            scaleColors: ['#1de9b6', '#03a9f5'],
            normalizeFunction: 'polynomial'
        });
    })(jQuery);
    </script>

</body>
</html>