<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Get suppliers for dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);

// Get recent purchases for dropdown
$purchases = $conn->query("
    SELECT sp.id, sp.reference_no, s.supplier_name 
    FROM supplier_purchases sp
    JOIN suppliers s ON sp.supplier_id = s.id
    WHERE sp.flag = 'active'
    ORDER BY sp.purchase_date DESC LIMIT 100
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Insert return header
        $stmt = $conn->prepare("
            INSERT INTO supplier_returns 
            (supplier_purchase_id, return_date, reference_no, return_reason, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['supplier_purchase_id'],
            $_POST['return_date'],
            generateReturnReference(),
            $_POST['return_reason'],
            $_SESSION['user_id']
        ]);
        $returnId = $conn->lastInsertId();
        
        // Insert return items
        foreach ($_POST['items'] as $item) {
            $stmt = $conn->prepare("
                INSERT INTO supplier_return_items
                (return_id, part_id, quantity, cost, fifo_reference_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $returnId,
                $item['part_id'],
                $item['quantity'],
                $item['cost'],
                $item['fifo_id']
            ]);
        }
        
        $conn->commit();
        $_SESSION['success'] = "Return created successfully!";
        header("Location: supplier_return_list.php");
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error creating return: " . $e->getMessage();
    }
}

function generateReturnReference() {
    return "RET-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));
}
?>

<div class="container mt-4">
    <h2>Create Supplier Return</h2>
    
    <form method="post">

<!-- Here's the PHP implementation to replace the JavaScript dynamic item addition: -->
<?php
// Add this code in supplier_return_create.php after the form opening tag
if (isset($_GET['purchase_id'])) {
    $purchaseId = (int)$_GET['purchase_id'];
    $items = $conn->query("
        SELECT spd.part_id, s.part_number, s.description, 
               spd.quantity AS available_qty, spd.unit_price
        FROM supplier_purchase_details spd
        JOIN stocks s ON spd.part_id = s.id
        WHERE spd.purchase_id = $purchaseId
        AND s.flag = 'active'
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($items as $item) {
        echo '<tr>
            <td>'.$item['part_number'].'</td>
            <td>'.$item['description'].'</td>
            <td>'.$item['available_qty'].'</td>
            <td>
                <input type="number" name="items['.$item['part_id'].'][quantity]" 
                       class="form-control" min="1" max="'.$item['available_qty'].'">
                <input type="hidden" name="items['.$item['part_id'].'][cost]" 
                       value="'.$item['unit_price'].'">
                <input type="hidden" name="items['.$item['part_id'].'][part_id]" 
                       value="'.$item['part_id'].'">
            </td>
            <td>'.number_format($item['unit_price'], 2).'</td>
            <td><button type="button" class="btn btn-danger" onclick="this.closest(\'tr\').remove()">
                <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>';
    }
    exit;
}
?>

<!-- Modify your purchase select dropdown to trigger a form submit -->
<select name="supplier_purchase_id" class="form-control" required 
        onchange="this.form.action='supplier_return_create.php'; this.form.submit()">
    <option value="">Select Purchase</option>
    <?php foreach ($purchases as $purchase): ?>
        <option value="<?= $purchase['id'] ?>" 
            <?= isset($_GET['purchase_id']) && $_GET['purchase_id'] == $purchase['id'] ? 'selected' : '' ?>>
            <?= $purchase['reference_no'] ?> - <?= $purchase['supplier_name'] ?>
        </option>
    <?php endforeach; ?>
</select>

<!-- The items table will auto-populate via PHP -->
<table class="table" id="itemsTable">
    <thead>
        <tr>
            <th>Part No</th>
            <th>Description</th>
            <th>Available Qty</th>
            <th>Return Qty</th>
            <th>Unit Cost</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($items)): ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['part_number'] ?></td>
                    <td><?= $item['description'] ?></td>
                    <td><?= $item['available_qty'] ?></td>
                    <td>
                        <input type="number" name="items[<?= $item['part_id'] ?>][quantity]" 
                               class="form-control" min="1" max="<?= $item['available_qty'] ?>">
                        <input type="hidden" name="items[<?= $item['part_id'] ?>][cost]" 
                               value="<?= $item['unit_price'] ?>">
                        <input type="hidden" name="items[<?= $item['part_id'] ?>][part_id]" 
                               value="<?= $item['part_id'] ?>">
                    </td>
                    <td><?= number_format($item['unit_price'], 2) ?></td>
                    <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">
                        <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>




    <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Supplier Purchase</label>
                <select name="supplier_purchase_id" class="form-control" required>
                    <?php foreach ($purchases as $purchase): ?>
                        <option value="<?= $purchase['id'] ?>">
                            <?= $purchase['reference_no'] ?> - <?= $purchase['supplier_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control" 
                       value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Reason</label>
                <select name="return_reason" class="form-control" required>
                    <option value="Defective">Defective</option>
                    <option value="Wrong Item">Wrong Item</option>
                    <option value="Over Supply">Over Supply</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4>Return Items</h4>
            </div>
            <div class="card-body">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Part No</th>
                            <th>Description</th>
                            <th>Available Qty</th>
                            <th>Return Qty</th>
                            <th>Unit Cost</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
                <!-- <button type="button" class="btn btn-secondary" onclick="addItemRow()">
                    <i class="fas fa-plus"></i> Add Item
                </button> -->
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Return</button>
    </form>
</div>

<script>
// JavaScript for dynamic item addition
function addItemRowJs() {
    // Implement AJAX to fetch available items from the selected purchase
    // and populate the table row with selectable items
}
</script>

<?php include('footer.php'); ?>