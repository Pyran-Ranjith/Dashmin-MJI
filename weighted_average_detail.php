<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$part_id = $_GET['part_id'] ?? 0;

include('db.php');
include('header.php');

// Get part info
try {
    $stmt = $conn->prepare("SELECT part_number, description FROM stocks WHERE id = ?");
    $stmt->execute([$part_id]);
    $part = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$part) {
        die("Part not found");
    }
    
    // Get all FIFO entries for this part
    $stmt = $conn->prepare("
        SELECT fq.id, fq.quantity, fq.cost, fq.purchase_date, 
               sp.id AS purchase_id, sup.supplier_name
        FROM fifo_queue fq
        LEFT JOIN supplier_purchases sp ON fq.purchase_id = sp.id
        LEFT JOIN suppliers sup ON sp.supplier_id = sup.id
        WHERE fq.part_id = ? AND fq.is_processed = 0
        ORDER BY fq.purchase_date
    ");
    $stmt->execute([$part_id]);
    $fifo_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate weighted average
    $total_cost = 0;
    $total_quantity = 0;
    foreach ($fifo_entries as $entry) {
        $total_cost += $entry['quantity'] * $entry['cost'];
        $total_quantity += $entry['quantity'];
    }
    $weighted_avg = $total_quantity > 0 ? $total_cost / $total_quantity : 0;
    
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>Weighted Average Cost Calculation</h2>
    <h3><?= htmlspecialchars($part['part_number']) ?> - <?= htmlspecialchars($part['description']) ?></h3>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Calculation Summary</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Total Available Quantity:</th>
                            <td><?= number_format($total_quantity, 2) ?></td>
                        </tr>
                        <tr>
                            <th>Total Cost Value:</th>
                            <td><?= number_format($total_cost, 2) ?></td>
                        </tr>
                        <tr class="table-primary">
                            <th>Weighted Average Cost:</th>
                            <td><?= number_format($weighted_avg, 4) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Included Inventory Batches</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Purchase Date</th>
                                    <th>Supplier</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost</th>
                                    <th>Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fifo_entries as $entry): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($entry['purchase_date']) ?></td>
                                        <td><?= htmlspecialchars($entry['supplier_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format($entry['quantity'], 2) ?></td>
                                        <td><?= number_format($entry['cost'], 4) ?></td>
                                        <td><?= number_format($entry['quantity'] * $entry['cost'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="weighted_average_report.php" class="btn btn-secondary">Back to Report</a>
    </div>
</div>

<?php include('footer.php'); ?>