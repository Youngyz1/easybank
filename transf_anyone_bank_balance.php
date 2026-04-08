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

// FIX #1: Check authentication
if(!isset($_SESSION['login'])) {
    header('Location: index.php');
    exit;  // CRITICAL: Must exit after redirect!
}

// FIX #2: Session timeout check at top level
$idletime = 900; // 15 minutes

if (time() - $_SESSION['timestamp'] > $idletime) {
    session_destroy();
    session_unset();
    header('Location: index.php');
    exit;
} else {
    $_SESSION['timestamp'] = time();
}

// FIX #3: CSRF Token validation
if (isset($_POST['transfer_anyone_bank'])) {
    
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<div class='alert alert-danger' role='alert'>
                <strong>Security Error:</strong> Invalid request. Please try again.
              </div>";
        exit;
    }

    require_once('__SRC__/connect.php');

    // FIX #4: Proper database connection handling
    if (!class_exists('DATABASE_CONNECT')) {
        error_log("Database class not found");
        echo "<div class='alert alert-danger' role='alert'>
                <strong>System Error:</strong> Unable to connect to database.
              </div>";
        exit;
    }

    $obj_conn = new DATABASE_CONNECT;
    $conn = $obj_conn->get_connection();

    if (!$conn) {
        error_log("Database connection failed");
        echo "<div class='alert alert-danger' role='alert'>
                <strong>System Error:</strong> Database connection failed.
              </div>";
        exit;
    }

    // FIX #5: Input validation - ensure POST data exists and is valid
    if (!isset($_POST['main_amount']) || !isset($_POST['secondary_amount'])) {
        echo "<div class='alert alert-danger' role='alert'>
                <strong>Error:</strong> Missing amount information.
              </div>";
        exit;
    }

    // Validate and sanitize amounts
    $main_amount = filter_var($_POST['main_amount'], FILTER_VALIDATE_INT);
    $secondary_amount = filter_var($_POST['secondary_amount'], FILTER_VALIDATE_INT);

    if ($main_amount === false || $main_amount <= 0) {
        echo "<div class='alert alert-danger' role='alert'>
                <strong>Error:</strong> Invalid main amount.
              </div>";
        exit;
    }

    if ($secondary_amount === false || $secondary_amount < 0 || $secondary_amount > 99) {
        echo "<div class='alert alert-danger' role='alert'>
                <strong>Error:</strong> Invalid secondary amount (must be 0-99).
              </div>";
        exit;
    }

    // FIX #6: Use proper numeric calculation instead of string concatenation
    $total_amount = $main_amount + ($secondary_amount / 100);

    // Sanitize email
    $email = filter_var($_SESSION['login'], FILTER_SANITIZE_EMAIL);

    // FIX #7: Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT total_balance FROM accounts WHERE email = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo "<div class='alert alert-danger' role='alert'>
                <strong>System Error:</strong> Database error occurred.
              </div>";
        exit;
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo "<div class='alert alert-danger' role='alert'>
                <strong>System Error:</strong> Database error occurred.
              </div>";
        exit;
    }

    $result = $stmt->get_result();
    if (!$result) {
        error_log("Get result failed: " . $stmt->error);
        echo "<div class='alert alert-danger' role='alert'>
                <strong>System Error:</strong> Database error occurred.
              </div>";
        exit;
    }

    // FIX #8: Proper error handling for no results
    if ($result->num_rows === 0) {
        error_log("Account not found: $email");
        echo "<div class='alert alert-danger' role='alert'>
                <strong>Error:</strong> Account not found.
              </div>";
        $stmt->close();
        $conn->close();
        exit;
    }

    // Process the balance check
    while ($row = $result->fetch_assoc()) {
        
        if (!isset($row['total_balance'])) {
            error_log("total_balance field missing for: $email");
            echo "<div class='alert alert-danger' role='alert'>
                    <strong>System Error:</strong> Invalid account data.
                  </div>";
            break;
        }

        $account_balance = floatval($row['total_balance']);

        // FIX #9: Proper transaction fee handling
        $transaction_fee = 3; // Fixed fee in currency units (should this be configurable?)
        $total_amount_with_fee = $total_amount + $transaction_fee;

        // FIX #10: Comprehensive balance validation
        if ($total_amount <= 0) {
            echo "<div class='alert alert-danger' role='alert'>
                    <strong>Error:</strong> Transfer amount must be greater than zero.
                  </div>";
            break;
        }

        if ($total_amount_with_fee > $account_balance) {
            echo "<div class='alert alert-danger' role='alert'>
                    <strong>Insufficient Balance:</strong> You do not have enough balance to complete this transfer.
                    <br>Required: " . htmlspecialchars(number_format($total_amount_with_fee, 2), ENT_QUOTES, 'UTF-8') . " EUR
                    <br>Available: " . htmlspecialchars(number_format($account_balance, 2), ENT_QUOTES, 'UTF-8') . " EUR
                  </div>";
            break;
        }

        // FIX #11: Optional warning if balance would be too low
        $minimum_balance = 10; // Minimum balance to keep in account
        $balance_after_transfer = $account_balance - $total_amount_with_fee;

        if ($balance_after_transfer < $minimum_balance) {
            echo "<div class='alert alert-warning' role='alert'>
                    <strong>Warning:</strong> After this transfer, your balance will be low.
                    <br>Balance after transfer: " . htmlspecialchars(number_format($balance_after_transfer, 2), ENT_QUOTES, 'UTF-8') . " EUR
                  </div>";
        }

        // Balance is sufficient, allow transfer to proceed
        // (other validation files will handle the rest)

    } // end of while

    $stmt->close();
    $conn->close();

} // end of if isset POST transfer button

?>