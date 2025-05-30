<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Get filter parameters
$part_id = $_GET['part_id'] ?? '';
$show_zero_qty = $_GET['show_zero_qty'] ?? 0;
$status_filter = $_GET['status'] ?? 'all'; // all, sold, unsold

try {
    // Base query
    $query = "SELECT 
                fq.id AS fifo_id,
                fq.part_id,
                s.part_number,
                fq.quantity,
                fq.cost,
                fq.purchase_date,
                fq.is_processed,
                sp.id AS supplier_id,
                sup.supplier_name
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              LEFT JOIN supplier_purchases sp ON fq.supplier_id = sp.id
              LEFT JOIN suppliers sup ON sp.supplier_id = sup.id
              WHERE 1=1";
    
    $params = [];
    
    // Apply filters
    if (!empty($part_id)) {
        $query .= " AND fq.part_id = ?";
        $params[] = $part_id;
    }
    
    if (!$show_zero_qty) {
        $query .= " AND fq.quantity > 0";
    }
    
    if ($status_filter !== 'all') {
        $query .= " AND fq.is_processed = ?";
        $params[] = ($status_filter === 'sold') ? 1 : 0;
    }
    
    $query .= " ORDER BY fq.part_id, fq.purchase_date ASC";
    
    // Execute query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all parts for filter dropdown
    $parts = $conn->query("SELECT id, part_number FROM stocks ORDER BY part_number")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error generating report: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>FIFO Inventory Report</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Report Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Part Number</label>
                    <select name="part_id" class="form-select">
                        <option value="">All Parts</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?= $part['id'] ?>" <?= $part['id'] == $part_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($part['part_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="sold" <?= $status_filter == 'sold' ? 'selected' : '' ?>>Sold</option>
                        <option value="unsold" <?= $status_filter == 'unsold' ? 'selected' : '' ?>>Unsold</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="form-check mt-4 pt-2">
                        <input class="form-check-input" type="checkbox" name="show_zero_qty" id="show_zero_qty" value="1" <?= $show_zero_qty ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_zero_qty">
                            Show Zero Quantity Items
                        </label>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <button type="submit" class="btn btn-primary mt-4">Generate Report</button>
                    <a href="fifo_inventory_report.php" class="btn btn-secondary mt-4">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Inventory Report Table -->
    <div class="card">
        <div class="card-header">
            <h3>FIFO Inventory Status</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>FIFO ID</th>
                            <th>Part Number</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Purchase Date</th>
                            <th>Supplier</th>
                            <th>Purchase ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No inventory items found matching your criteria</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventory as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['fifo_id']) ?></td>
                                    <td><?= htmlspecialchars($item['part_number']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td><?= htmlspecialchars(number_format($item['cost'], 2)) ?></td>
                                    <td><?= htmlspecialchars($item['purchase_date']) ?></td>
                                    <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['supplier_id']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_processed'] ? 'success' : 'warning' ?>">
                                            <?= $item['is_processed'] ? 'Sold' : 'Unsold' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Export Options -->
            <div class="mt-3">
                <a href="export_fifo_report.php?<?= http_build_query($_GET) ?>" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>