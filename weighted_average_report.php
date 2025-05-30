<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Get all parts with current inventory
try {
    $parts = $conn->query("
        SELECT s.id, s.part_number, s.description, 
               COALESCE(SUM(fq.quantity), 0) AS current_stock
        FROM stocks s
        LEFT JOIN fifo_queue fq ON s.id = fq.part_id AND fq.is_processed = 0
        GROUP BY s.id, s.part_number, s.description
        HAVING current_stock > 0
        ORDER BY s.part_number
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching parts: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>Weighted Average Cost Report</h2>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Current Inventory Valuation</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Part Number</th>
                            <th>Description</th>
                            <th>Current Stock</th>
                            <th>Weighted Avg Cost</th>
                            <th>Total Value</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($parts as $part): ?>
                            <?php 
                            $avg_cost = getWeightedAverageCost($conn, $part['id']);
                            $total_value = $avg_cost['avg_cost'] * $part['current_stock'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($part['part_number']) ?></td>
                                <td><?= htmlspecialchars($part['description']) ?></td>
                                <td><?= number_format($part['current_stock'], 2) ?></td>
                                <td><?= number_format($avg_cost['avg_cost'], 4) ?></td>
                                <td><?= number_format($total_value, 2) ?></td>
                                <td>
                                    <a href="weighted_average_detail.php?part_id=<?= $part['id'] ?>" 
                                       class="btn btn-sm btn-info">
                                        View Calculation
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>