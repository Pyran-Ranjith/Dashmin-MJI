<?php
ob_start();
session_start();
// require_once('header.php'); // Include header

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/* Testing Section */
echo "<h1>Welcome, Admin</h1>";
