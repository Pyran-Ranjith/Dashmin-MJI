<?php
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

// Handle Create/Update actions
if (isset($_POST['save_purchase'])) {
    $supplier_id = $_POST['supplier_id'];
    $part_id = $_POST['part_id'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];
    $purchase_date = $_POST['purchase_date'];

    if ($_POST['id']) {
        // Update existing supplier purchase
        $id = $_POST['id'];
        $sql = "UPDATE supplier_purchases 
                SET supplier_id='$supplier_id', part_id='$part_id', quantity='$quantity', cost='$cost', purchase_date='$purchase_date' 
                WHERE id='$id'";
    } else {
        // Create new supplier purchase entry
        $sql = "INSERT INTO supplier_purchases (supplier_id, part_id, quantity, cost, purchase_date)
                VALUES ('$supplier_id', '$part_id', '$quantity', '$cost', '$purchase_date')";
    }
    $conn->query($sql);
    header('Location: manage_supplier_purchases.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM supplier_purchases WHERE id='$id'";
    $conn->query($sql);
    header('Location: manage_supplier_purchases.php');
    exit;
}

// Fetch supplier purchases, parts (stocks), and suppliers
$purchases_result = $conn->query("
    SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number 
    FROM supplier_purchases 
    JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
    JOIN stocks ON supplier_purchases.part_id = stocks.id
    ");

$suppliers_result = $conn->query("SELECT * FROM suppliers");
$parts_result = $conn->query("SELECT * FROM stocks");

// Pre-fill form if editing a purchase
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $purchase_result = $conn->query("SELECT * FROM supplier_purchases WHERE id='$id'");
    $purchase = $purchase_result->fetch(PDO::FETCH_ASSOC);

    if ($purchase) {
        $supplier_id = $purchase['supplier_id'];
        $part_id = $purchase['part_id'];
        $quantity = $purchase['quantity'];
        $cost = $purchase['cost'];
        $purchase_date = $purchase['purchase_date'];
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
                <!-- <h2>Manage Supplier Purchases</h2> -->

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $purchase_result = $conn->query("
                        SELECT supplier_purchases.*, suppliers.supplier_name, stocks.part_number 
                        FROM supplier_purchases 
                        JOIN suppliers ON supplier_purchases.supplier_id = suppliers.id 
                        JOIN stocks ON supplier_purchases.part_id = stocks.id 
                        WHERE supplier_purchases.id='$id'
                    ");
                    $purchase = $purchase_result->fetch(PDO::FETCH_ASSOC);

                    if ($purchase) {
                        // View selected purchase details in a card
                        ?>
                        <hr>
                        <h3>Supplier Purchase Details</h3>
                        <div class="card">
                        <div class="card-header">
                        Supplier Purchase Details (View Only)
                        </div>
                            <div class="card-body">
                                <p><strong>Supplier:</strong> <?php echo $purchase['supplier_name']; ?></p>
                                <p><strong>Part Number:</strong> <?php echo $purchase['part_number']; ?></p>
                                <p><strong>Quantity:</strong> <?php echo $purchase['quantity']; ?></p>
                                <p><strong>Cost:</strong> <?php echo $purchase['cost']; ?></p>
                                <p><strong>Purchase Date:</strong> <?php echo $purchase['purchase_date']; ?></p>
                                <a href="manage_supplier_purchases.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                ?>

                <!-- Form to add/update supplier purchase -->
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
                        <?php while ($supplier = $suppliers_result->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $supplier['id']; ?>" <?php if ($supplier['id'] == $supplier_id) echo 'selected'; ?>>
                                    <?php echo $supplier['supplier_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><strong>Part</strong></label>
                        <select class="form-control" name="part_id" required>
                        <option value="" disabled selected>Select a Part</option>
                        <?php while ($part = $parts_result->fetch(PDO::FETCH_ASSOC)) { ?>
                                <option value="<?php echo $part['id']; ?>" <?php if ($part['id'] == $part_id) echo 'selected'; ?>>
                                    <?php echo $part['part_number']; ?>
                                </option>
                            <?php } ?>
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
                <?php } ?>

                <hr>

                <!-- Supplier Purchase List -->
                <table class="table">
                    <thead>
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
                        <?php while ($purchase = $purchases_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $purchase['supplier_name']; ?></td>
                                <td><?php echo $purchase['part_number']; ?></td>
                                <td><?php echo $purchase['quantity']; ?></td>
                                <td><?php echo $purchase['cost']; ?></td>
                                <td><?php echo $purchase['purchase_date']; ?></td>
                                <td>
                                    <a href="manage_supplier_purchases.php?view=<?php echo $purchase['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="manage_supplier_purchases.php?edit=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_supplier_purchases.php?delete=<?php echo $purchase['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>
<?php include('footer.php'); ?>
