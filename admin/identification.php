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

error_reporting(0);
ini_set('display_errors', FALSE);

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

require_once('__TMP__/connect.php');

if (!class_exists('DATABASE_CONNECT')) {
    die("Database class not found.");
}

$obj_conn = new DATABASE_CONNECT;
$host = $obj_conn->connect[0];
$user = $obj_conn->connect[1];
$pass = $obj_conn->connect[2];
$db   = $obj_conn->connect[3];

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Cannot connect: " . $conn->connect_error);
}

// ✅ Handle form submission BEFORE HTML output
if (isset($_POST['submit_account_condition'])) {
    verify_csrf_token();

    $account_condition            = $conn->real_escape_string($_POST['account_condition']);
    $lastname_account_condition   = $conn->real_escape_string($_POST['lastname_account_condition']);
    $firstname_account_condition  = $conn->real_escape_string($_POST['firstname_account_condition']);
    $id_account_condition         = intval($_POST['id_account_condition']);
    $account_no                   = $conn->real_escape_string($_POST['account_no']);
    $account_iban                 = $conn->real_escape_string($_POST['account_iban']);

    // Whitelist account_condition values
    if (!in_array($account_condition, ['block', 'active'])) {
        die("Invalid account condition.");
    }

    // ✅ Fixed SQL injection — prepared statement
    $stmt = $conn->prepare("UPDATE customers SET account_type = ? 
                            WHERE id = ? 
                            AND firstname = ? 
                            AND lastname = ? 
                            AND account_number = ? 
                            AND IBAN = ?");
    $stmt->bind_param("sissss",
        $account_condition,
        $id_account_condition,
        $firstname_account_condition,
        $lastname_account_condition,
        $account_no,
        $account_iban
    );
    $result1 = $stmt->execute();
    $stmt->close();

    if ($result1) {
        // ✅ Fixed SQL injection — prepared statement
        $stmt2 = $conn->prepare("UPDATE accounts SET account_statement = ? 
                                 WHERE account_no = ? 
                                 AND IBAN = ? 
                                 AND lastname = ? 
                                 AND firstname = ?");
        $stmt2->bind_param("sssss",
            $account_condition,
            $account_no,
            $account_iban,
            $lastname_account_condition,
            $firstname_account_condition
        );
        $stmt2->execute();
        $stmt2->close();

        echo "<script type='text/javascript'>
                alert('Account $lastname_account_condition $firstname_account_condition is $account_condition');
                location.href='identification.php';
              </script>";
        exit;
    }
}

// ✅ Fetch all customers
$sql = "SELECT id, account_number, IBAN, firstname, lastname, date_of_birth, 
               id_document_number, mobile_area_code, mobile_number,
               identity_front_data, identity_front_name, 
               identity_back_data, identity_back_name, account_type 
        FROM customers ORDER BY lastname ASC";
$result = $conn->query($sql);
?>

<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css'>

    <style>
    @import url(https://fonts.googleapis.com/css?family=Roboto:400,500,300,700);
    body {
        background: linear-gradient(to right, brown, brown);
        font-family: 'Roboto', sans-serif;
    }
    h1 { font-size: 30px; color: #fff; text-transform: uppercase; font-weight: 300; text-align: center; margin-bottom: 15px; }
    table { width: 100%; table-layout: fixed; }
    .tbl-header { background-color: rgba(255,255,255,0.3); color: white; }
    .tbl-content { height: 600px; overflow-x: auto; margin-top: 0px; border: 1px solid rgba(255,255,255,0.3); color: white; }
    th { padding: 20px 15px; text-align: left; font-weight: 500; font-size: 12px; color: white; text-transform: uppercase; }
    td { padding: 15px; text-align: left; vertical-align: middle; font-weight: 300; font-size: 12px; color: white; border-bottom: solid 1px rgba(255,255,255,0.1); }
    section { margin: 50px; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); }
    ::-webkit-scrollbar-thumb { -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); }
    a { text-decoration: none; color: white; }
    a:hover { text-decoration: none; color: black; }
    </style>
</head>

<body id="body">

<section>
    <a href='logout.php'>Sign out</a>
    <h1>Easy Bank Panel</h1>

    <div class="tbl-header">
        <table cellpadding="0" cellspacing="0" border="0">
            <thead>
                <tr>
                    <th>Lastname</th>
                    <th>Firstname</th>
                    <th>Date of birth</th>
                    <th>Id number</th>
                    <th>Mobile phone</th>
                    <th>Id front</th>
                    <th>Id back</th>
                    <th>Condition</th>
                </tr>
            </thead>
        </table>
    </div>

    <?php if (!$result): ?>
        <p>No records found.</p>
    <?php else: ?>

        <div class="tbl-content">
        <table cellpadding="0" cellspacing="0" border="0">
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $mobile_phone = $row['mobile_area_code'] . " " . $row['mobile_number'];
                $identity_number = $row['id_document_number'];

                $identity_front_name = $identity_number . ".front";
                $identity_front_data = "data:image/jpeg;base64," . base64_encode($row['identity_front_data']);

                $identity_back_name = $identity_number . ".back";
                $identity_back_data = "data:image/jpeg;base64," . base64_encode($row['identity_back_data']);

                $acc_condition1 = ($row['account_type'] == 'block') ? 'checked' : '';
                $acc_condition2 = ($row['account_type'] == 'active') ? 'checked' : '';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['lastname']) ?></td>
                    <td><?= htmlspecialchars($row['firstname']) ?></td>
                    <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
                    <td><?= htmlspecialchars($row['id_document_number']) ?></td>
                    <td><?= htmlspecialchars($mobile_phone) ?></td>
                    <td><a href="<?= $identity_front_data ?>" target="_blank"><?= htmlspecialchars($identity_front_name) ?></a></td>
                    <td><a href="<?= $identity_back_data ?>" target="_blank"><?= htmlspecialchars($identity_back_name) ?></a></td>
                    <td>
                        _________ <?= htmlspecialchars($row['account_type']) ?> _________
                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="lastname_account_condition"  value="<?= htmlspecialchars($row['lastname']) ?>">
                            <input type="hidden" name="firstname_account_condition" value="<?= htmlspecialchars($row['firstname']) ?>">
                            <input type="hidden" name="account_no"                  value="<?= htmlspecialchars($row['account_number']) ?>">
                            <input type="hidden" name="account_iban"                value="<?= htmlspecialchars($row['IBAN']) ?>">
                            <input type="hidden" name="id_account_condition"        value="<?= intval($row['id']) ?>">
                            <input type="radio" name="account_condition" value="block"  <?= $acc_condition1 ?>> Block
                            <input type="radio" name="account_condition" value="active" <?= $acc_condition2 ?>> Active
                            <input type="submit" name="submit_account_condition" value="Enter" style="color:black;">
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>

    <?php endif; ?>
</section>

<script>
$(window).on("load resize", function() {
    var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
    $('.tbl-header').css({'padding-right': scrollWidth});
}).resize();
</script>

</body>
</html>

<?php $conn->close(); ?>