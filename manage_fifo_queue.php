<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Initialize variables
$search_part_id = $_GET['part_id'] ?? '';
$show_processed = $_GET['show_processed'] ?? 0;

// Handle bulk operations
if (isset($_POST['bulk_action'])) {
    try {
        $conn->beginTransaction();
        
        $action = $_POST['bulk_action'];
        $selected_ids = $_POST['selected_ids'] ?? [];
        
        if (!empty($selected_ids)) {
            $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
            
            switch ($action) {
                case 'mark_processed':
                    $stmt = $conn->prepare("UPDATE fifo_queue1 SET is_processed = 1 WHERE id IN ($placeholders)");
                    break;
                case 'mark_unprocessed':
                    $stmt = $conn->prepare("UPDATE fifo_queue1 SET is_processed = 0 WHERE id IN ($placeholders)");
                    break;
                case 'delete':
                    $stmt = $conn->prepare("DELETE FROM fifo_queue1 WHERE id IN ($placeholders)");
                    break;
            }
            
            $stmt->execute($selected_ids);
        }
        
        $conn->commit();
        header("Location: manage_fifo_queue.php?" . http_build_query($_GET));
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error performing bulk action: " . $e->getMessage();
    }
}

// Fetch FIFO queue items
try {
    $query = "SELECT fq.*, s.part_number 
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($search_part_id)) {
        $query .= " AND fq.part_id = ?";
        $params[] = $search_part_id;
    }
    
    if (!$show_processed) {
        $query .= " AND fq.is_processed = 0";
    }
    
    $query .= " ORDER BY fq.is_processed ASC, fq.id ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $fifo_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch all parts for dropdown
    $parts = $conn->query("SELECT id, part_number FROM stocks ORDER BY part_number")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching FIFO queue: " . $e->getMessage();
    $fifo_items = [];
    $parts = [];
}
?>

<div class="container mt-4">
    <h2>FIFO Queue Management</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part Number</label>
                    <select name="part_id" class="form-select">
                        <option value="">All Parts</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?= $part['id'] ?>" <?= $part['id'] == $search_part_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($part['part_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4 pt-2">
                        <input class="form-check-input" type="checkbox" name="show_processed" id="show_processed" value="1" <?= $show_processed ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_processed">
                            Show Processed Items
                        </label>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary mt-4">Apply Filters</button>
                    <a href="manage_fifo_queue.php" class="btn btn-secondary mt-4">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bulk Actions Form -->
    <form method="post" id="bulkForm">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>FIFO Queue Items</h3>
                <div class="d-flex">
                    <select name="bulk_action" class="form-select me-2" style="width: auto;">
                        <option value="">Bulk Actions</option>
                        <option value="mark_processed">Mark as Processed</option>
                        <option value="mark_unprocessed">Mark as Unprocessed</option>
                        <option value="delete">Delete</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Part Number</th>
                                <th>Supplier Id</th>
                                <th>Quantity</th>
                                <th>Cost</th>
                                <th>Purchase Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fifo_items)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No FIFO items found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fifo_items as $item): ?>
                                    <tr class="<?= $item['is_processed'] ? 'table-secondary' : '' ?>">
                                        <td><input type="checkbox" name="selected_ids[]" value="<?= $item['id'] ?>"></td>
                                        <td><?= htmlspecialchars($item['id']) ?></td>
                                        <td><?= htmlspecialchars($item['part_number'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($item['supplier_id']) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td><?= htmlspecialchars(number_format($item['cost'], 2)) ?></td>
                                        <td><?= htmlspecialchars($item['purchase_date']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $item['is_processed'] ? 'success' : 'warning' ?>">
                                                <?= $item['is_processed'] ? 'Processed' : 'Available' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($item['is_processed']): ?>
                                                    <a href="?action=unprocess&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning" title="Mark as available">
                                                        <i class="fas fa-undo"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?action=process&id=<?= $item['id'] ?>" class="btn btn-sm btn-success" title="Mark as processed">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Select all checkboxes
document.getElementById('selectAll').addEventListener('click', function(e) {
    const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});
</script>

<?php include('footer.php'); ?>