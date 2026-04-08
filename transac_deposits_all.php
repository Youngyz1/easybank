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

$email = $_SESSION['login'];

require_once('__SRC__/connect.php');

if (!class_exists('DATABASE_CONNECT')) {
    die("Database class not found.");
}

$obj_conn = new DATABASE_CONNECT;
$conn = $obj_conn->get_connection();

// ✅ Validate and sanitize GET parameter
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($q)) {
    die("Invalid request.");
}

// ✅ Whitelist allowed values — only IBAN, account_no or 'All_banks'
// This prevents arbitrary SQL via $q
$rows = [];
$type = '';

if (strpos($q, 'EB') !== false) {
    // Anyone Bank — IBAN based
    $type = 'anyone_bank';
    $stmt = $conn->prepare("SELECT date_transfer, _from_customer_lastname, _from_customer_firstname,
                _from_customer_IBAN, _to_customer_lastname, _to_customer_firstname,
                _to_customer_IBAN, transaction_number, amount
                FROM transactions_anyone_bank
                WHERE _from_customer_IBAN = ?
                ORDER BY date_transfer DESC");
    $stmt->bind_param("s", $q);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

} elseif (strpos($q, 'All') !== false) {
    // All Banks
    $type = 'all_banks';
    $stmt2 = $conn->prepare("SELECT account_no, IBAN FROM accounts WHERE email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $stmt2->close();

    $account_no2 = $row2['account_no'] ?? '';
    $IBAN2       = $row2['IBAN'] ?? '';

    $stmt3 = $conn->prepare("SELECT date_transfer, _from_customer_lastname, _from_customer_firstname,
                _from_customer_accno_iban, _to_customer_lastname, _to_customer_firstname,
                _to_customer_accno_iban, transaction_number, amount
                FROM transactions_all
                WHERE _from_customer_accno_iban = ? OR _from_customer_accno_iban = ?
                ORDER BY date_transfer DESC");
    $stmt3->bind_param("ss", $account_no2, $IBAN2);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $stmt3->close();
    while ($row3 = $result3->fetch_assoc()) {
        $rows[] = $row3;
    }

} else {
    // Easy Bank — account_no based
    $type = 'easy_bank';
    $stmt4 = $conn->prepare("SELECT date_transfer, _from_customer_lastname, _from_customer_firstname,
                _from_customer_account_no, _to_customer_lastname, _to_customer_firstname,
                _to_customer_account_no, transaction_number, amount
                FROM transactions_easy_bank
                WHERE _from_customer_account_no = ?
                ORDER BY date_transfer DESC");
    $stmt4->bind_param("s", $q);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    $stmt4->close();
    while ($row4 = $result4->fetch_assoc()) {
        $rows[] = $row4;
    }
}

$conn->close();
?>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Easybank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon.png" type="image/png">

    <style>
    .panel-body { background: transparent; display: inline-block; overflow-y: scroll; max-height: 600px; width: 98%; }
    table { table-layout: fixed; }
    th { background-color: #D4EDDA; }
    tr { transition: all 0.5s; }
    tr:hover { background-color: #f0ad4e; transition: 0.5s; }
    .btn-warning { transition: all 0.8s; }
    .btn-warning:hover, .btn-warning:focus { transition: 0.8s; background-color: #428bca; border-color: #428bca; }
    .panel-footer { background-color: #5bc0de; color: white; }
    </style>
</head>

<body>

<?php if ($type === 'anyone_bank'): ?>

    <div class="panel-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center">Date Transfer</th>
                    <th class="text-center">From Customer</th>
                    <th class="text-center">From Customer IBAN</th>
                    <th class="text-center">To Customer</th>
                    <th class="text-center">To Customer IBAN</th>
                    <th class="text-center">Transaction Number</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="edit">
                    <td class="text-center"><?= htmlspecialchars($row['date_transfer']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_lastname']) ?> <?= htmlspecialchars($row['_from_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_IBAN']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_lastname']) ?> <?= htmlspecialchars($row['_to_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_IBAN']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['transaction_number']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['amount']) ?> &euro;</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div align="center">
        <a href="transac_export_anyone_bank.php">Export Transactions Anyone Bank</a>
    </div>

<?php elseif ($type === 'all_banks'): ?>

    <div class="panel-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center">Date Transfer</th>
                    <th class="text-center">From Customer</th>
                    <th class="text-center">From Customer ACC.NO/IBAN</th>
                    <th class="text-center">To Customer</th>
                    <th class="text-center">To Customer ACC.NO/IBAN</th>
                    <th class="text-center">Transaction Number</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="edit">
                    <td class="text-center"><?= htmlspecialchars($row['date_transfer']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_lastname']) ?> <?= htmlspecialchars($row['_from_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_accno_iban']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_lastname']) ?> <?= htmlspecialchars($row['_to_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_accno_iban']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['transaction_number']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['amount']) ?> &euro;</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div align="center">
        <a href="transac_export_all_banks.php">Export Transactions All Banks</a>
    </div>

<?php else: ?>

    <div class="panel-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center">Date Transfer</th>
                    <th class="text-center">From Customer</th>
                    <th class="text-center">From Account No</th>
                    <th class="text-center">To Customer</th>
                    <th class="text-center">To Account No</th>
                    <th class="text-center">Transaction Number</th>
                    <th class="text-center">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="edit">
                    <td class="text-center"><?= htmlspecialchars($row['date_transfer']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_lastname']) ?> <?= htmlspecialchars($row['_from_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_from_customer_account_no']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_lastname']) ?> <?= htmlspecialchars($row['_to_customer_firstname']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['_to_customer_account_no']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['transaction_number']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['amount']) ?> &euro;</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div align="center">
        <a href="transac_export_easy_bank.php">Export Transactions Easy Bank</a>
    </div>

<?php endif; ?>

</body>
</html>