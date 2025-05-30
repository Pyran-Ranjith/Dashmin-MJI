<?php
ob_start();
include('db.php');
include('header.php');
$pdo = $conn;

// Fetch sales records
$stmt = $pdo->query("SELECT sales.id, customers.first_name, customers.last_name, 
    stocks.part_number, stocks.description, 
    sales.quantity_sold, sales.total_price, sales.sale_date
    FROM sales  
    JOIN customers ON sales.customer_id = customers.id 
    JOIN stocks ON sales.stock_id = stocks.id");
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch customers and stocks for dropdowns
$customers = $pdo->query("SELECT id, first_name, last_name FROM customers")->fetchAll(PDO::FETCH_ASSOC);
$stocks = $pdo->query("SELECT id, part_number FROM stocks")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Sales Management</h2>
    <h3>Select Sales for Invoice</h3>
    <form method="GET" action="print_invoice.php" class="mt-3">
        <div class="mb-3">
            <label class="form-label"><strong>Select Sales</strong></label>
            <select name="id[]" class="form-select" multiple required>
                <?php foreach ($sales as $sale): ?>
                    <option value="<?= $sale['id'] ?>">
                        Sale ID: <?= $sale['id'] ?> - <?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?> - Total: <?= $sale['total_price'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generate Invoice</button>
    </form>
</div>

<table class="table table-bordered mt-4">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Part Number</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Sale Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></td>
                <td><?= htmlspecialchars($sale['part_number']) ?></td>
                <td><?= htmlspecialchars($sale['description']) ?></td>
                <td><?= htmlspecialchars($sale['quantity_sold']) ?></td>
                <td><?= htmlspecialchars($sale['total_price']) ?></td>
                <td><?= htmlspecialchars($sale['sale_date']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>
