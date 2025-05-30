<?php
// invoice.php

// Start session and include the database connection
session_start();
require_once 'db.php';  // Ensure this is your database connection file

// Check if the user is logged in (if applicable)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch sales data from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM sales ORDER BY date DESC");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching sales data: " . $e->getMessage());
}

// Calculate total sales amount
$totalAmount = 0;
foreach ($sales as $sale) {
    $totalAmount += $sale['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Sales Management</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-header {
            margin-bottom: 30px;
        }
        .invoice-table th, .invoice-table td {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Invoice Header -->
        <div class="invoice-header text-center">
            <h2>Sales Invoice</h2>
            <p>Date: <?php echo date('Y-m-d'); ?></p>
        </div>

        <!-- Sales Table -->
        <div class="table-responsive">
            <table class="table table-bordered invoice-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sales): ?>
                        <?php foreach ($sales as $index => $sale): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                                <td><?php echo number_format($sale['price'], 2); ?></td>
                                <td><?php echo number_format($sale['total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($sale['date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No sales data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Total Sales -->
        <div class="text-right">
            <h4>Total Sales: $<?php echo number_format($totalAmount, 2); ?></h4>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

