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

$idletime = 898;

if (time() - $_SESSION['timestamp'] > $idletime) {
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
} else {
    $_SESSION['timestamp'] = time();
}

if (isset($_POST['transfer_anyone_bank'])) {

    verify_csrf_token();

    error_reporting(0);
    ini_set('display_errors', FALSE);

    require_once('__SRC__/connect.php');
    require_once('__SRC__/secure_data.php');

    if (class_exists('DATABASE_CONNECT') && class_exists('SECURE_INPUT_DATA_AVAILABLE')) {

        $obj_conn = new DATABASE_CONNECT;
        $conn = $obj_conn->get_connection();

        $obj_secure_data = new SECURE_INPUT_DATA;

        // Get and sanitize inputs
        $firstname        = $obj_secure_data->SECURE_DATA_ENTER($_POST['firstname']);
        $lastname         = $obj_secure_data->SECURE_DATA_ENTER($_POST['lastname']);
        $IBAN             = $obj_secure_data->SECURE_DATA_ENTER($_POST['IBAN']);
        $main_amount      = $obj_secure_data->SECURE_DATA_ENTER($_POST['main_amount']);
        $secondary_amount = $obj_secure_data->SECURE_DATA_ENTER($_POST['secondary_amount']);
        $total_amount     = floatval($main_amount . "." . $secondary_amount);

        $amount_reserve = 3;
        $total_amount_with_reserve = $total_amount + $amount_reserve;

        // ✅ Get sender balance
        $stmt = $conn->prepare("SELECT total_balance FROM accounts WHERE email = ?");
        $stmt->bind_param("s", $_SESSION['login']);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = $result->fetch_assoc();
        $total_balance = floatval($row['total_balance']);

        if ($total_amount_with_reserve > $total_balance) {
            echo '<script type="text/javascript">alert("You do not have enough balance to do this transfer.");</script>';
            exit;
        }

        // ✅ Deduct from sender
        $stmt2 = $conn->prepare("UPDATE accounts SET 
            amounts_transferred = amounts_transferred + ?, 
            amounts_from_reserve = amounts_from_reserve + ?, 
            total_balance = total_balance - ? 
            WHERE email = ?");
        $stmt2->bind_param("ddds", $total_amount, $amount_reserve, $total_amount_with_reserve, $_SESSION['login']);
        $result2 = $stmt2->execute();
        $stmt2->close();

        // ✅ Add to recipient
        $stmt3 = $conn->prepare("UPDATE accounts SET 
            amounts_from_others = amounts_from_others + ?, 
            total_balance = total_balance + ? 
            WHERE firstname = ? AND lastname = ? AND IBAN = ?");
        $stmt3->bind_param("ddss", $total_amount, $total_amount, $firstname, $lastname, $IBAN);

        // ✅ Fix: bind_param should have 5 params not 4 (dd + sss = 5)
        $stmt3 = $conn->prepare("UPDATE accounts SET 
            amounts_from_others = amounts_from_others + ?, 
            total_balance = total_balance + ? 
            WHERE firstname = ? AND lastname = ? AND IBAN = ?");
        $stmt3->bind_param("ddsss", $total_amount, $total_amount, $firstname, $lastname, $IBAN);
        $result3 = $stmt3->execute();
        $stmt3->close();

        if ($result2 && $result3) {
            echo '<script type="text/javascript">alert("Transfer completed successfully!"); location.href="transf_anyone_bank.php";</script>';
        } else {
            echo '<script type="text/javascript">alert("Transfer failed. Please try again.");</script>';
        }

        $conn->close();
    }
}
?>