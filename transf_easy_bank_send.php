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
 */

session_start();

if (!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

// Session timeout (15 minutes)
$idletime = 900;
if (time() - $_SESSION['timestamp'] > $idletime) {
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
}
$_SESSION['timestamp'] = time();

if (isset($_POST['transfer_easy_bank'])) {
    // Enable error logging
    error_reporting(E_ALL);
    ini_set('display_errors', FALSE);
    ini_set('log_errors', TRUE);

    require_once('__SRC__/connect.php');
    require_once('__SRC__/secure_db.php');
    require_once('__SRC__/csrf.php');

    // Verify CSRF token
    verify_csrf_token();

    if (!class_exists('DATABASE_CONNECT')) {
        die("Database connection class not found");
    }

    $obj_conn = new DATABASE_CONNECT;
    $conn = $obj_conn->get_connection();
    $db = new SECURE_DB($conn);

    // Validate and sanitize input
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $account_no = trim($_POST['account_no'] ?? '');
    $main_amount = trim($_POST['main_amount'] ?? '0');
    $secondary_amount = trim($_POST['secondary_amount'] ?? '0');

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($account_no)) {
        echo '<script type="text/javascript">alert("All fields are required.");</script>';
        exit;
    }

    // Validate amounts are numeric
    if (!is_numeric($main_amount) || !is_numeric($secondary_amount)) {
        echo '<script type="text/javascript">alert("Invalid amount format.");</script>';
        exit;
    }

    $total_amount = floatval($main_amount) + (floatval($secondary_amount) / 100);

    if ($total_amount <= 0) {
        echo '<script type="text/javascript">alert("Invalid transfer amount.");</script>';
        exit;
    }

    // Get sender's balance using prepared statement
    $sender_email = $_SESSION['login'];
    $row = $db->fetchRow(
        "SELECT total_balance FROM accounts WHERE email = ?",
        [$sender_email]
    );

    if (!$row) {
        echo '<script type="text/javascript">alert("Account not found.");</script>';
        exit;
    }

    $total_balance = floatval($row['total_balance']);

    if ($total_amount > $total_balance) {
        echo '<script type="text/javascript">alert("You do not have enough balance to do this transfer.");</script>';
        exit;
    }

    // Find recipient by name and account number
    $recipient = $db->fetchRow(
        "SELECT email FROM accounts WHERE firstname = ? AND lastname = ? AND account_no = ?",
        [$firstname, $lastname, $account_no]
    );

    if (!$recipient) {
        echo '<script type="text/javascript">alert("Recipient account not found.");</script>';
        exit;
    }

    // Check for self-transfer
    if ($recipient['email'] === $sender_email) {
        echo '<script type="text/javascript">alert("Cannot transfer to your own account.");</script>';
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Deduct from sender
        $db->execute(
            "UPDATE accounts SET amounts_transferred = amounts_transferred + ?, 
             total_balance = total_balance - ? WHERE email = ?",
            [$total_amount, $total_amount, $sender_email]
        );

        // Add to recipient
        $db->execute(
            "UPDATE accounts SET amounts_from_others = amounts_from_others + ?, 
             total_balance = total_balance + ? WHERE firstname = ? AND lastname = ? AND account_no = ?",
            [$total_amount, $total_amount, $firstname, $lastname, $account_no]
        );

        // Commit transaction
        $conn->commit();

        echo '<script type="text/javascript">alert("Transfer completed successfully!"); location.href="transf_easy_bank.php";</script>';
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Transfer error: " . $e->getMessage());
        echo '<script type="text/javascript">alert("Transfer failed. Please try again.");</script>';
    }

    $conn->close();
}
?>