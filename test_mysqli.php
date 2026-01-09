<?php
$mysqli = new mysqli("localhost", "root", "", "mysql");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "MySQLi is working!";
?>
