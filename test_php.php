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


