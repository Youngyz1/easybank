<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // or your MySQL password
$db   = 'easybank';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";
?>
