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
$wac_data = null;
$error = null;
$search_term = $_GET['search_term'] ?? '';

// Fetch parts based on search term
try {
    $parts_query = "SELECT DISTINCT stocks.id as id, part_id, stocks.part_number as part_number FROM fifo_queue1
    LEFT JOIN stocks ON fifo_queue1.part_id = stocks.id
    ";
    $params = [];
    
    if (!empty($search_term)) {
        $parts_query .= " AND part_number LIKE ?";
        $params[] = '%' . $search_term . '%';
    }
    
    // $parts_query .= " ORDER BY part_number";
    $parts_query .= " ORDER BY part_id";
    
    $stmt = $conn->prepare($parts_query);
    $stmt->execute($params);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching parts: " . $e->getMessage();
}

// Calculate WAC if part is selected
if ($part_id) {
    try {
        // Current WAC (unsold only)
        $stmt = $conn->prepare("SELECT 
                stocks.part_number,
                COALESCE(SUM(fifo_queue1.quantity * fifo_queue1.cost), 0) AS total_cost,
                COALESCE(SUM(fifo_queue1.quantity), 0) AS total_quantity,
                CASE WHEN SUM(fifo_queue1.quantity) > 0 
                     THEN SUM(fifo_queue1.quantity * fifo_queue1.cost) / SUM(fifo_queue1.quantity) 
                     ELSE NULL 
                END AS wac
            FROM fifo_queue1
            JOIN stocks ON fifo_queue1.part_id = stocks.id
            WHERE fifo_queue1.part_id = ? AND fifo_queue1.is_processed = 0");
        $stmt->execute([$part_id]);
        $wac_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no unsold items, get historical WAC
        if ($wac_data['wac'] === null) {
            $stmt = $conn->prepare("SELECT 
                    stocks.part_number,
                    COALESCE(SUM(fifo_queue1.quantity * fifo_queue1.cost), 0) AS total_cost,
                    COALESCE(SUM(fifo_queue1.quantity), 0) AS total_quantity,
                    CASE WHEN SUM(fifo_queue1.quantity) > 0 
                         THEN SUM(fifo_queue1.quantity * fifo_queue1.cost) / SUM(fifo_queue1.quantity) 
                         ELSE NULL 
                    END AS wac
                FROM fifo_queue1
                JOIN stocks ON fifo_queue1.part_id = stocks.id
                WHERE fifo_queue1.part_id = ?");
            $stmt->execute([$part_id]);
            $wac_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $wac_data['wac_type'] = 'Historical';
        } else {
            $wac_data['wac_type'] = 'Current';
        }

    } catch (PDOException $e) {
        $error = "Error calculating WAC: " . $e->getMessage();
    }
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
                        Get WAC
                    </button>
                    <a href="fifo_part_number_wac_Inquiry.php" class="btn btn-secondary">Reset</a>
                    <a href="fifo_inventory_report.php" class="btn btn-warning">FIFO Inventory Report</a>
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
        <div class="card">
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
                            <!-- <td class="text-end"> -->
                            <td>
                                <?= $wac_data['wac'] !== null ? 'Rs. ' . number_format($wac_data['wac'], 2) : 'N/A' ?>
                            </td>
                            <!-- <td class="text-end"><?= number_format($wac_data['total_quantity']) ?></td> -->
                            <td><?= number_format($wac_data['total_quantity']) ?></td>
                            <!-- <td class="text-end">Rs. <?= number_format($wac_data['total_cost'], 2) ?></td> -->
                            <td>Rs. <?= number_format($wac_data['total_cost'], 2) ?></td>
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
    
    // Enable/disable Get WAC button based on selection
    partSelect.addEventListener('change', function() {
        document.querySelector('button[name="get_wac"]').disabled = !this.value;
    });
});
</script>

<?php include('footer.php'); ?>