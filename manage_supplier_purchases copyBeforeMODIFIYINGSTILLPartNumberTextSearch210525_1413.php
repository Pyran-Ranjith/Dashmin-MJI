<?php
// manage_supplier_purchases.php
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
$supplier_id = '';
$part_id = '';
$quantity = '';
$cost = '';
$purchase_date = '';
$id = '';
$search_term = $_GET['search_term'] ?? '';

// Fetch parts based on search term
try {
    $parts_query = "SELECT DISTINCT stocks.id as id, stocks.part_number as part_number FROM stocks
    -- LEFT JOIN stocks ON fifo_queue1.part_id = stocks.id
    ";
    $params = [];
    
    if (!empty($search_term)) {
        $parts_query .= " AND part_number LIKE ?";
        $params[] = '%' . $search_term . '%';
    }
    
    $parts_query .= " ORDER BY part_number";
    // $parts_query .= " ORDER BY part_id";
    
    $stmt = $conn->prepare($parts_query);
    $stmt->execute($params);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching parts: " . $e->getMessage();
}

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
                // Resftore the old stock quantity 
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ?,
                cost = ? WHERE id=?");
                $stmt->execute([$old_quantity, $cost, $old_part_id]);

                // Add the new stock quantity
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ?,
                cost = ? WHERE id=?");
                $stmt->execute([$quantity, $cost, $part_id]);
            } else {
                // Adjust the stock quantity for the same part
                $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ? - ?,
                cost = ? WHERE id=?");
                $stmt->execute([$quantity, $customers_result1, $old_quantity, $part_id]);
            }
        } else {
            // Create new supplier purchase entry
            $stmt = $conn->prepare("INSERT INTO supplier_purchases (supplier_id, part_id, quantity, cost, purchase_date, flag) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$supplier_id, $part_id, $quantity, $cost, $purchase_date]);

            // Increase the stock quantity
            $stmt = $conn->prepare("UPDATE stocks SET stock_quantity = stock_quantity + ?,
                cost = ? WHERE id=?");
            $stmt->execute([$quantity, $cost, $part_id]);

            // ===== FIFO IMPLEMENTATION =====
            $stmt = $conn->prepare("INSERT INTO fifo_queue1 
                                  (part_id, supplier_id, quantity, cost, purchase_date, item_data) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $item_data = "Purchase ID: $supplier_id | Part: $part_id | Qty: $quantity | Cost: $cost";
            $stmt->execute([$part_id, $supplier_id, $quantity, $cost, $purchase_date, $item_data]);
            // ===== END FIFO =====
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_supplier_purchases.php');
    exit;
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

            // ===== FIFO IMPLEMENTATION =====
            $stmt = $conn->prepare("UPDATE fifo_queue1 SET is_processed = 1 
                                  WHERE supplier_id = ?");
            $stmt->execute([$id]);
            // ===== END FIFO =====
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_supplier_purchases.php');
    exit;
}

// Fetch supplier purchases, parts (stocks), and suppliers (only active records)
try {
    $parts_query = "
        SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number 
        FROM supplier_purchases 
        JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
        JOIN stocks ON supplier_purchases.part_id = stocks.id
        WHERE supplier_purchases.flag = 'active'
         -- ORDER BY stocks.part_number
    ";

    $params = [];
    
    if (!empty($search_term)) {
        $parts_query .= " AND part_number LIKE ?";
        $params[] = '%' . $search_term . '%';
    }
    $parts_query .= " ORDER BY part_number";
    $purchases_result = $conn->query($parts_query);

    $purchases = $purchases_result->fetchAll(PDO::FETCH_ASSOC);

    $suppliers_result = $conn->query("SELECT * FROM suppliers");
    $suppliers = $suppliers_result->fetchAll(PDO::FETCH_ASSOC);

    $parts_result = $conn->query("SELECT * FROM stocks 
    WHERE flag = 'active'
    ORDER BY part_number
    ");
    $parts = $parts_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
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
        die("An error occurred: " . $e->getMessage());
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
                            SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number 
                            FROM supplier_purchases 
                            JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
                            JOIN stocks ON supplier_purchases.part_id = stocks.id 
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
                                    <p><strong>Cost:</strong> <?php echo htmlspecialchars($purchase['cost']); ?></p>
                                    <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars($purchase['purchase_date']); ?></p>
                                    <a href="manage_supplier_purchases.php" class="btn btn-secondary">Back to List</a>
                                </div>
                            </div>
                    <?php endif;
                    } catch (PDOException $e) {
                        die("An error occurred: " . $e->getMessage());
                    }
                    ?>
                <?php else: ?>
                    <!-- Form to add/update supplier purchase -->
                    <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                        <form method="POST" action="manage_supplier_purchases.php">
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
                <div class="col-md-6 mb-3">
                    <label class="form-label">Search Part Number</label>
                    <div class="input-group">
                        <input type="text" name="search_term" class="form-control" 
                               placeholder="Type to search part numbers..." 
                               value="<?= htmlspecialchars($search_term) ?>"
                               id="partSearch">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
                            <div class="form-group">
                                <label><strong>Part number</strong></label>
                                <select class="form-control" name="part_id" required>
                                    <option value="" disabled selected>Select a Part number</option>
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
                                <label><strong>Cost</strong></label>
                                <input type="number" class="form-control" name="cost" placeholder="Enter cost" value="<?php echo $cost; ?>" step="0.01" required>
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
                            <th>Cost</th>
                            <th>Purchase Date</th>
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
                                <td colspan="6" class="text-center">No supplier purchases found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>