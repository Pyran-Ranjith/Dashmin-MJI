<?php
ob_start();
include('db.php');
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$role_name = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_role'])) {
  $role_name = $_POST['role_name'];

    if ($_POST['id']) {
        // Update existing role
        $id = $_POST['id'];
        $sql = "UPDATE roles 
                SET role_name='$role_name'
                WHERE id='$id'";
    } else {
        // Create new role entry
        $sql = "INSERT INTO roles (role_name)
                VALUES ('$role_name')";
    }
    $conn->query($sql);
    header('Location: manage_roles.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE roles 
    SET flag='inactive' 
    WHERE id='$id'";
   
    $conn->query($sql);
    header('Location: manage_roles.php');
    exit;
}

// Fetch fields
$list_roles_result = $conn->query("
    SELECT roles.* 
    FROM roles
    WHERE flag = 'active' 
    ");

// Pre-fill form if editing a role
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $role_result = $conn->query("SELECT * FROM roles WHERE id='$id' AND flag = 'active'");
    $role = $role_result->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        $role_name = $role['role_name'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Roles</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
                <!-- <h2>Manage roles</h2> -->
            </div>

            <div class="card-body">
                <!-- <h2>Manage roles</h2> -->

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $role_result = $conn->query("
                        SELECT roles.* 
                        FROM roles 
                        WHERE roles.id='$id' AND flag = 'active'
                    ");
                    $role = $role_result->fetch(PDO::FETCH_ASSOC);

                    if ($role) {
                        // View selected role details in a card
                        ?>
                        <hr>
                        <h3>Role Details</h3>
                        <div class="card">
                        <div class="card-header">
                        Role Details (View Only)
                        </div>
                            <div class="card-body">
                                <p><strong>Role:</strong> <?php echo $role['role_name']; ?></p>
                                <a href="manage_roles.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                ?>

                <!-- Form to add/update role -->
                <form method="POST" action="manage_roles.php">
                <h3>Roles</h3>
                <div class="card">
                    <div class="card-header">
                        Supplier roles (Add/Update)
                    </div>
                </div>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <div class="form-group">
                        <label><strong>Role</strong></label>
                        <input type="text" class="form-control" name="role_name" placeholder="Enter Role" value="<?php echo $role_name; ?>" required>
                    </div>
                    <button type="submit" name="save_role" class="btn btn-primary">Save role</button>
                    <a href="manage_roles.php" class="btn btn-secondary">Cacel</a>
                    </form>
                <?php } ?>

                <hr>

                <!-- role List -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($role = $list_roles_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $role['role_name']; ?></td>
                                <td>
                                    <a href="manage_roles.php?view=<?php echo $role['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="manage_roles.php?edit=<?php echo $role['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_roles.php?delete=<?php echo $role['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>
<?php include('footer.php'); ?>
