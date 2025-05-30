<?php
// fifo_inventory_report.php
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

// Enhanced function to calculate averages and margins
function calculateInventoryMetrics($conn, $part_id) {
    $results = [];
    
    // Current WAC and selling price (unsold only)
    $stmt = $conn->prepare("SELECT 
                            SUM(f.quantity * f.cost) / NULLIF(SUM(f.quantity), 0) AS current_wac,
                            SUM(f.quantity) AS current_qty,
                            MAX(s.selling_price) AS current_selling_price
                           FROM fifo_queue1 f
                           JOIN stocks s ON f.part_id = s.id
                           WHERE f.part_id = ? 
                           AND f.is_processed = 0");
    $stmt->execute([$part_id]);
    $results['current'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate current margin
    if ($results['current']['current_selling_price'] > 0 && $results['current']['current_wac'] > 0) {
        $results['current']['margin_percent'] = 
            ($results['current']['current_selling_price'] - $results['current']['current_wac']) / 
            $results['current']['current_selling_price'] * 100;
    } else {
        $results['current']['margin_percent'] = 0;
    }
    
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
    // Enhanced base query with selling price
    $query = "SELECT 
                fq.id AS fifo_id,
                fq.part_id,
                s.part_number,
                s.description,
                s.selling_price,
                fq.quantity,
                fq.cost,
                fq.purchase_date,
                fq.is_processed,
                sup.supplier_name,
                fq.supplier_id,
                r.location_code AS rack_location,
                l.location_name
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              LEFT JOIN suppliers sup ON fq.supplier_id = sup.id
              LEFT JOIN racks r ON fq.rack_id = r.id
              LEFT JOIN locations l ON r.location_id = l.id
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
    
    // Calculate metrics if specific part selected
    $metrics = [];
    if (!empty($part_id)) {
        $metrics = calculateInventoryMetrics($conn, $part_id);
    }
    
    // Calculate summary totals
    $summary = [
        'total_cost_value' => 0,
        'total_retail_value' => 0,
        'total_items' => 0,
        'total_quantity' => 0
    ];
    
    foreach ($inventory as $item) {
        if (!$item['is_processed']) {
            $summary['total_cost_value'] += $item['quantity'] * $item['cost'];
            $summary['total_retail_value'] += $item['quantity'] * $item['selling_price'];
            $summary['total_quantity'] += $item['quantity'];
        }
        $summary['total_items']++;
    }
    
} catch (PDOException $e) {
    die("Error generating report: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>FIFO Inventory Valuation Report</h2>
    
    <!-- Summary Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3>Inventory Summary</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Total Items</h5>
                            <p class="display-6"><?= number_format($summary['total_items']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Total Quantity</h5>
                            <p class="display-6"><?= number_format($summary['total_quantity']) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Cost Value</h5>
                            <p class="display-6">$<?= number_format($summary['total_cost_value'], 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5>Retail Value</h5>
                            <p class="display-6">$<?= number_format($summary['total_retail_value'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($part_id) && isset($metrics['current'])): ?>
            <div class="mt-4 alert alert-info">
                <h5>Part-Specific Metrics</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Current WAC:</strong> $<?= number_format($metrics['current']['current_wac'], 2) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Current Selling Price:</strong> $<?= number_format($metrics['current']['current_selling_price'], 2) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Gross Margin:</strong> <?= number_format($metrics['current']['margin_percent'], 2) ?>%</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
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
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Sell Price</th>
                            <th>Margin</th>
                            <th>Purchase Date</th>
                            <th>Supplier</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory)): ?>
                            <tr>
                                <td colspan="11" class="text-center">No inventory items found matching your criteria</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventory as $item): ?>
                                <?php 
                                    $margin = ($item['selling_price'] > 0 && $item['cost'] > 0) ? 
                                        (($item['selling_price'] - $item['cost']) / $item['selling_price'] * 100) : 0;
                                    $row_class = $item['is_processed'] ? 'table-secondary' : '';
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td><?= $item['fifo_id'] ?></td>
                                    <td><?= htmlspecialchars($item['part_number']) ?></td>
                                    <td><?= htmlspecialchars($item['description']) ?></td>
                                    <td class="text-end"><?= number_format($item['quantity']) ?></td>
                                    <td class="text-end">$<?= number_format($item['cost'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($item['selling_price'], 2) ?></td>
                                    <td class="text-end <?= $margin >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($margin, 2) ?>%
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($item['purchase_date'])) ?></td>
                                    <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?= htmlspecialchars($item['location_name'] ?? 'N/A') ?>
                                        <?= $item['rack_location'] ? ' ('.$item['rack_location'].')' : '' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_processed'] ? 'warning' : 'success' ?>">
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