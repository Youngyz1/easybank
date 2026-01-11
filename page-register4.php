<?php
session_start();

if(!isset($_SESSION['step1']) || !isset($_SESSION['step2']) || !isset($_SESSION['step3'])) {
    header('Location: page-register.php');
    exit;
}

require 'vendor/autoload.php'; // AWS SDK for PHP

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

// Configure AWS SES client (region must match your SES setup)
$SesClient = new SesClient([
    'version' => 'latest',
    'region'  => 'us-east-1', // change if different
]);

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
        if(!in_array($identity_back_type, $allowed_imgs)) {
            echo '<script>alert("This file is not an image");</script>';
            echo "<script>location.href='page-register4.php'</script>";
            exit;
        }

        // Collect registration data from session
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

        // Generate PIN
        $pin = substr(str_shuffle("0123456789"), 0, 4);
        $pin_hashed = md5($pin);

        // Generate account info
        $number_bank_code = substr(str_shuffle("0123456789"), 0, 2);
        $account_number = substr(str_shuffle("0123456789"), 0, 10);
        $bank_iso = "EB";
        $bank_code = $number_bank_code;
        $bank_identity = "1411";
        $bank_acc_begin = substr($account_number, 0, -7);
        $bank_default_number = "000000";
        $bank_account_user = $account_number;
        $IBAN = $bank_iso.$bank_code.$bank_identity.$bank_acc_begin.$bank_default_number.$bank_account_user;

        // Database insertion
        require_once('__SRC__/connect.php');
        if(class_exists('DATABASE_CONNECT')) {
            $obj_conn = new DATABASE_CONNECT;
            $conn = new mysqli($obj_conn->connect[0], $obj_conn->connect[1], $obj_conn->connect[2], $obj_conn->connect[3]);
            if($conn->connect_error) die("Cannot connect ".$conn->connect_error);

            // Insert customer
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

            // Insert account
            $sql2 = "INSERT INTO accounts (
                        currency, email, lastname, firstname, account_no, IBAN,
                        limit_per_day_transfer, over_transfer, amounts_transferred, amounts_from_reserve,
                        amounts_from_you, amounts_from_others, total_balance, account_statement, i_code, i_code_time
                    ) VALUES (
                        'Euro','$email','$last_name','$first_name','$account_number','$IBAN',
                        '20000.00','0.00','0.00','0.00',
                        '0.00','0.00','0.00','on_hold','unused',''
                    )";

            // Notifications
            $sql3 = "INSERT INTO notifications (email, lastname, firstname, title, message)
                     VALUES ('$email','$last_name','$first_name','Welcome','Welcome to Easy Bank');";
            $sql3 .= "INSERT INTO notifications (email, lastname, firstname, title, message)
                      VALUES ('$email','$last_name','$first_name','Balance','Your balance is 0.00 Euro');";
            $sql3 .= "INSERT INTO notifications (email, lastname, firstname, title, message)
                      VALUES ('$email','$last_name','$first_name','Account','Your account is activated');";

            $conn->query($sql);
            $conn->query($sql2);
            $conn->multi_query($sql3);
            $conn->close();

            // Send email via SES
            try {
                $result = $SesClient->sendEmail([
                    'Destination' => [
                        'ToAddresses' => [$email],
                    ],
                    'ReplyToAddresses' => ['no-reply@yourdomain.com'], // replace with your SES-verified email
                    'Source' => 'no-reply@yourdomain.com',            // replace with your SES-verified email
                    'Message' => [
                        'Subject' => ['Data' => 'EasyBank PIN Code', 'Charset' => 'UTF-8'],
                        'Body' => [
                            'Html' => ['Data' => "<h3>Hello $first_name</h3><p>Your PIN is: <b>$pin</b></p>", 'Charset' => 'UTF-8'],
                            'Text' => ['Data' => "Hello $first_name, your PIN is: $pin", 'Charset' => 'UTF-8']
                        ],
                    ],
                ]);
                echo '<script>alert("Check your email for your PIN code.");</script>';
                echo "<script>location.href='logout.php'</script>";
            } catch (AwsException $e) {
                echo "<script>alert('PIN email failed: ".$e->getAwsErrorMessage()."');</script>";
                echo "<script>location.href='index.php'</script>";
            }
        }
    }
}
?>
