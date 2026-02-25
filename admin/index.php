<?php
/*
 * Copyright (c) 2018 Barchampas Gerasimos <makindosx@gmail.com>
 * online-banking a online banking system for local businesses.
 ...
 */
session_start();

// Check if already logged in
if (isset($_SESSION['login']) && $_SESSION['login'] === 'easybank') {
    header('Location: home.php');
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $admin_pass = getenv("ADMIN_PASSWORD") ?: "easybank";
    $password = $_POST['password'];
    
    // Simple plain text comparison
    if ($password === $admin_pass) {
        $_SESSION['login'] = "easybank";
        header('Location: home.php');
        exit;
    } else {
        $error = "Sign in control panel error";
    }
}
?>