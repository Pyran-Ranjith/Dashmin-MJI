<?php
// manage_sales.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$crud_permissions = $_SESSION['crud_permissions'];

include('db.php');
include('header.php');
$pdo = $conn;

// Initialize $sales as an empty array
$sales = [];

// Fetch sales records (only active records)
try {
    $stmt = $pdo->query("SELECT sales.id, customers.first_name, customers.last_name, 
        stocks.part_number, stocks.description, 
        sales.quantity_sold, sales.total_price, sales.sale_date
        FROM sales  
        JOIN customers ON sales.customer_id = customers.id 
        JOIN stocks ON sales.stock_id = stocks.id
        WHERE sales.flag = 'active'"); // Only fetch active sales
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle the error (log it, show a message, etc.)
    die("An error occurred while fetching sales: " . $e->getMessage());
}

// Fetch customers and stocks for dropdowns
$customers = $pdo->query("SELECT id, first_name, last_name FROM customers")->fetchAll(PDO::FETCH_ASSOC);
$stocks = $pdo->query("SELECT id, part_number, stock_quantity FROM stocks WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC); // Only fetch active stocks

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
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, stock_id, quantity_sold, total_price, sale_date, flag) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date]);

            // ===== FIFO IMPLEMENTATION =====
            $remaining = $quantity_sold;
            $stmt = $pdo->prepare("SELECT * FROM fifo_queue1 
                                 WHERE part_id = ? 
                                 AND is_processed = 0
                                 ORDER BY id ASC");
            $stmt->execute([$stock_id]);
            $fifo_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fifo_items as $item) {
                if ($remaining <= 0) break;

                $available = $item['quantity'];
                $used = min($available, $remaining);

                if ($used == $available) {
                    $update = $pdo->prepare("UPDATE fifo_queue1 SET is_processed = 1 WHERE id = ?");
                    $update->execute([$item['id']]);
                } else {
                    $new_quantity = $available - $used;
                    $update = $pdo->prepare("UPDATE fifo_queue1 SET quantity = ? WHERE id = ?");
                    $update->execute([$new_quantity, $item['id']]);
                }

                $remaining -= $used;
            }

            if ($remaining > 0) {
                throw new Exception("Not enough stock available in FIFO queue");
            }
            // ===== END FIFO =====


            // Deduct the stock quantity
            $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
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

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        // Fetch the quantity sold and stock ID
        $stmt = $pdo->prepare("SELECT quantity_sold, stock_id FROM sales WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sale) {
            // Set the sale flag to inactive
            $stmt = $pdo->prepare("UPDATE sales SET flag = 'inactive' WHERE id=?");
            $stmt->execute([$_GET['delete']]);

            // Increase the stock quantity (since the sale is no longer active)
            $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
            $stmt->execute([$sale['quantity_sold'], $sale['stock_id']]);

            // ===== FIFO IMPLEMENTATION =====
            $stmt = $pdo->prepare("UPDATE fifo_queue1 SET is_processed = 0 
                                 WHERE part_id = ? 
                                 AND is_processed = 1
                                 ORDER BY id DESC 
                                 LIMIT ?");
            $stmt->execute([$sale['stock_id'], $sale['quantity_sold']]);
            // ===== END FIFO =====
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

// Handle edit fetch (only active records)
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=? AND flag = 'active'");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="container mt-4">
    <h2>Sales Management</h2>
    <?php if ($crud_permissions['flag_create'] === 'active'): ?>
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
                            <!-- <?= htmlspecialchars($stock['part_number'] . ' (Available: ' . $stock['stock_quantity'] . ')') ?> -->
                            <?= htmlspecialchars($stock['part_number']) ?>
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
    <?php else: ?>
        <p>You do not have permission to create or edit sales.</p>
    <?php endif; ?>
</div>
<table class=" table-striped table table-bordered">
    <thead class="table-dark">
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
        <?php if (!empty($sales)): ?>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></td>
                    <td><?= htmlspecialchars($sale['part_number']) ?></td>
                    <td><?= htmlspecialchars($sale['description']) ?></td>
                    <td><?= htmlspecialchars($sale['quantity_sold']) ?></td>
                    <td><?= htmlspecialchars($sale['total_price']) ?></td>
                    <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                    <td>
                        <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                            <a href="?edit=<?= $sale['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <?php endif; ?>
                        <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                            <a href="?delete=<?= $sale['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sale?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No sales records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>