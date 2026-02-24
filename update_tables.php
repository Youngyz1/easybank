<?php
require_once('__SRC__/connect.php');

$obj_conn = new DATABASE_CONNECT();
$conn = $obj_conn->get_connection();

$queries = [
    "ALTER TABLE customers ADD COLUMN firstname VARCHAR(50) NOT NULL AFTER id, ADD COLUMN lastname VARCHAR(50) NOT NULL AFTER firstname;",
    "ALTER TABLE accounts ADD COLUMN firstname VARCHAR(50) NOT NULL AFTER email, ADD COLUMN lastname VARCHAR(50) NOT NULL AFTER firstname;",
    "ALTER TABLE notifications ADD COLUMN firstname VARCHAR(50) NOT NULL AFTER email, ADD COLUMN lastname VARCHAR(50) NOT NULL AFTER firstname;"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Query executed successfully.<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

$conn->close();
