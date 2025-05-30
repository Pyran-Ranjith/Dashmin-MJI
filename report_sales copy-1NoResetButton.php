<?php
ob_start();
include('db.php');
include('header.php');

// Initialize variables
$start_date = '';
$end_date = '';
$sales = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Fetch sales data
    $stmt = $conn->prepare("
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
        WHERE 
            sales.sale_date BETWEEN :start_date AND :end_date
        ORDER BY 
            sales.sale_date ASC
    ");
    $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
    <div class="container mt-4">
        <h2>Sales Report</h2>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>

        <?php if (!empty($sales)): ?>
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
            <div class="alert alert-info">No sales found for the selected date range.</div>
        <?php endif; ?>
    </div>

<?php
ob_end_flush();
?>

<?php include('footer.php'); ?>
