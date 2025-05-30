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

// Initialize variables
$sales = [];
$wac_info = null;
$wac_error = null;
$selected_stock_id = null;

// Fetch sales records (only active records)
try {
    $stmt = $pdo->query("SELECT sales.id, customers.first_name, customers.last_name, 
        stocks.part_number, stocks.description, 
        sales.quantity_sold, sales.total_price, sales.sale_date
        FROM sales  
        JOIN customers ON sales.customer_id = customers.id 
        JOIN stocks ON sales.stock_id = stocks.id
        WHERE sales.flag = 'active'");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred while fetching sales: " . $e->getMessage());
}

// Fetch customers and stocks for dropdowns
$customers = $pdo->query("SELECT id, first_name, last_name FROM customers")->fetchAll(PDO::FETCH_ASSOC);
$stocks = $pdo->query("SELECT id, part_number, stock_quantity FROM stocks WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $customer_id = intval($_POST['customer_id']);
    $stock_id = intval($_POST['stock_id']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $total_price = floatval($_POST['total_price']);
    $sale_date = $_POST['sale_date'];
    $selected_stock_id = $stock_id;

    try {
        $pdo->beginTransaction();

        if ($id) {
            // Update existing sale
            $stmt = $pdo->prepare("SELECT quantity_sold, stock_id FROM sales WHERE id=?");
            $stmt->execute([$id]);
            $old_sale = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_quantity_sold = $old_sale['quantity_sold'];
            $old_stock_id = $old_sale['stock_id'];

            $stmt = $pdo->prepare("UPDATE sales SET customer_id=?, stock_id=?, quantity_sold=?, total_price=?, sale_date=? WHERE id=?");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date, $id]);

            if ($old_stock_id != $stock_id) {
                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
                $stmt->execute([$old_quantity_sold, $old_stock_id]);

                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
                $stmt->execute([$quantity_sold, $stock_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? - ? WHERE id=?");
                $stmt->execute([$old_quantity_sold, $quantity_sold, $stock_id]);
            }
        } else {
            // Create new sale
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, stock_id, quantity_sold, total_price, sale_date, flag) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date]);

            // FIFO implementation
            $remaining = $quantity_sold;
            $stmt = $pdo->prepare("SELECT * FROM fifo_queue1 WHERE part_id = ? AND is_processed = 0 ORDER BY id ASC");
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
                    $update = $pdo->prepare("UPDATE fifo_queue1 SET quantity = ? WHERE id = ?");
                    $update->execute([$available - $used, $item['id']]);
                }

                $remaining -= $used;
            }

            if ($remaining > 0) {
                throw new Exception("Not enough stock available in FIFO queue");
            }

            $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
            $stmt->execute([$quantity_sold, $stock_id]);
        }

        $pdo->commit();
        header("Location: manage_sales.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $wac_error = "An error occurred: " . $e->getMessage();
    }
}

