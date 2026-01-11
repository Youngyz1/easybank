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


<?php
session_start();

require_once('__SRC__/secure_data.php'); // your existing input sanitization
require 'vendor/autoload.php'; // AWS SDK for PHP

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

// SES Client
$ses = new SesClient([
    'version' => '2010-12-01',
    'region'  => 'us-east-1', // change if needed
]);

$error_msg = '';
$step = 1;

// Handle Step 1: Capture user info and send PIN
if (isset($_POST['submit_step1'])) {

    if (class_exists('SECURE_INPUT_DATA_AVAILABLE')) {
        $obj_secure_data = new SECURE_INPUT_DATA;

        $email         = $obj_secure_data->SECURE_DATA_ENTER($_POST['email']);
        $password      = $obj_secure_data->SECURE_DATA_ENTER($_POST['password']);
        $area_code     = $obj_secure_data->SECURE_DATA_ENTER($_POST['area_code']);
        $mobile_number = $obj_secure_data->SECURE_DATA_ENTER($_POST['mobile']);

        $_SESSION['email']         = $email;
        $_SESSION['password']      = md5($password);
        $_SESSION['symbol_area_code'] = '+';
        $_SESSION['area_code']     = "+" . $area_code;
        $_SESSION['mobile_number'] = $mobile_number;

        // Generate a 6-digit PIN
        $pin = rand(100000, 999999);
        $_SESSION['pin'] = $pin;

        // Send PIN via SES
        try {
            $result = $ses->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$email],
                ],
                'Message' => [
                    'Body' => [
                        'Text' => [
                            'Data' => "Your EasyBank verification PIN is: $pin",
                            'Charset' => 'UTF-8',
                        ],
                    ],
                    'Subject' => [
                        'Data' => 'EasyBank Account Verification PIN',
                        'Charset' => 'UTF-8',
                    ],
                ],
                'Source' => 'you@yourdomain.com', // verified SES sender
            ]);
        } catch (AwsException $e) {
            $error_msg = "Failed to send verification email: " . $e->getAwsErrorMessage();
        }

        if (!$error_msg) {
            $step = 2; // Move to PIN verification
        }
    }
}

// Handle Step 2: Verify PIN
if (isset($_POST['submit_pin'])) {
    $entered_pin = $_POST['verification_pin'] ?? '';
    if (isset($_SESSION['pin']) && $entered_pin == $_SESSION['pin']) {
        $_SESSION['step2'] = true;
        unset($_SESSION['pin']); // remove PIN after successful verification
        // Redirect to next registration step or dashboard
        echo "<script>location.href='page-register3.php';</script>";
        exit;
    } else {
        $error_msg = "Invalid PIN. Please try again.";
        $step = 2;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EasyBank Registration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="margin-top:50px; max-width:600px;">
    <h2 align="center">EasyBank Registration</h2>
    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if ($step == 1): ?>
        <form method="post">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="form-group">
                <label>Area Code</label>
                <input type="text" class="form-control" name="area_code" required pattern="[0-9]{2,3}">
            </div>
            <div class="form-group">
                <label>Mobile Number</label>
                <input type="text" class="form-control" name="mobile" required pattern="[0-9]{6,15}">
            </div>
            <button type="submit" name="submit_step1" class="btn btn-primary btn-block">Next Step</button>
        </form>

    <?php elseif ($step == 2): ?>
        <form method="post">
            <div class="form-group">
                <label>Enter the PIN sent to <?= htmlspecialchars($_SESSION['email']) ?></label>
                <input type="text" class="form-control" name="verification_pin" required pattern="[0-9]{6}">
            </div>
            <button type="submit" name="submit_pin" class="btn btn-success btn-block">Verify PIN</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
