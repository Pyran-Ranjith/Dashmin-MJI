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
$stocks = $pdo->query("SELECT id, part_number, stock_quantity FROM stocks")->fetchAll(PDO::FETCH_ASSOC);

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $customer_id = intval($_POST['customer_id']);
    $stock_id = intval($_POST['stock_id']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $total_price = floatval($_POST['total_price']);
    $sale_date = $_POST['sale_date'];

    try {
        $pdo->beginTransaction();

        if ($id) {
            // Fetch the old quantity sold and stock ID
            $stmt = $pdo->prepare("SELECT quantity_sold, stock_id FROM sales WHERE id=?");
            $stmt->execute([$id]);
            $old_sale = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_quantity_sold = $old_sale['quantity_sold'];
            $old_stock_id = $old_sale['stock_id'];

            // Update the sale
            $stmt = $pdo->prepare("UPDATE sales SET customer_id=?, stock_id=?, quantity_sold=?, total_price=?, sale_date=? WHERE id=?");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date, $id]);

            // Adjust the stock quantity for the old stock item
            if ($old_stock_id != $stock_id) {
                // Restore the old stock quantity
                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
                $stmt->execute([$old_quantity_sold, $old_stock_id]);

                // Deduct the new stock quantity
                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
                $stmt->execute([$quantity_sold, $stock_id]);
            } else {
                // Adjust the stock quantity for the same stock item
                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? - ? WHERE id=?");
                $stmt->execute([$old_quantity_sold, $quantity_sold, $stock_id]);
            }
        } else {
            // Insert the new sale
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, stock_id, quantity_sold, total_price, sale_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date]);

            // Deduct the stock quantity
            $stmt = $pdo->prepare("UPDATE stocks SET quantity = quantity - ? WHERE id=?");
            $stmt->execute([$quantity_sold, $stock_id]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Handle the error (log it, show a message, etc.)
        die("An error occurred: " . $e->getMessage());
    }

    header("Location: manage_sales.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        // Fetch the quantity sold and stock ID
        $stmt = $pdo->prepare("SELECT quantity_sold, stock_id FROM sales WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sale) {
            // Delete the sale
            $stmt = $pdo->prepare("DELETE FROM sales WHERE id=?");
            $stmt->execute([$_GET['delete']]);

            // Increase the stock quantity
            $stmt = $pdo->prepare("UPDATE stocks SET quantity = quantity + ? WHERE id=?");
            $stmt->execute([$sale['quantity_sold'], $sale['stock_id']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Handle the error (log it, show a message, etc.)
        die("An error occurred: " . $e->getMessage());
    }

    header("Location: manage_sales.php");
    exit;
}

// Handle edit fetch
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="container mt-4">
    <h2>Sales Management</h2>
    <h3><?= isset($_GET['edit']) ? 'Edit' : 'Add' ?> Sale</h3>
    <form method="POST" class="mt-3">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <div class="mb-3">
            <label class="form-label"><strong>Customer</strong></label>
            <select name="customer_id" class="form-select" required>
                <option value="" disabled selected>Select a Customer</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= $customer['id'] ?>" <?= isset($edit) && $edit['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label"><strong>Stock</strong></label>
            <select name="stock_id" class="form-select" required>
                <option value="" disabled selected>Select a Stock</option>
                <?php foreach ($stocks as $stock): ?>
                    <option value="<?= $stock['id'] ?>" <?= isset($edit) && $edit['stock_id'] == $stock['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($stock['part_number'] . ' (Available: ' . $stock['quantity'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label"><strong>Quantity Sold</strong></label>
            <input type="number" name="quantity_sold" class="form-control" placeholder="Enter Quantity Sold" required value="<?= $edit['quantity_sold'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label"><strong>Total Price</strong></label>
            <input type="text" name="total_price" class="form-control" placeholder="Enter Total Price" required value="<?= $edit['total_price'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label class="form-label"><strong>Sale Date</strong></label>
            <input type="date" name="sale_date" class="form-control" placeholder="Enter Sale Date" required value="<?= $edit['sale_date'] ?? '' ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="manage_sales.php" class="btn btn-secondary btn-md">Back to list</a>
    </form>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Part Number</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Sale Date</th>
            <th>Actions</th>
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
                <td>
                    <a href="?edit=<?= $sale['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="?delete=<?= $sale['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>