// Calculate WAC if stock is selected
if (isset($_POST['stock_id']) || isset($_GET['edit'])) {
    $stock_id = isset($_POST['stock_id']) ? intval($_POST['stock_id']) : (isset($edit) ? $edit['stock_id'] : null);
    $selected_stock_id = $stock_id;

    if ($stock_id) {
        try {
            // Current WAC (unsold only)
            $stmt = $pdo->prepare("SELECT 
                    COALESCE(SUM(quantity * cost), 0) AS total_cost,
                    COALESCE(SUM(quantity), 0) AS total_quantity,
                    CASE WHEN SUM(quantity) > 0 
                         THEN SUM(quantity * cost) / SUM(quantity) 
                         ELSE 0 
                    END AS current_wac
                FROM fifo_queue1
                WHERE part_id = ? AND is_processed = 0");
            $stmt->execute([$stock_id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            // Historical WAC (all items)
            $stmt = $pdo->prepare("SELECT 
                    COALESCE(SUM(quantity * cost), 0) AS total_cost,
                    COALESCE(SUM(quantity), 0) AS total_quantity,
                    CASE WHEN SUM(quantity) > 0 
                         THEN SUM(quantity * cost) / SUM(quantity) 
                         ELSE 0 
                    END AS historical_wac
                FROM fifo_queue1
                WHERE part_id = ?");
            $stmt->execute([$stock_id]);
            $historical = $stmt->fetch(PDO::FETCH_ASSOC);

            $wac_info = [
                'current_wac' => $current['current_wac'],
                'current_quantity' => $current['total_quantity'],
                'historical_wac' => $historical['historical_wac'],
                'historical_quantity' => $historical['total_quantity']
            ];
        } catch (PDOException $e) {
            $wac_error = "Error calculating WAC: " . $e->getMessage();
        }
    }
}

// Handle "delete"
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT quantity_sold, stock_id FROM sales WHERE id=?");
        $stmt->execute([$_GET['delete']]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sale) {
            $stmt = $pdo->prepare("UPDATE sales SET flag = 'inactive' WHERE id=?");
            $stmt->execute([$_GET['delete']]);

            $stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
            $stmt->execute([$sale['quantity_sold'], $sale['stock_id']]);

            $stmt = $pdo->prepare("UPDATE fifo_queue1 SET is_processed = 0 
                                 WHERE part_id = ? AND is_processed = 1
                                 ORDER BY id DESC LIMIT ?");
            $stmt->execute([$sale['stock_id'], $sale['quantity_sold']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header("Location: manage_sales.php");
    exit;
}

// Handle edit fetch
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE id=? AND flag = 'active'");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
    $selected_stock_id = $edit['stock_id'];
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
                        <option value="<?= $customer['id'] ?>" <?= (isset($edit) && $edit['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><strong>Stock</strong></label>
                <select name="stock_id" class="form-select" required onchange="this.form.submit()">
                    <option value="" disabled selected>Select a Stock</option>
                    <?php foreach ($stocks as $stock): ?>
                        <option value="<?= $stock['id'] ?>" 
                            <?= (isset($selected_stock_id) && $selected_stock_id == $stock['id']) || (isset($edit) && $edit['stock_id'] == $stock['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stock['part_number']) ?> (Available: <?= $stock['stock_quantity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (isset($wac_info)): ?>
                <div class="alert alert-info mb-3">
                    <strong>WAC Information:</strong>
                    <table class="table table-sm mt-2">
                        <tr>
                            <th>Current WAC (Unsold):</th>
                            <td>Rs. <?= number_format($wac_info['current_wac'], 2) ?></td>
                            <td>(<?= $wac_info['current_quantity'] ?> units available)</td>
                        </tr>
                        <tr>
                            <th>Historical WAC:</th>
                            <td>Rs. <?= number_format($wac_info['historical_wac'], 2) ?></td>
                            <td>(<?= $wac_info['historical_quantity'] ?> units total)</td>
                        </tr>
                    </table>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Selling Price (X)</strong></label>
                    <input type="number" name="selling_price" class="form-control" 
                           placeholder="Enter selling price" step="0.01" 
                           min="<?= $wac_info['current_wac'] ?>" required
                           value="<?= $edit['total_price'] ?? '' ?>">
                </div>
            <?php endif; ?>

            <?php if (isset($wac_error)): ?>
                <div class="alert alert-danger">
                    <?= $wac_error ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label"><strong>Quantity Sold</strong></label>
                <input type="number" name="quantity_sold" class="form-control" 
                       placeholder="Enter Quantity Sold" required 
                       value="<?= $edit['quantity_sold'] ?? '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label"><strong>Sale Date</strong></label>
                <input type="date" name="sale_date" class="form-control" 
                       placeholder="Enter Sale Date" required 
                       value="<?= $edit['sale_date'] ?? date('Y-m-d') ?>">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="manage_sales.php" class="btn btn-secondary">Back to list</a>
        </form>
    <?php else: ?>
        <p>You do not have permission to create or edit sales.</p>
    <?php endif; ?>
</div>

<table class="table table-striped table-bordered mt-4">
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
                    <td>Rs. <?= number_format($sale['total_price'], 2) ?></td>
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