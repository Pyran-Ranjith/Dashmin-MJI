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
$status_filter = $_GET['status'] ?? 'all';

// Function to calculate weighted averages
function calculateAverages($conn, $part_id) {
    $results = [];
    
    // Current WAC (unsold only)
    $stmt = $conn->prepare("SELECT 
                            SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS current_wac,
                            SUM(quantity) AS current_qty
                           FROM fifo_queue1
                           WHERE part_id = ? 
                           AND is_processed = 0");
    $stmt->execute([$part_id]);
    $results['current'] = $stmt->fetch(PDO::FETCH_ASSOC);
    // Historical WAC (all items)
    $stmt = $conn->prepare("SELECT 
                            SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS historical_wac,
                            SUM(quantity) AS historical_qty
                           FROM fifo_queue1
                           WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $results['historical'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $results;
}

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
                sup.supplier_name,
                fq.supplier_id
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              LEFT JOIN suppliers sup ON fq.supplier_id = sup.id
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
    
    // Calculate averages if specific part selected
    $averages = [];
    if (!empty($part_id)) {
        $averages = calculateAverages($conn, $part_id);
    }
    
} catch (PDOException $e) {
    die("Error generating report: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>FIFO Inventory Report</h2>
    
    <div class="alert alert-info">
        <strong>Weighted Average Cost (WAC) Explanation:</strong> 
        <ul>
            <li><b>Current WAC</b>: Average cost of UNSOLD inventory only</li>
            <li><b>Historical WAC</b>: Average cost of ALL purchases (both sold and unsold)</li>
        </ul>
    </div>
    
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
                    <button type="submit" class="btn btn-primary mt-4">Apply Filters</button>
                    <a href="fifo_inventory_report.php" class="btn btn-secondary mt-4">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Inventory Report Table -->
    <div class="card">
        <div class="card-header">
            <h3>Inventory Details</h3>
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
                            <th>Status</th>
                            <th>Current WAC</th>
                            <th>Historical WAC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No inventory items found matching your criteria</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventory as $item): ?>
                                <tr>
                                    <td><?= $item['fifo_id'] ?></td>
                                    <td><?= htmlspecialchars($item['part_number']) ?></td>
                                    <td class="text-end"><?= number_format($item['quantity'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['cost'], 2) ?></td>
                                    <td><?= date('Y-m-d', strtotime($item['purchase_date'])) ?></td>
                                    <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_processed'] ? 'warning' : 'success' ?>">
                                            <?= $item['is_processed'] ? 'Sold' : 'Unsold' ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?= isset($averages['current']['current_wac']) ? number_format($averages['current']['current_wac'], 2) : 'N/A' ?></td>
                                    <!-- <td class="text-end"><?= $wac_current1 ?></td> -->
                                    <!-- <td class="text-end"><?= $averages['current']['current_wac'] ?></td> -->
                                    <td class="text-end"><?= isset($averages['historical']['historical_wac']) ? number_format($averages['historical']['historical_wac'], 2) : 'N/A' ?></td>
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