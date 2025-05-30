<?php
// stock_valuation_report.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Initialize variables
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$export = isset($_GET['export']);

// Get stock valuation data
try {
    $query = "SELECT 
                s.part_number,
                SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END) AS qty,
                SUM(CASE WHEN f.is_processed = 0 THEN f.quantity * f.cost ELSE 0 END) AS total_cost,
                CASE 
                    WHEN SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END) > 0 
                    THEN SUM(CASE WHEN f.is_processed = 0 THEN f.quantity * f.cost ELSE 0 END) / 
                         SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END)
                    ELSE 0
                END AS avg_cost
              FROM fifo_queue1 f
              JOIN stocks s ON f.part_id = s.id
              WHERE f.purchase_date <= ?
              GROUP BY s.part_number
              ORDER BY s.part_number";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$end_date]);
    $stock_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $total_qty = 0;
    $total_value = 0;
    foreach ($stock_data as $row) {
        $total_qty += $row['qty'];
        $total_value += $row['total_cost'];
    }

} catch (PDOException $e) {
    die("Error generating report: " . $e->getMessage());
}

// Handle Excel export
if ($export) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="stock_valuation_'.date('Ymd').'.xls"');
    
    echo "Stock Valuation Report for ".htmlspecialchars($start_date)." to ".htmlspecialchars($end_date)."\n\n";
    echo "Part No\tQuantity\tAvg Cost (Rs.)\tTotal Value (Rs.)\n";
    
    foreach ($stock_data as $row) {
        echo $row['part_number']."\t".
             $row['qty']."\t".
             number_format($row['avg_cost'], 2)."\t".
             number_format($row['total_cost'], 2)."\n";
    }
    
    echo "\nTotal\t".$total_qty."\t\t".number_format($total_value, 2)."\n";
    exit;
}
?>

<div class="container mt-4">
    <h2>Stock Valuation Report</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Report Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Generate Report</button>
                    <button type="button" class="btn btn-secondary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="submit" name="export" value="1" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Report Results -->
    <div class="card">
        <div class="card-header">
            <h3>Stock Valuation as of <?= htmlspecialchars($end_date) ?></h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Part No</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Avg Cost (Rs.)</th>
                            <th class="text-end">Total Value (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock_data)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No stock items found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stock_data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['part_number']) ?></td>
                                    <td class="text-end"><?= number_format($row['qty']) ?></td>
                                    <td class="text-end"><?= number_format($row['avg_cost'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['total_cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-dark">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong><?= number_format($total_qty) ?></strong></td>
                                <td></td>
                                <td class="text-end"><strong><?= number_format($total_value, 2) ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header, .card-body {
        break-inside: avoid;
    }
    .btn, .card-header h3 {
        display: none !important;
    }
    body {
        background: white;
        color: black;
        font-size: 12pt;
    }
    .table {
        width: 100%;
    }
    .table-dark {
        background-color: #343a40 !important;
        color: white !important;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6 !important;
    }
    h2 {
        page-break-before: always;
    }
}
</style>

<?php include('footer.php'); ?>