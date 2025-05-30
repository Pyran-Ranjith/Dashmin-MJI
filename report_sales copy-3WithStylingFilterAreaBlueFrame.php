<?php
ob_start();
include('db.php'); // Include your database connection file
include('header.php'); // Include your header file

// Initialize variables
$start_date = '';
$end_date = '';
$sales = [];

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

// Fetch sales data based on filters
$sql = "
    SELECT 
        sales.id AS sale_id,
        customers.first_name,
        customers.last_name,
        stocks.part_number,
        stocks.description,
        sales.quantity_sold,
        sales.total_price,
        sales.sale_date
    FROM 
        sales
    JOIN 
        customers ON sales.customer_id = customers.id
    JOIN 
        stocks ON sales.stock_id = stocks.id
";

// Add date filters if provided
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " WHERE sales.sale_date BETWEEN :start_date AND :end_date";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
} else {
    // Fetch all records if no dates are selected
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Add CSS for print-friendly layout and filter area styling -->
<style>
    /* Style for the filter area */
    .filter-area {
        border: 2px solid #007bff;
        /* Blue border */
        border-radius: 10px;
        /* Rounded corners */
        padding: 20px;
        /* Add some padding */
        background-color: #f8f9fa;
        /* Light background color */
        margin-bottom: 20px;
        /* Space below the filter area */
    }

    /* Style for the filter buttons */
    .filter-area .btn {
        margin-right: 10px;
        /* Space between buttons */
    }

    /* Additional margin for the buttons to avoid overlapping */
    .filter-area .col-md-2 {
        margin-top: 10px;
        /* Add margin above the buttons */
    }

    /* Print-specific styles */
    @media print {

        /* Hide the form, buttons, and other unnecessary elements */
        form,
        .btn,
        .alert,
        .filter-area {
            display: none;
        }

        /* Ensure the table takes full width and is properly styled */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Style for the date range section in print view */
        .print-date-range {
            display: block !important;
            /* Force display in print view */
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
    <h2>Sales Report</h2>

    <!-- Filter area with rounded border and colored frame -->
    <div class="filter-area">
        <h4>Filter Dates</h4> <!-- Heading for the filter area -->
        <form method="POST">
            <div class="row">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <!-- <div class="col-md-2 align-self-end">
                    <button type="submit" name="filter" class="btn btn-primary me-2">Filter</button>
                    <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
                </div> -->

                <div class="d-flex gap-2">
                    <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                    <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($sales)): ?>
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
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Part Number</th>
                    <th>Description</th>
                    <th>Quantity Sold</th>
                    <th>Total Price</th>
                    <th>Sale Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                        <td><?php echo htmlspecialchars($sale['first_name'] . ' ' . htmlspecialchars($sale['last_name'])); ?></td>
                        <td><?php echo htmlspecialchars($sale['part_number']); ?></td>
                        <td><?php echo htmlspecialchars($sale['description']); ?></td>
                        <td><?php echo htmlspecialchars($sale['quantity_sold']); ?></td>
                        <td><?php echo htmlspecialchars($sale['total_price']); ?></td>
                        <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No sales found.</div>
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