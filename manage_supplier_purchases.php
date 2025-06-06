<?php
// manage_supplier_purchases.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Handle success messages
if (isset($_GET['success'])) {
    echo "<script>
        alert('".($_GET['success'] == 'create' ? 
             'Purchase successfully created!' : 
             'Purchase successfully updated!')."');
    </script>";
}
$crud_permissions = $_SESSION['crud_permissions'];

ob_start();
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$supplier_id = '';
$part_id = '';
$quantity = '';
$cost = '';
$purchase_date = '';
$id = '';

// Initialize $purchases as an empty array
$purchases = [];

// Handle Create/Update actions
if (isset($_POST['save_purchase'])) {
    $supplier_id = intval($_POST['supplier_id']);
    $part_id = intval($_POST['part_id']);
    $quantity = intval($_POST['quantity']);
    $cost = floatval($_POST['cost']);
    $purchase_date = $_POST['purchase_date'];

    try {
        $conn->beginTransaction();

        if ($_POST['id']) {
            // Update existing supplier purchase
            $id = intval($_POST['id']);

            // Fetch the old quantity and part ID
            $stmt = $conn->prepare("SELECT quantity, part_id FROM supplier_purchases WHERE id=?");
            $stmt->execute([$id]);
            $old_purchase = $stmt->fetch(PDO::FETCH_ASSOC);
            $old_quantity = $old_purchase['quantity'];
            $old_part_id = $old_purchase['part_id'];

            // Update the purchase
            $stmt = $conn->prepare("UPDATE supplier_purchases SET supplier_id=?, part_id=?, quantity=?, cost=?, purchase_date=? WHERE id=?");
            $stmt->execute([$supplier_id, $part_id, $quantity, $cost, $purchase_date, $id]);

            // Adjust the stock quantity
            if ($old_part_id != $part_id) {
                // Restore the old stock quantity
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
                $stmt->execute([$old_quantity, $old_part_id]);

                // Add the new stock quantity
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
                $stmt->execute([$quantity, $part_id]);
            } else {
                // Adjust the stock quantity for the same part
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? - ? WHERE id=?");
                $stmt->execute([$quantity, $old_quantity, $part_id]);
            }
            
            header('Location: manage_supplier_purchases.php?success=update');
            exit;
        } else {
            // Create new supplier purchase entry
            $stmt = $conn->prepare("INSERT INTO supplier_purchases (supplier_id, part_id, quantity, cost, purchase_date, flag) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$supplier_id, $part_id, $quantity, $cost, $purchase_date]);

            // Increase the stock quantity
            $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? WHERE id=?");
            $stmt->execute([$quantity, $part_id]);

            // FIFO IMPLEMENTATION
            $stmt = $conn->prepare("INSERT INTO fifo_queue1 
                                  (part_id, supplier_id, quantity, cost, purchase_date, item_data) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $item_data = "Purchase ID: $supplier_id | Part: $part_id | Qty: $quantity | Cost: $cost";
            $stmt->execute([$part_id, $supplier_id, $quantity, $cost, $purchase_date, $item_data]);
            
            header('Location: manage_supplier_purchases.php?success=create');
            exit;
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('An error occurred: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        $conn->beginTransaction();

        // Fetch the quantity and part ID
        $stmt = $conn->prepare("SELECT quantity, part_id FROM supplier_purchases WHERE id=?");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($purchase) {
            // Set the purchase flag to inactive
            $stmt = $conn->prepare("UPDATE supplier_purchases SET flag = 'inactive' WHERE id=?");
            $stmt->execute([$id]);

            // Decrease the stock quantity
            $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
            $stmt->execute([$purchase['quantity'], $purchase['part_id']]);

            // FIFO IMPLEMENTATION
            $stmt = $conn->prepare("UPDATE fifo_queue1 SET is_processed = 1 WHERE supplier_id = ?");
            $stmt->execute([$id]);
        }

        $conn->commit();
        echo "<script>alert('Purchase successfully deleted!');</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('An error occurred: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Fetch supplier purchases, parts (stocks), and suppliers (only active records)
try {
    $purchases_result = $conn->query("
        SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number, stocks.rack_id, 
        racks.location_code 
        FROM supplier_purchases 
        JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
        JOIN stocks ON supplier_purchases.part_id = stocks.id
        LEFT JOIN racks ON stocks.rack_id = racks.id
        WHERE supplier_purchases.flag = 'active'
    ");
    $purchases = $purchases_result->fetchAll(PDO::FETCH_ASSOC);

    $suppliers_result = $conn->query("SELECT * FROM suppliers");
    $suppliers = $suppliers_result->fetchAll(PDO::FETCH_ASSOC);

    $parts_result = $conn->query("SELECT * FROM stocks WHERE flag = 'active'");
    $parts = $parts_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "');</script>";
}

// Pre-fill form if editing a purchase
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM supplier_purchases WHERE id=? AND flag = 'active'");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($purchase) {
            $supplier_id = $purchase['supplier_id'];
            $part_id = $purchase['part_id'];
            $quantity = $purchase['quantity'];
            $cost = $purchase['cost'];
            $purchase_date = $purchase['purchase_date'];
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error loading purchase: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Manage Supplier Purchases</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
                <!-- <h2>Manage Supplier Purchases</h2> -->
            </div>

            <div class="card-body">
                <!-- Handle View action -->
                <?php if (isset($_GET['view'])): ?>
                    <?php
                    $id = intval($_GET['view']);
                    try {
                        $stmt = $conn->prepare("
                            SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number, 
                            stocks.rack_id, racks.location_code AS location_code
                            FROM supplier_purchases 
                            JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
                            JOIN stocks ON supplier_purchases.part_id = stocks.id 
                            LEFT JOIN racks ON stocks.rack_id = racks.id
                            WHERE supplier_purchases.id=? AND supplier_purchases.flag = 'active'
                        ");
                        $stmt->execute([$id]);
                        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($purchase): ?>
                            <hr>
                            <h3>Supplier Purchase Details</h3>
                            <div class="card">
                                <div class="card-header">
                                    Supplier Purchase Details (View Only)
                                </div>
                                <div class="card-body">
                                    <p><strong>Supplier:</strong> <?php echo htmlspecialchars($purchase['supplier_name']); ?></p>
                                    <p><strong>Part Number:</strong> <?php echo htmlspecialchars($purchase['part_number']); ?></p>
                                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($purchase['quantity']); ?></p>
                                    <p><strong>Unit Cost:</strong> <?php echo htmlspecialchars($purchase['cost']); ?></p>
                                    <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($purchase['purchase_date']); ?></p>
                                    <p><strong>Racks Possition:</strong> <?php echo htmlspecialchars($purchase['location_code']); ?></p>
                                    <a href="manage_supplier_purchases.php" class="btn btn-secondary">Back to List</a>
                                </div>
                            </div>
                    <?php endif;
                    } catch (PDOException $e) {
                        echo "<script>alert('Error viewing purchase: " . addslashes($e->getMessage()) . "');</script>";
                    }
                    ?>
                <?php else: ?>
                    <!-- Form to add/update supplier purchase -->
                    <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                        <form method="POST" action="manage_supplier_purchases.php" onsubmit="return confirm('Are you sure you want to save this purchase?');">
                            <h3>Supplier Purchases</h3>
                            <div class="card">
                                <div class="card-header">
                                    Supplier Purchases (Add/Update)
                                </div>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="form-group">
                                <label><strong>Supplier</strong></label>
                                <select class="form-control" name="supplier_id" required>
                                    <option value="" disabled selected>Select a Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>" <?php if ($supplier['id'] == $supplier_id) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong>Part Number</strong></label>
                                <select class="form-control" name="part_id" required>
                                    <option value="" disabled selected>Select a Part Number</option>
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
                                <label><strong>Unit Cost</strong></label>
                                <input type="number" class="form-control" name="cost" placeholder="Enter unit cost" value="<?php echo $cost; ?>" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label><strong>Purchase Date</strong></label>
                                <input type="date" class="form-control" name="purchase_date" value="<?php echo $purchase_date; ?>" required>
                            </div>
                            <button type="submit" name="save_purchase" class="btn btn-primary">Save Purchase</button>
                            <a href="manage_supplier_purchases.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <hr>

                <!-- Supplier Purchase List -->
                <table class=" table-striped table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Supplier</th>
                            <th>Part Number</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Purchase Date</th>
                            <th>Rack Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($purchases)): ?>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['part_number']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['cost']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                                    <td><?= htmlspecialchars($purchase['location_code']) ?></td>
                                    <td>
                                        <a href="manage_supplier_purchases.php?view=<?php echo $purchase['id']; ?>" class="btn btn-info btn-sm">View</a>
                                        <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                            <a href="manage_supplier_purchases.php?edit=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <?php endif; ?>
                                        <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                            <a href="manage_supplier_purchases.php?delete=<?php echo $purchase['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this purchase?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No supplier purchases found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>