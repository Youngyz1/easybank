<?php
// setup.php
require_once('__SRC__/connect.php'); // make sure this points to your DATABASE_CONNECT class

// Connect to the database
$obj_conn = new DATABASE_CONNECT();
$conn = new mysqli(
    $obj_conn->connect[0],
    $obj_conn->connect[1],
    $obj_conn->connect[2],
    $obj_conn->connect[3]
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create 'customers' table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'customers' is ready!\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Optional: insert a test user (password hashed)
$testName = "Test User";
$testEmail = "test@example.com";
$testPassword = password_hash("password123", PASSWORD_DEFAULT);

// Check if test user exists
$stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
$stmt->bind_param("s", $testEmail);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO customers (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $testName, $testEmail, $testPassword);
    $stmt->execute();
    echo "Test user inserted.\n";
} else {
    echo "Test user already exists.\n";
}

$stmt->close();
$conn->close();
?>
