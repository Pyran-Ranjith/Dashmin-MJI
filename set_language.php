<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Redirect back to the previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
