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

// ✅ Fixed SQL injection — prepared statement for personal details
$stmt1 = $conn->prepare("SELECT firstname, lastname, date_of_birth, id_document_number, 
                         mobile_area_code, mobile_number, nationality, country, town_city, 
                         street, street_number, tax_id_number 
                         FROM customers WHERE email = ?");
$stmt1->bind_param("s", $email);
$stmt1->execute();
$result1 = $stmt1->get_result();
$stmt1->close();

// ✅ Fixed SQL injection — prepared statement for documents
$stmt3 = $conn->prepare("SELECT identity_front_data, identity_back_data FROM customers WHERE email = ?");
$stmt3->bind_param("s", $email);
$stmt3->execute();
$result3 = $stmt3->get_result();
$stmt3->close();

$conn->close();

// Fetch rows
$personal_details = [];
while ($row1 = $result1->fetch_assoc()) {
    $personal_details[] = $row1;
}

$documents = [];
while ($row3 = $result3->fetch_assoc()) {
    $documents[] = $row3;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Easybank</title>
    <link rel="shortcut icon" href="favicon.png" type="image/png">
    <meta HTTP-EQUIV="REFRESH" content="900; url=/logout.php">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Slabo+27px|Yesteryear'>
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style>
    * { -webkit-box-sizing: border-box; box-sizing: border-box; }
    body { background: #eceef1; font-family: 'Slabo 27px', serif; color: #333a45; }
    .wrapper { margin: 5em auto; max-width: 1000px; background-color: #fff; box-shadow: 0 0 5px 0 rgba(0,0,0,0.06); }
    .header { padding: 30px 30px 0; text-align: center; }
    .header__title { margin: 0; text-transform: uppercase; font-size: 2.5em; font-weight: 500; line-height: 1.1; }
    .header__subtitle { margin: 0; font-size: 1.5em; color: #949fb0; font-weight: 500; line-height: 1.1; }
    .cards { padding: 15px; display: flex; flex-flow: row wrap; }
    .card { margin: 15px; width: calc((100% / 3) - 30px); transition: all 0.2s ease-in-out; }
    @media screen and (max-width: 991px) { .card { width: calc((100% / 2) - 30px); } }
    @media screen and (max-width: 767px) { .card { width: 100%; } }
    .card:hover .card__inner { background-color: #1abc9c; transform: scale(1.05); }
    .card__inner { width: 100%; padding: 30px; position: relative; cursor: pointer; background-color: #949fb0; color: #eceef1; font-size: 1.5em; text-transform: uppercase; text-align: center; transition: all 0.2s ease-in-out; }
    .card__inner:after { transition: all 0.3s ease-in-out; }
    .card__inner .fa { width: 100%; margin-top: .25em; }
    .card__expander { transition: all 0.2s ease-in-out; background-color: transparent; width: 100%; position: relative; display: flex; justify-content: center; align-items: center; text-transform: uppercase; color: #eceef1; font-size: 1.5em; }
    .card__expander .fa { font-size: 0.75em; position: absolute; top: 10px; right: 10px; cursor: pointer; color: black; }
    .card__expander .fa:hover { opacity: 0.9; }
    .card.is-collapsed .card__inner:after { content: ""; opacity: 0; }
    .card.is-collapsed .card__expander { max-height: 0; min-height: 0; overflow: hidden; margin-top: 0; opacity: 0; }
    .card.is-expanded .card__inner { background-color: #1abc9c; }
    .card.is-expanded .card__inner:after { content: ""; opacity: 1; display: block; height: 0; width: 0; position: absolute; bottom: -30px; left: calc(50% - 15px); border-left: 15px solid transparent; border-right: 15px solid transparent; border-bottom: 15px solid #333a45; }
    .card.is-expanded .card__inner .fa:before { content: "\f115"; }
    .card.is-expanded .card__expander { max-height: 1000px; min-height: 200px; overflow: visible; margin-top: 30px; opacity: 1; }
    .card.is-expanded:hover .card__inner { transform: scale(1); }
    .card.is-inactive .card__inner { pointer-events: none; opacity: 0.5; }
    .card.is-inactive:hover .card__inner { background-color: #949fb0; transform: scale(1); }
    @media screen and (min-width: 992px) {
        .card:nth-of-type(3n+2) .card__expander { margin-left: calc(-100% - 30px); }
        .card:nth-of-type(3n+3) .card__expander { margin-left: calc(-200% - 60px); }
        .card:nth-of-type(3n+4) { clear: left; }
        .card__expander { width: calc(300% + 60px); }
    }
    @media screen and (min-width: 768px) and (max-width: 991px) {
        .card:nth-of-type(2n+2) .card__expander { margin-left: calc(-100% - 30px); }
        .card:nth-of-type(2n+3) { clear: left; }
        .card__expander { width: calc(200% + 30px); }
    }
    .carousel { width: 800px; }
    .article-slide .carousel-indicators { bottom: -30%; left: 20%; margin-left: 100px; width: 100%; }
    .article-slide .carousel-indicators li { border: medium none; border-radius: 0; float: left; height: 80px; margin-bottom: 5px; margin-left: 0; margin-right: 5px !important; margin-top: 0; width: 130px; }
    .article-slide .carousel-indicators img { border: 2px solid #fff; float: left; height: 80px; left: 0; width: 130px; }
    .article-slide .carousel-indicators .active img { border: 2px solid #428BCA; opacity: 0.7; }
    </style>
</head>

<body>

<div class="wrapper">
    <div class="header">
        <h1 class="header__title">Account Settings</h1>
        <h2 class="header__subtitle">Easy Bank</h2>
    </div>

    <div class="cards">

        <!-- Personal Details Card -->
        <?php foreach ($personal_details as $row1): ?>
        <div class="card [ is-collapsed ]">
            <div class="card__inner [ js-expander ]">
                <span>Personal Details</span>
                <i class="fa fa-folder-o"></i>
            </div>
            <div class="card__expander">
                <i class="fa fa-close [ js-collapser ]"></i>
                <div class="container">
                    <div class="row">
                        <div class="panel panel-default" style="min-width:900px; max-width:900px; color:black;">
                            <div class="panel-heading">
                                <h4>Customer Details</h4>
                            </div>
                            <div class="panel-body">
                                <div class="col-md-4 col-xs-12 col-sm-6 col-lg-4">
                                    <img alt="User Pic" src="images/logo_pdf.jpg" class="img-circle img-responsive">
                                </div>
                                <div class="col-md-8 col-xs-12 col-sm-6 col-lg-8">
                                    <div class="container">
                                        <h2><?= htmlspecialchars($row1['lastname']) ?> <?= htmlspecialchars($row1['firstname']) ?></h2>
                                        <p>a <b>Customer</b></p>
                                    </div>
                                    <hr>
                                    <ul class="container details">
                                        <li><p><span class="glyphicon glyphicon-picture" style="width:50px;"></span> ID Number: &nbsp; <?= htmlspecialchars($row1['id_document_number']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-info-sign" style="width:50px;"></span> Tax ID: &nbsp; <?= htmlspecialchars($row1['tax_id_number']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-calendar" style="width:50px;"></span> Date of Birth: &nbsp; <?= htmlspecialchars($row1['date_of_birth']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-phone" style="width:50px;"></span> Mobile: &nbsp; <?= htmlspecialchars($row1['mobile_area_code']) ?> <?= htmlspecialchars($row1['mobile_number']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-globe" style="width:50px;"></span> Nationality: &nbsp; <?= htmlspecialchars($row1['nationality']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-map-marker" style="width:50px;"></span> Country: &nbsp; <?= htmlspecialchars($row1['country']) ?> &nbsp; City: &nbsp; <?= htmlspecialchars($row1['town_city']) ?></p></li>
                                        <li><p><span class="glyphicon glyphicon-road" style="width:50px;"></span> Street: &nbsp; <?= htmlspecialchars($row1['street']) ?> &nbsp; Number: &nbsp; <?= htmlspecialchars($row1['street_number']) ?></p></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Login Details Card -->
        <div class="card [ is-collapsed ]">
            <div class="card__inner [ js-expander ]">
                <span>Login Details</span>
                <i class="fa fa-folder-o"></i>
            </div>
            <div class="card__expander">
                <i class="fa fa-close [ js-collapser ]"></i>
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6 col-sm-offset-3">
                            <form action="account_change_pass.php" method="post" id="passwordForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="password" class="input-lg form-control" name="password" id="password1" placeholder="New Password" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <span id="8char" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span>
                                        <font color="black"> 8 Characters Long</font><br>
                                        <span id="ucase" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span>
                                        <font color="black"> One Uppercase Letter</font>
                                    </div>
                                    <div class="col-sm-6">
                                        <span id="lcase" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span>
                                        <font color="black"> One Lowercase Letter</font><br>
                                        <span id="num" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span>
                                        <font color="black"> One Number</font>
                                    </div>
                                </div>

                                <input type="password" class="input-lg form-control" name="password_retype" id="password2" placeholder="Repeat Password" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <span id="pwmatch" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Passwords Match
                                    </div>
                                </div>

                                <input type="submit" name="change_password" class="col-xs-12 btn btn-primary btn-load btn-lg" value="Change Password">
                            </form>
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <?php foreach ($documents as $row3): ?>
        <div class="card [ is-collapsed ]">
            <div class="card__inner [ js-expander ]">
                <span>Your Documents</span>
                <i class="fa fa-folder-o"></i>
            </div>
            <div class="card__expander">
                <i class="fa fa-close [ js-collapser ]"></i>
                <div align="center">
                    <div class="carousel slide article-slide" id="article-photo-carousel">
                        <div class="carousel-inner cont-slider">
                            <div class="item active">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row3['identity_front_data']) ?>" height="350" width="450" alt="ID Front">
                            </div>
                            <div class="item">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row3['identity_back_data']) ?>" height="350" width="450" alt="ID Back">
                            </div>
                        </div>
                        <ol class="carousel-indicators">
                            <li class="active" data-slide-to="0" data-target="#article-photo-carousel">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row3['identity_front_data']) ?>"/>
                            </li>
                            <li data-slide-to="1" data-target="#article-photo-carousel">
                                <img src="data:image/jpeg;base64,<?= base64_encode($row3['identity_back_data']) ?>"/>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

<script>
$("input[type=password]").keyup(function() {
    var ucase = new RegExp("[A-Z]+");
    var lcase = new RegExp("[a-z]+");
    var num   = new RegExp("[0-9]+");

    if ($("#password1").val().length >= 8) {
        $("#8char").removeClass("glyphicon-remove").addClass("glyphicon-ok").css("color","#00A41E");
    } else {
        $("#8char").removeClass("glyphicon-ok").addClass("glyphicon-remove").css("color","#FF0004");
    }
    if (ucase.test($("#password1").val())) {
        $("#ucase").removeClass("glyphicon-remove").addClass("glyphicon-ok").css("color","#00A41E");
    } else {
        $("#ucase").removeClass("glyphicon-ok").addClass("glyphicon-remove").css("color","#FF0004");
    }
    if (lcase.test($("#password1").val())) {
        $("#lcase").removeClass("glyphicon-remove").addClass("glyphicon-ok").css("color","#00A41E");
    } else {
        $("#lcase").removeClass("glyphicon-ok").addClass("glyphicon-remove").css("color","#FF0004");
    }
    if (num.test($("#password1").val())) {
        $("#num").removeClass("glyphicon-remove").addClass("glyphicon-ok").css("color","#00A41E");
    } else {
        $("#num").removeClass("glyphicon-ok").addClass("glyphicon-remove").css("color","#FF0004");
    }
    if ($("#password1").val() == $("#password2").val()) {
        $("#pwmatch").removeClass("glyphicon-remove").addClass("glyphicon-ok").css("color","#00A41E");
    } else {
        $("#pwmatch").removeClass("glyphicon-ok").addClass("glyphicon-remove").css("color","#FF0004");
    }
});

var $cell = $('.card');

$cell.find('.js-expander').click(function() {
    var $thisCell = $(this).closest('.card');
    if ($thisCell.hasClass('is-collapsed')) {
        $cell.not($thisCell).removeClass('is-expanded').addClass('is-collapsed').addClass('is-inactive');
        $thisCell.removeClass('is-collapsed').addClass('is-expanded');
        if (!$cell.not($thisCell).hasClass('is-inactive')) {
            $cell.not($thisCell).addClass('is-inactive');
        }
    } else {
        $thisCell.removeClass('is-expanded').addClass('is-collapsed');
        $cell.not($thisCell).removeClass('is-inactive');
    }
});

$cell.find('.js-collapser').click(function() {
    var $thisCell = $(this).closest('.card');
    $thisCell.removeClass('is-expanded').addClass('is-collapsed');
    $cell.not($thisCell).removeClass('is-inactive');
});
</script>

</body>
</html>