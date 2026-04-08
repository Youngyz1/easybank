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

$idletime = 900;
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

// âœ… Get user details
$stmt_user = $conn->prepare("SELECT lastname, firstname FROM customers WHERE email = ?");
$stmt_user->bind_param("s", $email);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$row_user = $result_user->fetch_assoc();
$stmt_user->close();

$lastname  = ucfirst($row_user['lastname'] ?? '');
$firstname = ucfirst($row_user['firstname'] ?? '');

// âœ… Get notifications
$stmt = $conn->prepare("SELECT id, created, lastname, firstname, title, message FROM notifications WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result_notifications = $stmt->get_result();
$stmt->close();

$notifications = [];
while ($row = $result_notifications->fetch_assoc()) {
    $notifications[] = $row;
}

// âœ… Get balance and account statement
$stmt2 = $conn->prepare("SELECT total_balance, account_statement FROM accounts WHERE email = ?");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$stmt2->close();

$balance           = $row2['total_balance'] ?? 0;
$account_statement = $row2['account_statement'] ?? '';

// âœ… Get account number and IBAN
$stmt3 = $conn->prepare("SELECT account_no, IBAN FROM accounts WHERE email = ?");
$stmt3->bind_param("s", $email);
$stmt3->execute();
$result3 = $stmt3->get_result();
$row3 = $result3->fetch_assoc();
$stmt3->close();

$account_no = $row3['account_no'] ?? '';
$IBAN       = $row3['IBAN'] ?? '';

// âœ… Get transfer counts
$stmt4 = $conn->prepare("SELECT COUNT(_from_customer_account_no) AS cnt FROM transactions_easy_bank WHERE _from_customer_account_no = ?");
$stmt4->bind_param("s", $account_no);
$stmt4->execute();
$row4 = $stmt4->get_result()->fetch_assoc();
$stmt4->close();

$stmt5 = $conn->prepare("SELECT COUNT(_from_customer_IBAN) AS cnt FROM transactions_anyone_bank WHERE _from_customer_IBAN = ?");
$stmt5->bind_param("s", $IBAN);
$stmt5->execute();
$row5 = $stmt5->get_result()->fetch_assoc();
$stmt5->close();

$all_transfers = ($row4['cnt'] ?? 0) + ($row5['cnt'] ?? 0);

// âœ… Get transaction counts
$stmt6 = $conn->prepare("SELECT COUNT(_from_customer_account_no) AS cnt FROM transactions_easy_bank WHERE _from_customer_account_no = ? OR _to_customer_account_no = ?");
$stmt6->bind_param("ss", $account_no, $account_no);
$stmt6->execute();
$row6 = $stmt6->get_result()->fetch_assoc();
$stmt6->close();

$stmt7 = $conn->prepare("SELECT COUNT(_from_customer_IBAN) AS cnt FROM transactions_anyone_bank WHERE _from_customer_IBAN = ? OR _to_customer_IBAN = ?");
$stmt7->bind_param("ss", $IBAN, $IBAN);
$stmt7->execute();
$row7 = $stmt7->get_result()->fetch_assoc();
$stmt7->close();

$all_transactions = ($row6['cnt'] ?? 0) + ($row7['cnt'] ?? 0);

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
    </style>
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
                        <h1>Welcome <b><?= htmlspecialchars($lastname) ?> <?= htmlspecialchars($firstname) ?></b></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Table -->
        <div class="container-fluid">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-3">
                            <h2 class="text-center pull-left" style="padding-left:30px;">
                                <i class="menu-icon fa fa-bell"></i> Notifications
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="panel-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Date</th>
                                <th class="text-center">Lastname</th>
                                <th class="text-center">Firstname</th>
                                <th class="text-center">Title</th>
                                <th class="text-center">Notice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $row): ?>
                            <tr class="edit" id="detail">
                                <td class="text-center"><?= htmlspecialchars($row['created']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['lastname']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['firstname']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['title']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['message']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <br><br>

        <!-- Stats Cards -->
        <div align="center">
            <div class="col-xl-3 col-lg-6">
                <div class="card"><div class="card-body"><div class="stat-widget-one">
                    <div class="stat-icon dib"><i class="fa fa-euro text-success border-success"></i></div>
                    <div class="stat-content dib">
                        <div class="stat-text">Your balance</div>
                        <div class="stat-digit"><?= htmlspecialchars($balance) ?></div>
                    </div>
                </div></div></div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card"><div class="card-body"><div class="stat-widget-one">
                    <div class="stat-icon dib"><i class="ti-user text-primary border-primary"></i></div>
                    <div class="stat-content dib">
                        <div class="stat-text">Your account</div>
                        <div class="stat-digit"><?= htmlspecialchars($account_statement) ?></div>
                    </div>
                </div></div></div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card"><div class="card-body"><div class="stat-widget-one">
                    <div class="stat-icon dib"><i class="fa fa-handshake-o text-warning border-warning"></i></div>
                    <div class="stat-content dib">
                        <div class="stat-text">Transactions</div>
                        <div class="stat-digit"><?= htmlspecialchars($all_transactions) ?></div>
                    </div>
                </div></div></div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card"><div class="card-body"><div class="stat-widget-one">
                    <div class="stat-icon dib"><i class="fa fa-credit-card-alt text-warning border-warning"></i></div>
                    <div class="stat-content dib">
                        <div class="stat-text">Transfers</div>
                        <div class="stat-digit"><?= htmlspecialchars($all_transfers) ?></div>
                    </div>
                </div></div></div>
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
