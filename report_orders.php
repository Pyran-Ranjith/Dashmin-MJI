<?php
ob_start();
include('db.php'); // Include your database connection file
include('header.php'); // Include your header file

// Initialize variables
$start_date = '';
$end_date = '';
$orders = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Reset the form (clear dates)
        $start_date = '';
        $end_date = '';
    } else {
        // Get the date filters
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
    }
}

// Fetch orders data based on filters
$sql = "
    SELECT 
        orders.id,
        customers.first_name,
        customers.last_name,
        stocks.part_number,
        stocks.description,
        orders.quantity,
        -- orders.total_price,
        orders.order_date,
        orders.status
    FROM 
        orders
    JOIN 
        customers ON orders.customer_id = customers.id
    JOIN 
        stocks ON orders.part_id = stocks.id
";

// Add date filters if provided
if (!empty($start_date) && !empty($end_date)) {
    // Ensure the dates are in the correct format (YYYY-MM-DD)
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date = date('Y-m-d', strtotime($end_date));

    // Debugging: Print the dates to ensure they are correct
    echo "<script>console.log('Start Date:', '$start_date');</script>";
    echo "<script>console.log('End Date:', '$end_date');</script>";

    // Add the date filter to the SQL query
    $sql .= " WHERE DATE(orders.order_date) BETWEEN :start_date AND :end_date";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
} else {
    // Fetch all records if no dates are selected
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Add CSS for print-friendly layout and filter area styling -->
<style>
/* Style for the filter area */
.filter-area {
    border: 2px solid #007bff; /* Blue border */
    border-radius: 10px; /* Rounded corners */
    padding: 20px; /* Add some padding */
    background-color: #f8f9fa; /* Light background color */
    margin-bottom: 20px; /* Space below the filter area */
}

/* Style for the filter buttons */
.filter-area .btn {
    margin-right: 10px; /* Space between buttons */
}
@media print {
    /* Hide the form, buttons, and other unnecessary elements */
    form, .btn, .alert {
        display: none;
    }

    /* Ensure the table takes full width and is properly styled */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    table, th, td {
        border: 1px solid black;
    }
    th, td {
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }

    /* Style for the date range section in print view */
    .print-date-range {
        display: block !important; /* Force display in print view */
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
    }
}

/* Hide the date range section on screen */
.print-date-range {
    display: none;
}
</style>

<div class="container mt-4">
    <h2>Orders Report</h2>
    <!-- Filter area with rounded border and colored frame -->
    <div class="filter-area">
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="d-flex gap-2">
            <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
            </div>
        </div>
    </form>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="mb-3">
            <button onclick="printReport()" class="btn btn-success">Print Report</button>
        </div>

        <!-- Date range section (visible only in print view) -->
        <div class="print-date-range">
            <?php
            if (!empty($start_date) && !empty($end_date)) {
                echo "Date Range: " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date);
            } else {
                echo "Date Range: All Records";
            }
            ?>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Part Number</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Order Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . htmlspecialchars($order['last_name'])); ?></td>
                        <td><?php echo htmlspecialchars($order['part_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['description']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($order['total_price']); ?></td> -->
                        <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No orders found.</div>
    <?php endif; ?>
</div>

<script>
function printReport() {
    window.print(); // Trigger the browser's print functionality
}
</script>

<?php
include('footer.php'); // Include your footer file
ob_end_flush();
?>