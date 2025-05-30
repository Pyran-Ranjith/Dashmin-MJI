<?php
// inquiry_sales_wac.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Initialize variables
$part_id = $_GET['part_id'] ?? null;
$search_term = $_GET['search_term'] ?? '';
$wac_data = null;
$fifo_transactions = null;
$stock_valuation = null;
$error = null;

// Fetch parts based on search term
try {
    $parts_query = "SELECT id, part_number FROM stocks WHERE flag = 'active'";
    $params = [];
    
    if (!empty($search_term)) {
        $parts_query .= " AND part_number LIKE ?";
        $params[] = '%' . $search_term . '%';
    }
    
    $parts_query .= " ORDER BY part_number";
    
    $stmt = $conn->prepare($parts_query);
    $stmt->execute($params);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching parts: " . $e->getMessage();
}

// Calculate WAC and get transaction history if part is selected
if ($part_id) {
    try {
        // Get part details
        $stmt = $conn->prepare("SELECT part_number FROM stocks WHERE id = ?");
        $stmt->execute([$part_id]);
        $part_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Current WAC (unsold only)
        $stmt = $conn->prepare("SELECT 
                COALESCE(SUM(fifo_queue1.quantity * fifo_queue1.cost), 0) AS total_cost,
                COALESCE(SUM(fifo_queue1.quantity), 0) AS total_quantity,
                CASE WHEN SUM(fifo_queue1.quantity) > 0 
                     THEN SUM(fifo_queue1.quantity * fifo_queue1.cost) / SUM(fifo_queue1.quantity) 
                     ELSE NULL 
                END AS wac
            FROM fifo_queue1
            WHERE part_id = ? AND is_processed = 0");
        $stmt->execute([$part_id]);
        $wac_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $wac_data['part_number'] = $part_info['part_number'];

        // If no unsold items, get historical WAC
        if ($wac_data['wac'] === null) {
            $stmt = $conn->prepare("SELECT 
                    COALESCE(SUM(quantity * cost), 0) AS total_cost,
                    COALESCE(SUM(quantity), 0) AS total_quantity,
                    CASE WHEN SUM(quantity) > 0 
                         THEN SUM(quantity * cost) / SUM(quantity) 
                         ELSE NULL 
                    END AS wac
                FROM fifo_queue1
                WHERE part_id = ?");
            $stmt->execute([$part_id]);
            $wac_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $wac_data['part_number'] = $part_info['part_number'];
            $wac_data['wac_type'] = 'Historical';
        } else {
            $wac_data['wac_type'] = 'Current';
        }

        // Get FIFO transaction history
        $stmt = $conn->prepare("SELECT 
                purchase_date AS date,
                part_id,
                quantity AS qty,
                cost,
                'Purchase' AS type,
                is_processed
            FROM fifo_queue1
            WHERE part_id = ?
            UNION ALL
            SELECT 
                sales.sale_date AS date,
                sales.stock_id AS part_id,
                sales.quantity_sold AS qty,
                sales.total_price / sales.quantity_sold AS cost,
                'Sale' AS type,
                1 AS is_processed
            FROM sales
            JOIN fifo_queue1 ON sales.stock_id = fifo_queue1.part_id
            WHERE sales.stock_id = ?
            ORDER BY date, type DESC");
        $stmt->execute([$part_id, $part_id]);
        $fifo_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate current stock in hand
        $stmt = $conn->prepare("SELECT 
                part_id,
                SUM(CASE WHEN is_processed = 0 THEN quantity ELSE 0 END) AS qty,
                SUM(CASE WHEN is_processed = 0 THEN quantity * cost ELSE 0 END) AS total_cost
            FROM fifo_queue1
            WHERE part_id = ?
            GROUP BY part_id");
        $stmt->execute([$part_id]);
        $stock_valuation = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error = "Error calculating WAC: " . $e->getMessage();
    }
}

// Handle Excel export
if (isset($_GET['export']) && $part_id) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="wac_report_' . $part_id . '_' . date('Ymd') . '.xls"');
    
    // Simple Excel content
    echo "Part Number\tWAC Type\tWeighted Average Cost\tTotal Quantity\tTotal Value\n";
    echo $wac_data['part_number'] . "\t" . 
         $wac_data['wac_type'] . "\t" . 
         ($wac_data['wac'] !== null ? number_format($wac_data['wac'], 2) : 'N/A') . "\t" . 
         number_format($wac_data['total_quantity']) . "\t" . 
         number_format($wac_data['total_cost'], 2) . "\n\n";
    
    echo "FIFO Transaction History\n";
    echo "Date\tType\tQuantity\tUnit Cost\tProcessed\n";
    foreach ($fifo_transactions as $transaction) {
        echo $transaction['date'] . "\t" . 
             $transaction['type'] . "\t" . 
             $transaction['qty'] . "\t" . 
             number_format($transaction['cost'], 2) . "\t" . 
             ($transaction['is_processed'] ? 'Yes' : 'No') . "\n";
    }
    
    echo "\nCurrent Stock Valuation\n";
    echo "Part Number\tQuantity\tTotal Value\n";
    echo $wac_data['part_number'] . "\t" . 
         ($stock_valuation['qty'] ?? 0) . "\t" . 
         number_format($stock_valuation['total_cost'] ?? 0, 2) . "\n";
    exit;
}
?>

<div class="container mt-4">
    <h2>Part Number WAC Inquiry</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Search Part Number</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row" id="searchForm">
                <input type="hidden" name="export" id="exportFlag" value="">
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Search Part Number</label>
                    <div class="input-group">
                        <input type="text" name="search_term" class="form-control" 
                               placeholder="Type to search part numbers..." 
                               value="<?= htmlspecialchars($search_term) ?>"
                               id="partSearch">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Select Part Number</label>
                    <select name="part_id" class="form-select" required id="partSelect">
                        <option value="" selected disabled>Select from filtered results</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?= $part['id'] ?>" <?= $part['id'] == $part_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($part['part_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <button type="submit" name="get_wac" class="btn btn-success" <?= empty($part_id) ? 'disabled' : '' ?>>
                        Get WAC Report
                    </button>
                    <button type="button" class="btn btn-info" onclick="window.print()" <?= empty($part_id) ? 'disabled' : '' ?>>
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="exportToExcel()" <?= empty($part_id) ? 'disabled' : '' ?>>
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </button>
                    <a href="inquiry_sales_wac.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if ($wac_data): ?>
        <!-- WAC Results -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>WAC Information</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Part Number</th>
                            <th>WAC Type</th>
                            <th>Weighted Average Cost</th>
                            <th>Total Quantity</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($wac_data['part_number']) ?></td>
                            <td><?= $wac_data['wac_type'] ?></td>
                            <td class="text-end">
                                <?= $wac_data['wac'] !== null ? 'Rs. ' . number_format($wac_data['wac'], 2) : 'N/A' ?>
                            </td>
                            <td class="text-end"><?= number_format($wac_data['total_quantity']) ?></td>
                            <td class="text-end">Rs. <?= number_format($wac_data['total_cost'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- FIFO Transaction History -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>FIFO Transaction History</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Part No</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Cost (Rs.)</th>
                                <th class="text-end">Total (Rs.)</th>
                                <th>Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fifo_transactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['date']) ?></td>
                                    <td><?= htmlspecialchars($transaction['type']) ?></td>
                                    <td><?= htmlspecialchars($wac_data['part_number']) ?></td>
                                    <td class="text-end"><?= number_format($transaction['qty']) ?></td>
                                    <td class="text-end"><?= number_format($transaction['cost'], 2) ?></td>
                                    <td class="text-end"><?= number_format($transaction['qty'] * $transaction['cost'], 2) ?></td>
                                    <td><?= $transaction['is_processed'] ? 'Yes' : 'No' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Current Stock Valuation -->
        <div class="card">
            <div class="card-header">
                <h3>Current Stock Valuation</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Part Number</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Total Value (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($wac_data['part_number']) ?></td>
                            <td class="text-end"><?= number_format($stock_valuation['qty'] ?? 0) ?></td>
                            <td class="text-end"><?= number_format($stock_valuation['total_cost'] ?? 0, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif ($part_id): ?>
        <div class="alert alert-warning">
            No WAC data found for the selected part number.
        </div>
    <?php endif; ?>
</div>

<!-- Add this script before the footer -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const partSearch = document.getElementById('partSearch');
    const partSelect = document.getElementById('partSelect');
    const searchForm = document.getElementById('searchForm');
    
    // Auto-submit search when typing stops (500ms delay)
    let typingTimer;
    partSearch.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            searchForm.submit();
        }, 500);
    });
    
    // Enable/disable buttons based on selection
    partSelect.addEventListener('change', function() {
        const buttons = document.querySelectorAll('button[name="get_wac"], .btn-info, .btn-secondary');
        buttons.forEach(btn => {
            btn.disabled = !this.value;
        });
    });
});

function exportToExcel() {
    document.getElementById('exportFlag').value = '1';
    document.getElementById('searchForm').submit();
    document.getElementById('exportFlag').value = '';
}
</script>

<style>
@media print {
    .card-header, .card-body {
        break-inside: avoid;
    }
    .btn {
        display: none !important;
    }
    body {
        background: white;
        color: black;
    }
    .table-dark {
        background-color: #343a40 !important;
        color: white !important;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6 !important;
    }
}
</style>

<?php include('footer.php'); ?>