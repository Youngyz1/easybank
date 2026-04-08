<?php
session_start();

require_once('__SRC__/csrf.php');

/* ===============================
   SHOW ERRORS (VERY IMPORTANT)
================================= */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ===============================
   SESSION VALIDATION
================================= */
if(!isset($_SESSION['step1']) || !isset($_SESSION['step2']) || !isset($_SESSION['step3'])) {
    header('Location: page-register.php');
    exit;
}

/* ===============================
   LOAD AWS SDK
================================= */
require_once __DIR__ . '/vendor/autoload.php';

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

/* ===============================
   CREATE SES CLIENT
================================= */
$SesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'us-east-1'
]);

/* ===============================
   FORM SUBMIT
================================= */
if(isset($_POST['submit_end'])) {
    verify_csrf_token();

    if(!file_exists('__SRC__/secure_data.php')){
        die("secure_data.php missing");
    }

    require_once('__SRC__/secure_data.php');

    /* ===============================
       FILE VALIDATION
    ================================= */
    if(empty($_FILES['identity_back']['tmp_name'])) {
        die("Identity back image is required.");
    }

    $allowed_imgs = ["image/jpeg","image/jpg","image/png","image/gif"];

    if(!in_array($_FILES['identity_back']['type'], $allowed_imgs)) {
        die("Only JPG, PNG, GIF allowed.");
    }

    $identity_back_name = $_FILES['identity_back']['name'];
    $identity_back_type = $_FILES['identity_back']['type'];
    $identity_back_size = $_FILES['identity_back']['size'];
    $identity_back_data = addslashes(file_get_contents($_FILES['identity_back']['tmp_name']));

    /* ===============================
       SESSION DATA
    ================================= */
    $first_name = $_SESSION['first_name'];
    $last_name  = $_SESSION['last_name'];
    $email      = $_SESSION['email'];

    $password_raw = $_SESSION['password'];
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    $account_number = rand(1000000000,9999999999);
    $IBAN = "EB14".$account_number;

    $pin = rand(1000,9999);
    $pin_hashed = password_hash($pin, PASSWORD_DEFAULT);

    $ip_instant_register = $_SERVER['REMOTE_ADDR'];

    /* ===============================
       DATABASE
    ================================= */
    if(!file_exists('__SRC__/connect.php')){
        die("connect.php missing");
    }

    require_once('__SRC__/connect.php');

    $obj_conn = new DATABASE_CONNECT;
    $conn = $obj_conn->get_connection();

    if(!$conn){
        die("Database connection failed.");
    }

    $stmt = $conn->prepare("INSERT INTO customers 
            (firstname, lastname, email, password, pin, account_number, IBAN, identity_back_name, identity_back_type, identity_back_size, identity_back_data, instant_register, ip_instant_register, is_active)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0)");

    $stmt->bind_param("ssssisssiss",
        $first_name, $last_name, $email, $password, $pin_hashed, $account_number, $IBAN,
        $identity_back_name, $identity_back_type, $identity_back_size, $identity_back_data,
        $ip_instant_register);

    if(!$stmt->execute()){
        die("Database insert failed: " . $stmt->error);
    }
    $stmt->close();
    $conn->close();

    /* ===============================
       SEND EMAIL
    ================================= */
    try {
        $SesClient->sendEmail([
            'Destination' => ['ToAddresses' => [$email]],
            'Source' => 'ofiliyoungyz@gmail.com',
            'Message' => [
                'Subject' => [
                    'Data' => 'EasyBank PIN Code',
                    'Charset' => 'UTF-8'
                ],
                'Body' => [
                    'Html' => [
                        'Data' => "<h3>Hello $first_name</h3><p>Your PIN is: <b>$pin</b></p><p>Your account will be activated by our admin team shortly.</p>",
                        'Charset' => 'UTF-8'
                    ],
                    'Text' => [
                        'Data' => "Hello $first_name, your PIN is: $pin\nYour account will be activated by our admin team shortly.",
                        'Charset' => 'UTF-8'
                    ]
                ],
            ],
        ]);

        // Clear session data
        unset($_SESSION['step1']);
        unset($_SESSION['step2']);
        unset($_SESSION['step3']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['email']);
        unset($_SESSION['password']);

        header('Location: page-login-pin.php');
        exit;

    } catch (AwsException $e) {
        $_SESSION['error'] = "Email failed: " . $e->getMessage();
        header('Location: page-register.php');
        exit;
    }
}
?>

<!DOCTYPE html>
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
    <link rel="stylesheet" href="assets/scss/style.css">
    <style>
    body {
        background-image: url("/images/bg1.jpg");
        background-repeat: no-repeat;
        background-size: 100% 100%;
    }
    .btn-file {
        position: relative;
        overflow: hidden;
    }
    .btn-file input[type=file] {
        position: absolute;
        top: 0; right: 0;
        min-width: 100%; min-height: 100%;
        font-size: 100px;
        opacity: 0;
        cursor: inherit;
        display: block;
    }
    #img-upload { height: 40%; width: 100%; }
    </style>
    <script>
    $(document).ready(function() {
        $(document).on('change', '.btn-file :file', function() {
            var input = $(this), label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [label]);
        });
        $('.btn-file :file').on('fileselect', function(event, label) {
            var input = $(this).parents('.input-group').find(':text');
            if(input.length) input.val(label);
        });
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { $('#img-upload').attr('src', e.target.result); }
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
                <div class="login-form" style="width: 550px; position: relative; left: 0%;">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <h3 align="center">
                            <font color="black"><b><i>&dollar;&dollar; EASYBANK ACCOUNT &euro;&euro;</i></b></font>
                        </h3><hr>
                        <h3 align="center"><font color="black"><b>Step 4: Back of your ID</b></font></h3>

                        <div class="container">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div align="center"><label for="imgInp">Back image of identity</label></div>
                                    <div class="input-group">
                                        <span class="btn btn-default btn-file glyphicon glyphicon-open-file">
                                            Browse… <input type="file" name="identity_back" id="imgInp" required autocomplete="off">
                                        </span>
                                        <input type="text" class="form-control" readonly>
                                    </div>
                                    <img id='img-upload'/>
                                </div>
                            </div>
                        </div>

                        <div class="wrapper">
                            <span class="group-btn">
                                <button type="submit" name="submit_end" class="btn btn-success btn-flat m-b-30 m-t-30">
                                    Complete Registration <i class="glyphicon glyphicon-ok"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>