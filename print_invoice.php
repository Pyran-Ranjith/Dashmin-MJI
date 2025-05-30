<?php
require 'db.php';

if (!isset($_GET['sale_id'])) {
    die("Invalid request");
}

$sale_id = $_GET['sale_id'];
$pdo = $conn;

// Fetch sale details
$stmt = $pdo->prepare("SELECT sales.id, customers.first_name, customers.last_name, 
    stocks.part_number, stocks.description, 
    sales.quantity_sold, sales.total_price, sales.sale_date
    FROM sales  
    JOIN customers ON sales.customer_id = customers.id 
    JOIN stocks ON sales.stock_id = stocks.id
    WHERE sales.id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    die("Sale not found");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice - INV-<?= htmlspecialchars($sale['id']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .invoice-box {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <h2 class="text-center">Invoice</h2>
        <p><strong>Invoice #:</strong> INV-<?= htmlspecialchars($sale['id']) ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></p>
        <p><strong>Part Number:</strong> <?= htmlspecialchars($sale['part_number']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($sale['description']) ?></p>
        <p><strong>Quantity:</strong> <?= htmlspecialchars($sale['quantity_sold']) ?></p>
        <p><strong>Total Price:</strong> <?= htmlspecialchars($sale['total_price']) ?></p>
        <p><strong>Sale Date:</strong> <?= htmlspecialchars($sale['sale_date']) ?></p>
        <hr>
        <p class="text-center">Thank you for your business!</p>
        <div class="text-center">
            <button onclick="window.print();" class="btn btn-primary">Print Invoice</button>
            <a href="invoices.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
</body>
</html>
