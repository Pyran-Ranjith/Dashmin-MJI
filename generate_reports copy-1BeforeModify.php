<?php
ob_start();
include 'header.php';
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

?>

<div class="container mt-5">
    <h1>Generate Reports</h1>

    <!-- Report Type Selection Form -->
    <form method="GET" action="generate_reports.php" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <label for="report_type">Select Report Type</label>
                <select id="report_type" name="report_type" class="form-control">
                    <option value="" disabled selected>Select Report Type</option>
                    <option value="sales" <?php if ($report_type == 'sales') echo 'selected'; ?>>Sales Report</option>
                    <option value="stock" <?php if ($report_type == 'stock') echo 'selected'; ?>>Stock Report</option>
                    <option value="supplier" <?php if ($report_type == 'supplier') echo 'selected'; ?>>Supplier Report</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date">Start Date</label>
                <!-- <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>"> -->
                <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Generate Report</button>
                <a href="generate_reports.php" class="btn btn-secondary">Refresh</a>
                <!-- <button type="submit" class="btn btn-primary">Reset</button> -->
            </div>
        </div>
    </form>

    <!-- Display Report Data -->
    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($report_type)): ?>
                <h3><?php echo ucfirst($report_type); ?> Report</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <?php if ($report_type == 'sales'): 
                                $colcount = 5-1;
                                $recs = $sales->getSalesSpare_partsFilltered($start_date, $end_date);
                                ?>
                                <th>Sale ID</th>
                                <th>Part Number</th>
                                <th>Quantity Sold</th>
                                <th>Sale Price</th>
                                <th>Sale Date</th>
                            <?php elseif ($report_type == 'stock'): 
                                $colcount = 8-1;
                                $recs = $stocks->getStocksFilltered($start_date, $end_date);
                                ?>
                                <th>Part ID</th>
                                <th>Part Number</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Date</th>
                            <?php elseif ($report_type == 'supplier'): 
                                $colcount = 5-1;
                                $recs = $supplier->getSupplierPurchasesFilltered($start_date, $end_date);
                                ?>
                                <th>Supplier ID</th>
                                <th>Supplier Name</th>
                                <th>Part Supplied</th>
                                <th>Quantity</th>
                                <!-- <th>Total Cost</th> -->
                                <th>Date Supplied</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        // SQL query based on report type
                        if ($report_type == 'sales') {
                            // $query = "
                            //     -- SELECT s.id as sale_id, p.part_number, s.quantity, s.sale_price, s.sale_date
                            //     *
                            // FROM sales s
                            //           JOIN parts p ON s.part_id = p.id
                            //           WHERE 1=1";

                            // // Apply date filters if provided
                            // if (!empty($start_date)) {
                            //     $query .= " AND s.sale_date >= '$start_date'";
                            // }
                            // if (!empty($end_date)) {
                            //     $query .= " AND s.sale_date <= '$end_date'";
                            // }

                            // Fetch required sales
                            // $sales = $sales->getSalesSpare_partsFilltered($start_date, $end_date);
                        } elseif ($report_type == 'stock') {
                            // $query = "
                            //     -- SELECT p.id as part_id, p.part_number, p.description, p.category, p.quantity, p.cost_price, p.selling_price
                            //     *
                            // FROM parts p
                            //           WHERE 1=1";

                        } elseif ($report_type == 'supplier') {
                            // $query = "
                            //     -- SELECT sp.id as supplier_id, sp.supplier_name, p.part_number, sp.quantity_supplied, sp.total_cost, sp.date_supplied
                            //     *
                            //           FROM supplier_purchases sp
                            //           JOIN parts p ON sp.part_id = p.id
                            //           WHERE 1=1";

                            // Apply date filters if provided
                            // if (!empty($start_date)) {
                            //     $query .= " AND sp.date_supplied >= '$start_date'";
                            // }
                            // if (!empty($end_date)) {
                            //     $query .= " AND sp.date_supplied <= '$end_date'";
                            // }
                        }

                        // Execute the query and display results
                        // $stmt = $conn->query($query);
                        // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        //     $sales = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
                        //     echo "<tr>";
                        //     foreach ($row as $col) {
                        //         echo "<td>" . htmlspecialchars($col) . "</td>";
                        //     }
        
                        //     echo "</tr>";
                        // }


                        // $sales = $sales->getSalesSpare_partsFilltered($start_date, $end_date);
                        // // Execute the query and display results
                        // $stmt = $conn->query($query);
                        // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        //     $sales = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
                        //     echo "<tr>";
                        //     foreach ($row as $col) {
                        //         echo "<td>" . htmlspecialchars($col) . "</td>";
                        //     }
                        //     foreach ($sales as $item):
        
                        //     echo "</tr>";
                        // }


// ?>
<?php foreach ($recs as $item): 
    ?>
                <tr>
                <?php for ($i=0; $i <=  $colcount; $i++) { ?>
                    <td><?= $item[$i] ?></td>
                <?php }; ?>
                    <!-- <td>  -->
                        <?php //if ($item['image']): ?>
                        <!-- <?php //else: ?> -->
                            <!-- No Image -->
                        <!-- <?php //endif; ?> -->
                    <!-- </td> -->
                </tr>
            <?php endforeach; ?>

                    </tbody>
                </table>
            <?php else: ?>
                <p>Please select a report type and date range to generate the report.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Print Button -->
<button onclick="printReport()" class="btn btn-primary">Print Report</button>

<script>
function printReport() {
    var printContents = document.getElementById('invoice-area').innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
