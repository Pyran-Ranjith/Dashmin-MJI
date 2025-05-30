<?php
session_start();
ob_start();
include('db.php');
include('header.php');

$crud_permissions = $_SESSION['crud_permissions'];

/// Initialize empty variables to hold form data for pre-fill when editing
$manage_activate = false;
$first_name = '';
$last_name = '';
$email = '';
$phone = '';
$address = '';
$created_at = '';
$flag = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_customer'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $created_at = $_POST['created_at'];
    if ($manage_activate) {
        $flag = $_POST['flag'];
    }

    if ($_POST['id']) {
        // Update existing customer
        $id = $_POST['id'];
        echo $id;
        echo "Update";
        if ($manage_activate) {
            $sql = "
            UPDATE customers 
            SET first_name='$first_name', last_name='$last_name', email='$email', phone='$phone', address='$address', created_at='$created_at', flag='$flag'
            WHERE id='$id'
            ";
        } else {
            $sql = "
            UPDATE customers 
            SET first_name='$first_name', last_name='$last_name', email='$email', phone='$phone', address='$address', created_at='$created_at'
            WHERE id='$id'
            ";
        }
    } else {
        echo $id;
        echo "insert";
        // Create new customer entry
        $sql = "INSERT INTO customers (first_name, last_name, email, phone, address, created_at)
                VALUES ('$first_name', '$last_name', '$email', '$phone', '$address', 'created_at')";
    }
    $conn->query($sql);
    header('Location: manage_customers.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE customers 
    SET flag='inactive' 
    WHERE id='$id'";

    $conn->query($sql);
    header('Location: manage_customers.php');
    exit;
}

if (isset($_GET['manage_activate'])) {
    $table_name = $_GET['manage_activate'];
    $manage_activate = true;
}

// Fetch customers for bottom list
$customers_result1 = $conn->query("
SELECT customers.*
FROM customers
 WHERE flag = 'active' 
");

// Pre-fill form if editing a purchase
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $customer_result = $conn->query("SELECT * FROM customers WHERE id='$id' AND flag = 'active'");
    $customer = $customer_result->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        $first_name = $customer['first_name'];
        $last_name = $customer['last_name'];
        $email = $customer['email'];
        $phone = $customer['phone'];
        $address = $customer['address'];
        $created_at = $customer['created_at'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Customers</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
                <h2>Customers Details</h2>
            </div>

            <div class="card-body">
                <!-- <h2>Manage Supplier Purchases</h2> -->

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $customer_result = $conn->query("
                        SELECT customers.*
                        FROM customers 
                        WHERE id='$id' AND flag = 'active'
                    ");
                    $customer = $customer_result->fetch(PDO::FETCH_ASSOC);

                    if ($customer) {
                        // View selected customer in a card
                ?>
                        <hr>
                        <h3>Customer Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Customer Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>First name:</strong> <?php echo $customer['first_name']; ?></p>
                                <p><strong>Last name:</strong> <?php echo $customer['last_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $customer['email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $customer['phone']; ?></p>
                                <p><strong>Address:</strong> <?php echo $customer['address']; ?></p>
                                <p><strong>Created at:</strong> <?php echo $customer['created_at']; ?></p>
                                <a href="manage_customers.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>

                    <!-- Form to add/update customer -->
                    <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                        <form method="POST" action="manage_customers.php">
                            <!-- <h3>Manage customers</h3> -->
                            <div class="card">
                                <!-- <div class="card-header">
                                Manage customers (Add/Update)
                            </div> -->
                            </div>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="form-group row mb-3">
                                <label for="first_name" class="col-sm-2 col-form-label"><strong>First name</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" value="<?php echo $first_name; ?>" required>
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label for="email" class="col-sm-2 col-form-label"><strong>Last name</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" value="<?php echo $last_name; ?>" >
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label for="email" class="col-sm-2 col-form-label"><strong>Email</strong></label>
                                <div class="col-sm-10">
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" value="<?php echo $email; ?>" >
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label for="phone" class="col-sm-2 col-form-label"><strong>Phone</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone" value="<?php echo $phone; ?>" >
                                </div>
                            </div>
                            <div class="form-group row mb-3">
                                <label for="address" class="col-sm-2 col-form-label"><strong>Address</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="address" name="address" class="form-control" placeholder="Enter address" value="<?php echo $address; ?>" >
                                </div>
                            </div>
                            <!-- <div class="form-group row mb-3">
                                <label for="created_at" class="col-sm-2 col-form-label"><strong>Created at</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="created_at" name="created_at" class="form-control" placeholder="Enter created at" value="<?php echo $created_at; ?>" >
                                </div>
                            </div> -->


                            <!-- <div class="form-group row mb-3">
                                <label for="flag" class="col-sm-2 col-form-label"><strong>Flag</strong></label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="flag" required>
                                        <?php //foreach ($roles as $role) { 
                                                ?>
                                        <option value="<?php //echo $role['id']; 
                                                        ?>" <?php //echo $editing && $edit_role_menu_options['role_id'] == $role['id'] ? 'selected' : ''; 
                                                            ?>>
                                            <?php //echo $role['role_name']; 
                                            ?>
                                        </option>

                                    <?php //} 
                                    ?>
                                        <option value="1">activate</option>
                                        <option value="2">inactivate</option>

                                    </select>
                                </div>
                            </div> -->


                            <button type="submit" name="save_customer" class="btn btn-primary">Save customer</button>
                            <a href="manage_customers.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php endif; ?>
                <?php } ?>

                <hr>

                <!-- customer List -->
                <table class=" table-striped table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <!-- <th>Created at</th> -->
                            <?php if ($manage_activate) {; ?> <php if ($manage_activate) { ?>
                                    <th>Flag</th>
                                <?php } ?>
                                <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($customer = $customers_result1->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></td>
                                <td><?php echo $customer['first_name']; ?></td>
                                <td><?php echo $customer['email']; ?></td>
                                <td><?php echo $customer['phone']; ?></td>
                                <td><?php echo $customer['address']; ?></td>
                                <!-- <td><?php echo $customer['created_at']; ?></td> -->
                                <?php if ($manage_activate) {; ?>
                                    <td><?php echo $customer['flag']; ?></td>
                                <?php } ?>
                                <td>
                                    <a href="manage_customers.php?view=<?php echo $customer['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_customers.php?edit=<?php echo $customer['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_customers.php?delete=<?php echo $customer['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>
<?php
include('footer.php');
?>