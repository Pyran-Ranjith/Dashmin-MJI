<?php
ob_start();
include('db.php');
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$menu_option_id = ''; //dd
$role_id = ''; //dd
$menu_tag_id = ''; //dd
$roleorder = '';
$id = '';

// Filter variables
$filter_menu_name = isset($_GET['filter_menu_name']) ? $_GET['filter_menu_name'] : '';
$filter_role_name = isset($_GET['filter_role_name']) ? $_GET['filter_role_name'] : '';
$filter_tag_name = isset($_GET['filter_tag_name']) ? $_GET['filter_tag_name'] : '';
// Handle Filte Inputs
if (isset($_POST['filter'])) {
    $filter_menu_name = $_POST['filter_menu_name'];
    $filter_role_name = $_POST['filter_role_name'];
    $filter_tag_name = $_POST['filter_tag_name'];
}
// Handle Create/Update actions
if (isset($_POST['save_role_menu_options'])) {
    $menu_option_id = $_POST['menu_option_id'];
    $role_id = $_POST['role_id'];
    $menu_tag_id = $_POST['menu_tag_id'];
    $roleorder = $_POST['roleorder'];

    if ($_POST['id']) {
        // Update existing supplier purchase
        $id = $_POST['id'];
        $sql = "UPDATE role_menu_options 
                SET role_id='$role_id', menu_tag_id='$menu_tag_id', roleorder='$roleorder', menu_option_id='$menu_option_id' 
                WHERE id='$id' AND flag = 'active'";
    } else {
        // Create new supplier purchase entry
        $sql = "INSERT INTO role_menu_options (role_id, menu_option_id, roleorder, menu_tag_id)
                VALUES ('$role_id', '$menu_option_id', '$roleorder', '$menu_tag_id')";
    }
    $conn->query($sql);
    header('Location: manage_role_menu_options-2.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE role_menu_options
    SET flag='inactive' 
    WHERE id='$id'";

    $conn->query($sql);
    header('Location: manage_role_menu_options-2.php');
    exit;
}

$query = "SELECT role_menu_options.*, menu_options.menu_name, roles.role_name, menu_tags.tag_name 
    FROM role_menu_options 
    JOIN menu_options ON role_menu_options.menu_option_id = menu_options.id 
    JOIN roles ON role_menu_options.role_id = roles.id 
    JOIN menu_tags ON role_menu_options.menu_tag_id = menu_tags.id
    WHERE role_menu_options.flag = 'active'
    -- AND roles.role_name LIKE '%Manager%'
    ";

if (!empty($filter_menu_name)) {
    $query .= " AND menu_options.menu_name LIKE '%$filter_menu_name%'";
}
if (!empty($filter_role_name)) {
    $query .= " AND roles.role_name LIKE '%$filter_role_name%'";
}
if (!empty($filter_tag_name)) {
    $query .= " AND menu_tags.tag_name LIKE '%$filter_tag_name%'";
}

$role_menu_options_result99 = $conn->query($query);

$menu_options_result = $conn->query("SELECT * FROM menu_options WHERE flag = 'active'");
$roles_result = $conn->query("SELECT * FROM roles WHERE flag = 'active'");
$menu_tags_result = $conn->query("SELECT * FROM menu_tags WHERE flag = 'active'");

// Pre-fill form if editing a purchase
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $role_menu_options_result = $conn->query("SELECT * FROM role_menu_options WHERE id='$id' AND flag = 'active'");
    $role_menu_option = $role_menu_options_result->fetch(PDO::FETCH_ASSOC);

    if ($role_menu_option) {
        $menu_option_id = $role_menu_option['menu_option_id'];
        $role_id = $role_menu_option['role_id'];
        $menu_tag_id = $role_menu_option['menu_tag_id'];
        $roleorder = $role_menu_option['roleorder'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Role Menu Ootions</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
            </div>

            <div class="card-body">

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $query = "SELECT role_menu_options.*, menu_options.menu_name, roles.role_name, menu_tags.tag_name 
                        FROM role_menu_options 
                        JOIN menu_options ON role_menu_options.menu_option_id = menu_options.id 
                        JOIN roles ON role_menu_options.role_id = roles.id 
                        JOIN menu_tags ON role_menu_options.menu_tag_id = menu_tags.id 
                        WHERE role_menu_options.id='$id' AND role_menu_options.flag = 'active'
                    ";
                    $role_menu_options_result = $conn->query($query);
                    $filter_role_name  = "Manager";
                    if (!empty($filter_menu_name)) {
                        $query .= " AND menu_options.menu_name LIKE '%$filter_menu_name%'";
                    }
                    if (!empty($filter_role_name)) {
                        $query .= " AND roles.role_name LIKE '%$filter_role_name%'";
                    }
                    if (!empty($filter_tag_name)) {
                        $query .= " AND menu_tags.tag_name LIKE '%$filter_tag_name%'";
                    }

                    $role_menu_options = $role_menu_options_result->fetch(PDO::FETCH_ASSOC);

                    if ($role_menu_options) {
                        // View selected role_menu_options details in a card
                ?>
                        <hr>
                        <h3>Role Menu Options Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Role Menu Ootions Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>Menu option name:</strong> <?php echo $role_menu_options['menu_option_id']; ?></p>
                                <p><strong>Role name:</strong> <?php echo $role_menu_options['role_name']; ?></p>
                                <p><strong>Menu tag name:</strong> <?php echo $role_menu_options['tag_name']; ?></p>
                                <p><strong>roleorder:</strong> <?php echo $role_menu_options['roleorder']; ?></p>
                                <a href="manage_role_menu_options-2.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>

                    <!-- Form to add/update role menu options -->
                    <form method="POST" action="manage_role_menu_options-2.php">
                        <h3>Role Menu Ootions</h3>
                        <div class="card">
                            <div class="card-header">
                                Role Menu Ootions (Add/Update)
                            </div>

                            <div>
                                <form method="GET" action="manage_role_menu_options-2.php">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="text" name="filter_menu_name" class="form-control" placeholder="Filter by Menu Name" value="<?php echo htmlspecialchars($filter_menu_name); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="filter_role_name" class="form-control" placeholder="Filter by Role Name" value="<?php echo htmlspecialchars($filter_role_name); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" name="filter_tag_name" class="form-control" placeholder="Filter by Tag Name" value="<?php echo htmlspecialchars($filter_tag_name); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                            <a href="manage_role_menu_options-2.php" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>




                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">

                        <div class="form-group">
                            <label><strong>Menu option</strong></label>
                            <select class="form-control" name="menu_option_id" required>
                                <?php while ($menu_option9 = $menu_options_result->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $menu_option9['id']; ?>" <?php if ($menu_option9['id'] == $menu_option_id) echo 'selected'; ?>>
                                        <!-- <option value="<?php //echo $menu_option9['id']; 
                                                            ?>" > -->
                                        <!-- <?php //echo $menu_tag['tag_name']; 
                                                ?> -->
                                        <?php echo $menu_option9['menu_name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><strong>Role</strong></label>
                            <select class="form-control" name="role_id" required>
                                <?php while ($roles = $roles_result->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $roles['id']; ?>" <?php if ($roles['id'] == $role_id) echo 'selected'; ?>>
                                        <?php echo $roles['role_name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Menu tag</strong></label>
                            <select class="form-control" name="menu_tag_id" required>
                                <?php while ($menu_tag = $menu_tags_result->fetch(PDO::FETCH_ASSOC)) { ?>
                                    <option value="<?php echo $menu_tag['id']; ?>" <?php if ($menu_tag['id'] == $menu_tag_id) echo 'selected'; ?>>
                                        <?php echo $menu_tag['tag_name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>roleorder</strong></label>
                            <input type="number" class="form-control" name="roleorder" placeholder="Enter roleorder" value="<?php echo $roleorder; ?>" required>
                        </div>
                        <button type="submit" name="save_role_menu_options" class="btn btn-primary">Save</button>
                        <a href="manage_role_menu_options-2.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php } ?>

                <hr>

                <!-- Supplier Purchase List -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Menu Option</th>
                            <th>Role</th>
                            <th>Men Tag</th>
                            <th>Roleorder</th>
                            <th>Actionssss</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($role_menu_option1 = $role_menu_options_result99->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $role_menu_option1['menu_name']; ?></td>
                                <td><?php echo $role_menu_option1['role_name']; ?></td>
                                <td><?php echo $role_menu_option1['tag_name']; ?></td>
                                <td><?php echo $role_menu_option1['roleorder']; ?></td>
                                <td>
                                    <a href="manage_role_menu_options-2.php?view=<?php echo $role_menu_option1['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="manage_role_menu_options-2.php?edit=<?php echo $role_menu_option1['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_role_menu_options-2.php?delete=<?php echo $role_menu_option1['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
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