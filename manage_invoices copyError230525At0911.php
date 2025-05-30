<?php
ob_start();
include('db.php');
include('header.php');

// Handle Create Invoice action
if (isset($_POST['save_invoice'])) {
    $customer_id = $_POST['customer_id'];
    $issue_date = $_POST['issue_date'];
    $sales_ids = $_POST['sales_ids'];
    $total_price = 0;

    // Create the invoice
    $sql = "INSERT INTO invoices (invoice_number, customer_id, issue_date, total_price)
            VALUES (CONCAT('INV-', FLOOR(RAND() * 10000)), '$customer_id', '$issue_date', '0')";
    $conn->query($sql);
    $invoice_id = $conn->lastInsertId();

    // Insert invoice items
    foreach ($sales_ids as $sale_id) {
        $sale_result = $conn->query("
        SELECT * FROM sales 
        LEFT JOIN stocks st ON sales.stock_id = st.id
        WHERE id = $sale_id AND flag = 'active'
        ");
        $sale = $sale_result->fetch(PDO::FETCH_ASSOC);
        $part_number = $sale['part_number'];
        $quantity = $sale['quantity_sold'];
        // $unit_price = $sale['total_price'] / $sale['quantity_sold'];
        $unit_price = $sale['selling_price'] ;
        $item_total_price = $sale['selling_price'] * $quantity;

        $sql = "INSERT INTO invoice_items (invoice_id, sale_id, quantity, unit_price, total_price, part_number)
                VALUES ('$invoice_id', '$sale_id', '$quantity', '$unit_price', '$item_total_price', $part_number)";
        $conn->query($sql);

        $total_price += $item_total_price;
    }

    // Update the total price for the invoice
    $sql = "UPDATE invoices SET total_price = '$total_price' WHERE id = '$invoice_id' AND flag = 'active'";
    $conn->query($sql);
}

// Fetch invoices and customers
$invoices_result = $conn->query("
SELECT invoices.*, customers.first_name, customers.last_name 
FROM invoices 
JOIN customers ON invoices.customer_id = customers.id
WHERE invoices.flag = 'active' 
");

$customers_result = $conn->query("SELECT * FROM customers WHERE flag = 'active'");

// Fetch sales for the customer to create an invoice
// $sales_result = $conn->query("SELECT * FROM sales WHERE flag = 'active'");
// Fetch sales records
$sales_result = $conn->query("SELECT sa.id, cu.first_name as first_name, cu.last_name, 
    st.part_number, st.description, 
    sa.quantity_sold, sa.total_price, sa.selling_price, sa.sale_date
    FROM sales sa  
    JOIN customers cu ON sa.customer_id = cu.id 
    JOIN stocks st ON sa.stock_id = st.id");
// $sales_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Invoices</h2>
        <!-- Manage Invoices -->

    </div>
    <!-- <div> -->
    <div class="card-body">
        <!-- Form to create an invoice -->
        <form method="POST" action="manage_invoices.php">
            <div class="form-group">
                <label><strong>Customer</strong></label>
                <select class="form-control" name="customer_id" required>
                    <option value="" disabled selected>Select a Customer</option>
                    <?php while ($customer = $customers_result->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $customer['id']; ?>"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label><strong>Issue Date</strong></label>
                <input type="date" class="form-control" name="issue_date" placeholder="Enter Issue Date" required>
            </div>
            <div class="form-group">
                <label><strong>Select Sales</strong></label>

                <select class="form-control" name="sales_ids[]" multiple required>
                    <option value="" disabled selected>Select a Sale</option>
                    <?php while ($sale = $sales_result->fetch(PDO::FETCH_ASSOC)) { ?>
                        <noscript>
                            <option value="
                        <?php echo $sale['id']; ?>">
                                Sale ID: <?php echo $sale['id']; ?>
                                - Selling Price: <?php echo $sale['selling_price']; ?>
                                - Total price: <?php echo $sale['selling_price'] * $sale['quantity_sold']; ?>
                            </option>
                        </noscript>

                        <option value="
                        <?php echo $sale['id']; ?>">
                            Name: <?php echo $sale['first_name']; ?>
                            <!-- <?php echo $sale['last_name']; ?> -->
                            - Part : <?php echo $sale['part_number']; ?>
                            <!-- Description: <?php echo $sale['description']; ?> -->
                            - Quantity: <?php echo $sale['quantity_sold']; ?>
                            - Selling Price: <?php echo $sale['selling_price']; ?>
                            <!-- - Cost of Sale: <?php echo $sale['total_price']; ?> -->
                            - Date: <?php echo $sale['sale_date']; ?>
                            <!-- - Total: <?php echo $sale['total_price']; ?> -->
                        </option>

                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="save_invoice" class="btn btn-primary">Create Invoice</button>
        </form>

        <hr>

        <!-- Invoices List -->
        <table class=" table-striped table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Invoice Id</th>
                    <th>Invoice Number</th>
                    <th>Customer</th>
                    <th>Issue Date</th>
                    <th>Total Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($invoice = $invoices_result->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?php echo $invoice['id']; ?></td>
                        <td><?php echo $invoice['invoice_number']; ?></td>
                        <td><?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?></td>
                        <td><?php echo $invoice['issue_date']; ?></td>
                        <td><?php echo $invoice['total_price']; ?></td>
                        <td>
                            <a href="view_invoice.php?invoice_id=<?php echo $invoice['id']; ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<!-- </div>
<div> -->

<?php
include('footer.php');
?>