<?php
ob_start();
// include_once($path_ . '/db.php');
// include_once('header.php');
// require_once('./parameters.php'); // For root level
// Use __DIR__ to get the absolute path to parameters.php
require_once(__DIR__ . '/parameters.php');


try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection SUCEED. .";
} catch (PDOException $e) {
    // Handle connection failure
    echo "Connection failed: " . $e->getMessage();
}

// require_once('./db_sales.php');
// $sales = new sales($conn);

// require_once('./db_supplier.php');
// $supplier = new supplier($conn);

// require_once('./db_stocks.php');
// $stocks = new stocks($conn);
