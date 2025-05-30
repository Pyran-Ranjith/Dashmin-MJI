<?php
ob_start();
// header ("Location: ./manage_stock.php");
// // header ("Location: ./manage_stocks_WithViewButton.php");

// Ensure user is logged in
if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    session_unset();
    session_destroy();
}

// header ("Location: ./dashboard.php");
header ("Location: ./admin_dashboard.php");
// header ("Location: ./dashboard_WithMenuOptions.php");
?>
<h1>Test</h1>

