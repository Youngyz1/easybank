<?php
require_once('__SRC__/connect.php');

$obj_conn = new DATABASE_CONNECT();
$conn = new mysqli($obj_conn->connect[0], $obj_conn->connect[1], $obj_conn->connect[2], $obj_conn->connect[3]);

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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
