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
        <strong>Note:</strong> 
        <ul>
            <li><b>Current WAC</b>: Calculated using UNSOLD inventory only</li>
            <li><b>Historical WAC</b>: Calculated using ALL purchases of this item</li>
        </ul>
    </div>
    
    <!-- [Keep your existing filter form here - unchanged] -->
    
    <!-- Inventory Report Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>FIFO Inventory Status</h3>
            <?php if (!empty($part_id)): ?>
                <div class="text-end">
                    <div class="fw-bold">Current WAC: <?= number_format($averages['current']['current_wac'] ?? 0, 4) ?></div>
                    <div class="fw-bold">Historical WAC: <?= number_format($averages['historical']['historical_wac'] ?? 0, 4) ?></div>
                </div>
            <?php endif; ?>
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
                            <?php if (empty($part_id)): ?>
                                <th>Current WAC</th>
                                <th>Historical WAC</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventory)): ?>
                            <tr>
                                <td colspan="<?= empty($part_id) ? 10 : 8 ?>" class="text-center">No inventory items found</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $current_part = null;
                            $part_averages = [];
                            
                            if (empty($part_id)) {
                                // Pre-calculate all averages for performance
                                $stmt = $conn->query("
                                    SELECT part_id, 
                                        SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS historical_wac,
                                        SUM(CASE WHEN is_processed = 0 THEN quantity * cost ELSE 0 END) / 
                                        NULLIF(SUM(CASE WHEN is_processed = 0 THEN quantity ELSE 0 END), 0) AS current_wac
                                    FROM fifo_queue1
                                    GROUP BY part_id
                                ");
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $part_averages[$row['part_id']] = $row;
                                }
                            }
                            
                            foreach ($inventory as $item): 
                                if ($current_part !== $item['part_id']):
                                    $current_part = $item['part_id'];
                                    $avg_data = empty($part_id) ? 
                                        ($part_averages[$item['part_id']] ?? ['current_wac' => 0, 'historical_wac' => 0]) : 
                                        $averages;
                                endif;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['fifo_id']) ?></td>
                                    <td><?= htmlspecialchars($item['part_number']) ?></td>
                                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                                    <td><?= number_format($item['cost'], 2) ?></td>
                                    <td><?= htmlspecialchars($item['purchase_date']) ?></td>
                                    <td><?= htmlspecialchars($item['supplier_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($item['supplier_id']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_processed'] ? 'success' : 'warning' ?>">
                                            <?= $item['is_processed'] ? 'Sold' : 'Unsold' ?>
                                        </span>
                                    </td>
                                    <?php if (empty($part_id)): ?>
                                        <td><?= number_format($avg_data['current_wac'], 2) ?></td>
                                        <td><?= number_format($avg_data['historical_wac'], 2) ?></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<!-- Export Options -->
<div class="mt-3">
    <a href="export_fifo_report.php?<?= http_build_query($_GET) ?>" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Export to Excel
    </a>
</div>

<?php include('footer.php'); ?>