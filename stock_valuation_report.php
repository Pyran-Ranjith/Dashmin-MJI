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

// Get stock valuation data
try {
    $query = "SELECT 
                s.part_number,
                s.description,
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
              GROUP BY s.part_number, s.description
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
?>

<div class="container mt-4">
    <h2>Stock Valuation Report</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4 no-print">
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
                    <a href="export_stock_valuation_report.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
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
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Cost (Rs.)</th>
                            <th class="text-end">Total Value (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stock_data)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No stock items found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stock_data as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['part_number']) ?></td>
                                    <td><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                    <td class="text-end"><?= number_format($row['qty']) ?></td>
                                    <td class="text-end"><?= number_format($row['avg_cost'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['total_cost'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-dark">
                                <td colspan="2"><strong>Total</strong></td>
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
    .no-print {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
        font-size: 12pt;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 5px !important;
    }
    .table-dark {
        background-color: #000 !important;
        color: white !important;
    }
    .text-end {
        text-align: right !important;
    }
    h2, h3 {
        color: black !important;
        page-break-after: avoid;
    }
}
</style>

<script>
// Ensure proper printing
function beforePrint() {
    document.body.style.zoom = "100%";
}
window.onbeforeprint = beforePrint;
</script>

<?php include('footer.php'); ?>