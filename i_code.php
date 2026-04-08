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

// Generate i_code
$length_code = 4;
$i_code = substr(str_shuffle("123456789"), 0, $length_code);

// ✅ Already using prepared statement — kept as is
$stmt = $conn->prepare("UPDATE accounts SET i_code = ?, i_code_time = NOW() WHERE email = ?");
$stmt->bind_param("ss", $i_code, $email);
$result = $stmt->execute();
$stmt->close();
$conn->close();

if (!$result) {
    echo '<script type="text/javascript">alert("i_code error. Please try again.");</script>';
    echo "<script>location.href='transf_easy_bank.php';</script>";
    exit;
}

// ✅ Send email
$msg = "Mr/s $email, your i_code for the confirm transaction is: $i_code";

$headers  = "From: Easybank <noreply@ofiliyoungyz.site>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

$send = mail($email, "Easybank i-code", $msg, $headers);

if (!$send) {
    echo '<script type="text/javascript">alert("i_code error. Please try again.");</script>';
    echo "<script>location.href='transf_easy_bank.php';</script>";
    exit;
} else {
    echo '<script type="text/javascript">alert("Check your mail for i_code");</script>';
    echo "<script>location.href='transf_easy_bank.php?i_code_one';</script>";
    exit;
}
?>