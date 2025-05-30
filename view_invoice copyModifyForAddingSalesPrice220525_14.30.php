<?php
ob_start();
include('db.php');
include('header.php');

if (isset($_GET['invoice_id'])) {
    $invoice_id = $_GET['invoice_id'];

    // Fetch invoice details
    $invoice_result = $conn->query("
    SELECT invoices.*, cu.first_name, cu.last_name, cu.email, cu.address 
        FROM invoices 
        JOIN customers cu ON invoices.customer_id = cu.id 
        WHERE invoices.id = '$invoice_id' AND invoices.flag = 'active'
    ");
    // $invoice = $invoice_result->fetch(PDO::FETCH_ASSOC);

    // Fetch invoice items without foreign key constraint
    $items_result = $conn->query("SELECT invoice_items.*, stocks.part_number, stocks.description 
                FROM invoice_items 
                LEFT JOIN stocks ON invoice_items.stock_id = stocks.id 
                WHERE invoice_items.invoice_id = '$invoice_id' AND invoice_items.flag = 'active'");
}

if (isset($_GET['invoice_item_id'])) {
    $invoice_item_id = $_GET['invoice_id'];

    // Fetch invoice details
    $invoice_result = $conn->query("SELECT invoices.*, customers.first_name, customers.last_name, customers.email, customers.address 
        FROM invoices 
        JOIN customers ON invoices.customer_id = customers.id 
        WHERE invoices.id = '$invoice_item_id' AND invoices.flag = 'active'");
}
?>

<!-- Invoice Details -->
<div id="invoice-area">
 <?php 
    // $invoice = $invoice_result->fetch(PDO::FETCH_ASSOC);
    while ($invoice = $invoice_result->fetch(PDO::FETCH_ASSOC)) { 
 ?>

<div class="card">
    <div class="card-header">
        <!-- <h2>Manage Sales</h2> -->
        <!-- <h2>Invoice #<?php echo $invoice['id']; ?></h2>  -->
        <h2>Invoice <?php echo $invoice['invoice_number']; ?></h2>
    </div>
    <div class="card-body">
            <p><strong>Date: </strong><?php echo $invoice['issue_date']; ?></p>
            <p><strong>Customer: </strong><?php echo $invoice['first_name'] . ' ' . $invoice['last_name']; ?></p>
            <p><strong>Contact: </strong><?php echo $invoice['email']; ?></p>
            <p><strong>Address: </strong><?php echo $invoice['address']; ?></p>
            <!-- </nosript> -->
        <?php } ?> 

            <hr>

            <!-- Invoice Items -->
            <h3>Invoice Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Part Number</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    while ($item = $items_result->fetch(PDO::FETCH_ASSOC)) { 
                        $total_price = $item['quantity'] * $item['unit_price'];
                        $grand_total += $total_price;
                    ?>
                    <tr>
                        <td><?php echo $item['part_number']; ?></td>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['unit_price']; ?></td>
                        <td><?php echo $total_price; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Grand Total -->
            <h4>Grand Total: <?php echo $grand_total; ?></h4>
        </div>

    </div>
<!-- /div> -->
        <!-- Print Button -->
        <button onclick="printInvoice()" class="btn btn-primary">Print Invoice</button>

        <script>
            function printInvoice() {
                var printContents = document.getElementById('invoice-area').innerHTML;
                var originalContents = document.body.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
            }
        </script>

<?php
include('footer.php');
?>
