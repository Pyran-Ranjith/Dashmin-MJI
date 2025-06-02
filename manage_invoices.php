<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
        WHERE id = '$sale_id' AND flag = 'active'
        ");
        $sale = $sale_result->fetch(PDO::FETCH_ASSOC);

        $quantity = $sale['quantity_sold'];
        $unit_price = $sale['selling_price'];
        $item_total_price = $sale['selling_price'] * $sale['quantity_sold'];
        $stock_id = $sale['stock_id'];
        $unit_price1 = $sale['total_price'];

        $sql = "INSERT INTO invoice_items (invoice_id, sale_id, quantity, unit_price, total_price, stock_id)
                VALUES ('$invoice_id', '$sale_id', '$quantity', '$unit_price1', '$item_total_price', '$stock_id')";
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

$customers_result = $conn->query("SELECT * FROM customers 
WHERE flag = 'active'
ORDER BY customers.first_name
");

// Get selected customer ID from POST or default to empty
$selected_customer = $_POST['customer_id'] ?? '';
?>

<div class="card">
    <div class="card-header">
        <h2>Manage Invoices</h2>
    </div>
    <div class="card-body">
        <!-- Form to create an invoice -->
        <form method="POST" action="manage_invoices.php" id="invoiceForm">
            <div class="form-group">
                <label><strong>Customer</strong></label>
                <select class="form-control" name="customer_id" id="customerSelect" required onchange="filterSales()">
                    <option value="" disabled selected>Select a Customer</option>
                    <?php 
                    $customers_result->execute();
                    while ($customer = $customers_result->fetch(PDO::FETCH_ASSOC)) { 
                    ?>
                        <option value="<?= $customer['id'] ?>" <?= $selected_customer == $customer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label><strong>Issue Date</strong></label>
                <input type="date" class="form-control" name="issue_date" placeholder="Enter Issue Date" required>
            </div>
            <div class="form-group">
                <label><strong>Select Sales</strong></label>
                <select class="form-control" name="sales_ids[]" id="salesSelect" multiple required>
                    <option value="" disabled selected>Select a Sale</option>
                    <?php 
                    $sales_query = "SELECT sa.id, cu.first_name, cu.last_name, 
                                  st.part_number, st.description, 
                                  sa.quantity_sold, sa.total_price, sa.sale_date, sa.selling_price
                                  FROM sales sa  
                                  JOIN customers cu ON sa.customer_id = cu.id 
                                  JOIN stocks st ON sa.stock_id = st.id
                                  WHERE sa.flag = 'active'";
                    
                    if (!empty($selected_customer)) {
                        $sales_query .= " AND sa.customer_id = :customer_id";
                        $stmt = $conn->prepare($sales_query);
                        $stmt->bindParam(':customer_id', $selected_customer);
                        $stmt->execute();
                    } else {
                        $stmt = $conn->query($sales_query);
                    }
                    
                    while ($sale = $stmt->fetch(PDO::FETCH_ASSOC)) { 
                    ?>
                        <option value="<?= $sale['id'] ?>">
                            Name: <?= htmlspecialchars($sale['first_name']) ?>
                            - Part: <?= htmlspecialchars($sale['part_number']) ?>
                            - Quantity: <?= htmlspecialchars($sale['quantity_sold']) ?>
                            - Unit Cost: <?= htmlspecialchars($sale['total_price']) ?>
                            - Selling Price: <?= htmlspecialchars($sale['selling_price']) ?>
                            - Date: <?= htmlspecialchars($sale['sale_date']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="save_invoice" class="btn btn-primary">Create Invoice</button>
        </form>

        <hr>

        <!-- Invoices List -->
        <table class="table table-striped table-bordered">
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
                <?php 
                $invoices_result->execute();
                while ($invoice = $invoices_result->fetch(PDO::FETCH_ASSOC)) { 
                ?>
                    <tr>
                        <td><?= htmlspecialchars($invoice['id']) ?></td>
                        <td><?= htmlspecialchars($invoice['invoice_number']) ?></td>
                        <td><?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?></td>
                        <td><?= htmlspecialchars($invoice['issue_date']) ?></td>
                        <td><?= number_format($invoice['total_price'], 2) ?></td>
                        <td>
                            <a href="view_invoice.php?invoice_id=<?= $invoice['id'] ?>" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterSales() {
    // Submit the form when customer selection changes
    document.getElementById('invoiceForm').submit();
}
</script>

<?php
include('footer.php');
?>