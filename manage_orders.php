<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$crud_permissions = $_SESSION['crud_permissions'];

ob_start();
include('db.php');
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$customer_id = '';
$part_id = '';
$quantity = '';
$order_date = '';
$status = 'pending'; // Default status
$id = '';

// Initialize $orders as an empty array
$orders = [];

// Handle Create/Update actions
if (isset($_POST['save_order'])) {
    $customer_id = intval($_POST['customer_id']);
    $part_id = intval($_POST['part_id']);
    $quantity = intval($_POST['quantity']);
    $order_date = $_POST['order_date'];
    $status = $_POST['status'];

    try {
        $conn->beginTransaction();

        if ($_POST['id']) {
            // Update existing order
            $id = intval($_POST['id']);

            // Fetch the old quantity and part ID
            $stmt = $conn->prepare("SELECT quantity, part_id, status FROM orders WHERE id=?");
            $stmt->execute([$id]);
            $old_order = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_quantity = $old_order['quantity'];
            $old_part_id = $old_order['part_id'];
            $old_status = $old_order['status'];

            // Update the order
            $stmt = $conn->prepare("UPDATE orders SET customer_id=?, part_id=?, quantity=?, order_date=?, status=? WHERE id=?");
            $stmt->execute([$customer_id, $part_id, $quantity, $order_date, $status, $id]);

            // Adjust the stock quantity if the status changes to/from "fulfilled"
            if ($status === 'fulfilled' && $old_status !== 'fulfilled') {
                // Reduce stock quantity when order is fulfilled
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
                $stmt->execute([$quantity, $part_id]);
            } elseif ($status !== 'fulfilled' && $old_status === 'fulfilled') {
                // Restore stock quantity when order is no longer fulfilled
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
                $stmt->execute([$old_quantity, $old_part_id]);
            }
        } else {
            // Create new order
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, part_id, quantity, order_date, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customer_id, $part_id, $quantity, $order_date, $status]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_orders.php');
    exit;
}

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        $conn->beginTransaction();

        // Fetch the order details
        $stmt = $conn->prepare("SELECT quantity, part_id, status FROM orders WHERE id=?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Restore stock quantity if the order was fulfilled
            if ($order['status'] === 'fulfilled') {
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
                $stmt->execute([$order['quantity'], $order['part_id']]);
            }

            // Set the order flag to inactive
            $stmt = $conn->prepare("UPDATE orders SET flag = 'inactive' WHERE id=?");
            $stmt->execute([$id]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_orders.php');
    exit;
}

// Fetch orders, customers, and parts (stocks)
try {
    $orders_result = $conn->query("
        SELECT orders.*, customers.first_name, customers.last_name, stocks.part_number 
        FROM orders 
        JOIN customers ON orders.customer_id = customers.id 
        JOIN stocks ON orders.part_id = stocks.id
        WHERE orders.flag = 'active'
    ");
    $orders = $orders_result->fetchAll(PDO::FETCH_ASSOC);

    $customers_result = $conn->query("SELECT * FROM customers");
    $customers = $customers_result->fetchAll(PDO::FETCH_ASSOC);

    $parts_result = $conn->query("SELECT * FROM stocks WHERE flag = 'active'");
    $parts = $parts_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Pre-fill form if editing an order
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id=? AND flag = 'active'");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $customer_id = $order['customer_id'];
            $part_id = $order['part_id'];
            $quantity = $order['quantity'];
            $order_date = $order['order_date'];
            $status = $order['status'];
        }
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Manage Orders</h2>
    </div>

    <div class="card-body">
        <!-- Form to add/update order -->
        <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
            <form method="POST" action="manage_orders.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="form-group">
                    <label><strong>Customer</strong></label>
                    <select class="form-control" name="customer_id" required>
                        <option value="" disabled selected>Select a Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" <?php if ($customer['id'] == $customer_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Part</strong></label>
                    <select class="form-control" name="part_id" required>
                        <option value="" disabled selected>Select a Part</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?php echo $part['id']; ?>" <?php if ($part['id'] == $part_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($part['part_number']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Quantity</strong></label>
                    <input type="number" class="form-control" name="quantity" placeholder="Enter quantity" value="<?php echo $quantity; ?>" required>
                </div>
                <div class="form-group">
                    <label><strong>Order Date</strong></label>
                    <input type="date" class="form-control" name="order_date" value="<?php echo $order_date; ?>" required>
                </div>
                <div class="form-group">
                    <label><strong>Status</strong></label>
                    <select class="form-control" name="status" required>
                        <option value="pending" <?php if ($status === 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="fulfilled" <?php if ($status === 'fulfilled') echo 'selected'; ?>>Fulfilled</option>
                        <option value="canceled" <?php if ($status === 'canceled') echo 'selected'; ?>>Canceled</option>
                    </select>
                </div>
                <button type="submit" name="save_order" class="btn btn-primary">Save Order</button>
                <a href="manage_orders.php" class="btn btn-secondary">Cancel</a>
            </form>
            <hr>
        <?php endif; ?>

        <!-- Order List -->
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Part Number</th>
                    <th>Quantity</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['part_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td>
                                <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                    <a href="manage_orders.php?edit=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <?php endif; ?>
                                <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                    <a href="manage_orders.php?delete=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this order?')">Deactivate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>