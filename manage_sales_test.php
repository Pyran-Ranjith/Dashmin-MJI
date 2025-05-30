<?php
ob_start();
include('db.php');
include('header.php');

$sql = "
SELECT *
SELECT customers.first_name, customers.last_name, stocks.part_number, stocks.description, stocks.id, sales.id, sales.total_price 
FROM sales  
JOIN customers ON sales.customer_id = customers.id 
JOIN stocks ON sales.stock_id = stocks.id 
";

$sales_result = $conn->query($sql);
// Handle Create/Update/Delete actions for sales
if (isset($_POST['save_sale'])) { //user pressed 'Save Sale' button
 $customer_id = $_POST['customer_id'];
 $quantity_sold = $_POST['quantity_sold'];
 $sale_date = $_POST['sale_date'];

 if ($_POST['id']) {
  // Update existing sale
  $id = $_POST['id'];
  $sql = "UPDATE sales SET customer_id='$customer_id', stock_id='$stock_id', quantity_sold='$quantity_sold', total_price='$total_price', sale_date='$sale_date' WHERE id='$id'";
 } else {
  // Create new sale entry
  // $sql = "INSERT INTO sales (customer_id, stock_id, quantity_sold, total_price, sale_date)
  //         VALUES ('$customer_id', '$stock_id', '$quantity_sold', '$total_price', '$sale_date')";
  $sql = "INSERT INTO sales (quantity_sold, sale_date)
  VALUES ('$quantity_sold', '$sale_date')";
}
 $conn->query($sql);
}
?>

<h2>Manage Sales-Test</h2>
<form method="POST" action="manage_sales_test.php">
 <input type="hidden" name="id" id="sale_id" value="<?php echo isset($view_sale['id']) ? $view_sale['id'] : ''; ?>">

 <div class="form-group">
    <label><strong>Customer</strong></label>
    <select class="form-control" name="customer_id" id="customer_id" required>
        <?php while ($customer = $customers_result->fetch(PDO::FETCH_ASSOC)) { ?>
            <option value="<?php echo $customer['id']; ?>" <?php echo isset($view_sale['customer_id']) && $view_sale['customer_id'] == $customer['id'] ? 'selected' : ''; ?>>
                <?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?>
            </option>
        <?php } ?>
    </select>
</div>

 <div class="form-group">
  <label><strong>Sale Date</strong></label>
  <input type="date" class="form-control" name="sale_date" id="sale_date" value="<?php echo isset($view_sale['sale_date']) ? $view_sale['sale_date'] : ''; ?>" required>
 </div>
 <div class="form-group">
  <label><strong>Quantity sold</strong></label>
  <input type="number" class="form-control" name="quantity_sold" id="quantity_sold" value="<?php //echo isset($view_sale['quantity_sold']) ? $view_sale['quantity_sold'] : ''; 
                                                                                           ?>" required>
 </div>
 <button type="submit" name="save_sale" class="btn btn-primary">Save Sale</button>
 <a href="manage_sales_test.php" class="btn btn-secondary">Cancel</a>
</form>

<!-- Sales List -->
<table class="table">
 <thead>
  <tr>
   <th>Sales Id</th>
   <th>Sale Date</th>
   <th>Quantity Sold</th>
   <th>Actions</th>
  </tr>
 </thead>
 <tbody>
  <?php while ($sale = $sales_result->fetch(PDO::FETCH_ASSOC)) { ?>
   <tr>
    <td><?php echo $sale['id']; ?></td>
    <td><?php echo $sale['sale_date']; ?></td>
    <td><?php echo $sale['quantity_sold']; ?></td>
    <td>
     <a href="manage_sales_test.php?view=<?php echo $sale['id']; ?>" class="btn btn-info btn-sm">View</a>
     <a href="manage_sales_test.php?edit=<?php echo $sale['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
     <a href="manage_sales_test.php?delete=<?php echo $sale['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
     <!-- New Invoice Button -->
     <!-- <a href="generate_invoice.php?sale_id=<?php echo $sale['id']; ?>" class="btn btn-warning btn-sm">Generate Invoice</a> -->
    </td>
   </tr>
  <?php } ?>
 </tbody>
</table>