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

if(!isset($_SESSION['step1']) || !isset($_SESSION['step2']) || !isset($_SESSION['step3'])) {
    header('Location: page-register.php');
    exit;
}
?>

<html>
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
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet'>
    <style>
        body { background-image: url("/images/bg1.jpg"); background-repeat: no-repeat, repeat; background-size: 100%; }
        .kv-avatar .krajee-default.file-preview-frame,.kv-avatar .krajee-default.file-preview-frame:hover { margin:0; padding:0; border:none; box-shadow:none; text-align:center; }
        .kv-avatar { display:inline-block; }
        .kv-avatar .file-input { display: table-cell; width:213px; }
        .kv-reqd { color:red; font-family:monospace; font-weight:normal; }
        .btn-file { position:relative; overflow:hidden; }
        .btn-file input[type=file] { position:absolute; top:0; right:0; min-width:100%; min-height:100%; font-size:100px; text-align:right; opacity:0; cursor:inherit; display:block; }
        #img-upload { height:40%; width:100%; }
    </style>
    <script>
        $(document).ready(function(){
            $(document).on('change', '.btn-file :file', function() {
                var input = $(this), label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [label]);
            });
            $('.btn-file :file').on('fileselect', function(event, label){
                var input = $(this).parents('.input-group').find(':text');
                if(input.length) input.val(label); else if(label) alert(label);
            });
            function readURL(input){
                if(input.files && input.files[0]){
                    var reader = new FileReader();
                    reader.onload = function(e){ $('#img-upload').attr('src', e.target.result); }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $("#imgInp").change(function(){ readURL(this); });
        });
    </script>
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
            <div class="login-form" style="width:550px;">
                <form action="" method="post" enctype="multipart/form-data">
                    <h3 align="center"><b><i>&dollar; &dollar; EASYBANK ACCOUNT &euro; &euro;</i></b></h3>
                    <hr>
                    <h2 align="center"><b>Step 3: Your documents</b></h2>
                    <div class="container">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div align="center"><label>Back image of identity</label></div>
                                <div class="input-group">
                                    <span class="btn btn-default btn-file glyphicon glyphicon-open-file">
                                        Browse… <input type="file" name="identity_back" id="imgInp" required>
                                    </span>
                                    <input type="text" class="form-control" readonly>
                                </div>
                                <img id='img-upload'/>
                            </div>
                        </div>
                    </div>
                    <div class="checkbox" align="center">
                        <label><input type="checkbox" required> Agree the terms and policy</label>
                    </div>
                    <div class="wrapper" align="center">
                        <button type="submit" name="submit_end" class="btn btn-primary btn-flat m-b-30 m-t-30">
                            Register <i class="glyphicon glyphicon-ok-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
error_reporting(E_ALL | E_WARNING | E_NOTICE);
ini_set('display_errors', TRUE);

if(isset($_POST['submit_end'])) {
    require_once('__SRC__/secure_data.php');
    if(class_exists('SECURE_INPUT_DATA_AVAILABLE')) {
        $obj_secure_data = new SECURE_INPUT_DATA;

        $identity_back_name = $_FILES['identity_back']['name'];
        $identity_back_type = $_FILES['identity_back']['type'];
        $identity_back_size = $_FILES['identity_back']['size'];
        $identity_back_data = addslashes(file_get_contents($_FILES['identity_back']['tmp_name']));
        $allowed_imgs = ["image/pjpeg","image/jpeg","image/jpg","image/png","image/x-png","image/gif"];
        if(!in_array($identity_back_type,$allowed_imgs)) {
            echo '<script>alert("This file is not an image");</script>';
            echo "<script>location.href='page-register4.php'</script>";
            exit;
        }

        $first_name = $_SESSION['first_name'];
        $last_name = $_SESSION['last_name'];
        $date_of_birth = $_SESSION['day']."-".$_SESSION['month']."-".$_SESSION['year'];
        $nationality = $_SESSION['nationality'];
        $id_document_number = $_SESSION['identity_number'];
        $mobile_area_code = $_SESSION['area_code'];
        $mobile_number = $_SESSION['mobile_number'];
        $country = $_SESSION['country_code'];
        $city = $_SESSION['city'];
        $street = $_SESSION['street'];
        $street_number = $_SESSION['number'];
        $post_code = $_SESSION['post_code'];
        $tax_residence = $_SESSION['tax_residence'];
        $tax_id_number = $_SESSION['tax_id_number'];
        $identity_front_name = $_SESSION['identity_front_name'];
        $identity_front_type = $_SESSION['identity_front_type'];
        $identity_front_size = $_SESSION['identity_front_size'];
        $identity_front_data = $_SESSION['identity_front_data'];
        $email = $_SESSION['email'];
        $password = $_SESSION['password'];
        $ip_instant_register = $_SERVER['REMOTE_ADDR'];

        $pin = substr(str_shuffle("0123456789"),0,4);
        $pin_hashed = md5($pin);

        $number_bank_code = substr(str_shuffle("0123456789"),0,2);
        $account_number = substr(str_shuffle("0123456789"),0,10);
        $bank_iso = "EB";
        $bank_code = $number_bank_code;
        $bank_identity = "1411";
        $bank_acc_begin = substr($account_number,0,-7);
        $bank_default_number = "000000";
        $bank_account_user = $account_number;
        $IBAN = $bank_iso.$bank_code.$bank_identity.$bank_acc_begin.$bank_default_number.$bank_account_user;

        require_once('__SRC__/connect.php');
        if(class_exists('DATABASE_CONNECT')) {
            $obj_conn = new DATABASE_CONNECT;
            $conn = new mysqli($obj_conn->connect[0],$obj_conn->connect[1],$obj_conn->connect[2],$obj_conn->connect[3]);
            if($conn->connect_error) die("Cannot connect ".$conn->connect_error);

            $sql = "INSERT INTO customers (
                        firstname, lastname, date_of_birth, nationality, id_document_number,
                        mobile_area_code, mobile_number, country, town_city, street, street_number, post_code,
                        tax_residence, tax_id_number, identity_front_name, identity_front_type, identity_front_size, identity_front_data,
                        identity_back_name, identity_back_type, identity_back_size, identity_back_data,
                        email, password, pin, account_number, IBAN, account_type, instant_register, ip_instant_register
                    ) VALUES (
                        '$first_name','$last_name','$date_of_birth','$nationality','$id_document_number',
                        '$mobile_area_code','$mobile_number','$country','$city','$street','$street_number','$post_code',
                        '$tax_residence','$tax_id_number','$identity_front_name','$identity_front_type','$identity_front_size','$identity_front_data',
                        '$identity_back_name','$identity_back_type','$identity_back_size','$identity_back_data',
                        '$email','$password','$pin_hashed','$account_number','$IBAN','block',NOW(),'$ip_instant_register'
                    )";

            $sql2 = "INSERT INTO accounts (
                        currency, email, lastname, firstname, account_no, IBAN,
                        limit_per_day_transfer, over_transfer, amounts_transferred, amounts_from_reserve,
                        amounts_from_you, amounts_from_others, total_balance, account_statement, i_code, i_code_time
                    ) VALUES (
                        'Euro','$email','$last_name','$first_name','$account_number','$IBAN',
                        '20000.00','0.00','0.00','0.00',
                        '0.00','0.00','0.00','on_hold','unused',''
                    )";

            $sql3 = "INSERT INTO notifications (email, lastname, firstname, title, message)
                     VALUES ('$email','$last_name','$first_name','Welcome','Welcome to Easy Bank');";
            $sql3 .= "INSERT INTO notifications (email, lastname, firstname, title, message)
                      VALUES ('$email','$last_name','$first_name','Balance','Your balance is 0.00 Euro');";
            $sql3 .= "INSERT INTO notifications (email, lastname, firstname, title, message)
                      VALUES ('$email','$last_name','$first_name','Account','Your account is activated');";

            $conn->query($sql);
            $conn->query($sql2);
            $conn->multi_query($sql3);

            $msg = "Mr/s $email your PIN is: $pin";
            $headers  = "From: Easybank <easybank@easybank.no-reply> \r\n";
            $headers .= "Reply-To:".$email."\r\n"."X-Mailer: PHP/".phpversion();
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            if(mail($email,"Easybank pin code",$msg,$headers)){
                echo '<script>alert("Check your mail for your PIN code.");</script>';
                echo "<script>location.href='logout.php'</script>";
            } else {
                echo '<script>alert("PIN error. Please support us.");</script>';
                echo "<script>location.href='index.php'</script>";
            }
            $conn->close();
        }
    }
}
?>

