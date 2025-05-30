<?php
session_start();
ob_start();
include('db.php');
include('header.php');

$crud_permissions = $_SESSION['crud_permissions'];

// Initialize empty variables to hold form data for pre-fill when editing
$supplier_name = '';
$contact_info = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_supplier'])) {
  $supplier_name = $_POST['supplier_name'];
  $contact_info = $_POST['contact_info'];

  if ($_POST['id']) {
    // Update existing supplier
    $id = $_POST['id'];
    $sql = "UPDATE suppliers 
          SET supplier_name='$supplier_name', contact_info='$contact_info' 
          WHERE id='$id'";
  } else {
    // Create new supplier entry
    $sql = "
  INSERT INTO suppliers (supplier_name, contact_info)
  VALUES ('$supplier_name', '$contact_info')
          ";
  }
  $conn->query($sql);
  header('Location: manage_suppliers.php');
  exit;
}

if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  //  $sql = "-- DELETE FROM suppliers WHERE id='$id'";
  $sql = "UPDATE suppliers 
 SET flag='inactive' 
 WHERE id='$id'";

  $conn->query($sql);
  header('Location: manage_suppliers.php');
  exit;
}

// Fetch supplier for list bottom
$list_suppliers_result = $conn->query("
SELECT suppliers.*
 FROM suppliers
 WHERE flag = 'active' 
 ");

// Pre-fill form if editing a suppliers
if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $suppliers_result = $conn->query("SELECT * FROM suppliers WHERE id='$id' AND flag = 'active'");
  $supplier = $suppliers_result->fetch(PDO::FETCH_ASSOC);

  if ($supplier) {
    $supplier_name = $supplier['supplier_name'];
    $contact_info = $supplier['contact_info'];
  }
}

?>

<h2>Manage Suppliers</h2>

<?php
// Handle View action
if (isset($_GET['view'])) {
  $id = $_GET['view'];
  $view_supplier_result = $conn->query("
    SELECT suppliers.* FROM suppliers 
    WHERE id='$id' AND flag = 'active'
    ");
  $view_supplier = $view_supplier_result->fetch(PDO::FETCH_ASSOC);

  if ($view_supplier) {
    // View selected supplier details in a card
?>
    <hr>
    <h3>Supplier Details</h3>
    <div class="card">
      <div class="card-header">
        Supplier Details (View Only)
      </div>
      <div class="card-body">
        <p><strong>Supplier name:</strong> <?php echo $view_supplier['supplier_name']; ?></p>
        <p><strong>Contact Info:</strong> <?php echo $view_supplier['contact_info']; ?></p>
        <a href="manage_suppliers.php" class="btn btn-secondary">Back to List</a>
      </div>
    </div>
  <?php
  }
} else {
  ?>

  <!-- Form to add/update supplier -->
  <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
    <form method="POST" action="manage_suppliers.php">
      <h3>Suppliers</h3>
      <div class="card">
        <div class="card-header">
          Supplies (Add/Update)
        </div>
      </div>
      <input type="hidden" name="id" value="<?php echo $id; ?>">

      <div class="form-group">
        <label><strong>Supplier Name</strong></label>
        <input type="text" class="form-control" name="supplier_name" placeholder="Enter Supplier Name" value="<?php echo $supplier_name; ?>" required>
      </div>
      <div class="form-group">
        <label><strong>Contact Info</strong></label>
        <input type="text" class="form-control" name="contact_info" placeholder="Enter Contact Info" value="<?php echo $contact_info; ?>" required>
      </div>
      <button type="submit" name="save_supplier" class="btn btn-primary">Save supplier</button>
      <a href="manage_suppliers.php" class="btn btn-secondary">Cancel</a>
    </form>
  <?php endif; ?>
<?php } ?>

<hr>

<!-- Supplier List -->
<table class=" table-striped table table-bordered">
  <thead class="table-dark">
    <tr>
      <th>Id</th>
      <th>SupplierName</th>
      <th>Contact Info</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($supplier = $list_suppliers_result->fetch(PDO::FETCH_ASSOC)) { ?>
      <tr>
        <td><?php echo $supplier['id']; ?></td>
        <td><?php echo $supplier['supplier_name']; ?></td>
        <td><?php echo $supplier['contact_info']; ?></td>
        <td>
          <a href="manage_suppliers.php?view=<?php echo $supplier['id']; ?>" class="btn btn-info btn-sm">View</a>
          <?php if ($crud_permissions['flag_update'] === 'active'): ?>
            <a href="manage_suppliers.php?edit=<?php echo $supplier['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
          <?php endif; ?>
          <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
            <a href="manage_suppliers.php?delete=<?php echo $supplier['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
<a href="manage_stock.php" class="btn btn-primary btn-sm">Manage Stock</a>

<?php include('footer.php'); ?>