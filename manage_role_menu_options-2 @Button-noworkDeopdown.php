<?php
ob_start();
include('db.php');
include('header.php');

// Fetch filter values if set
$filter_menu_option = isset($_GET['menu_option_id']) ? $_GET['menu_option_id'] : '';
$filter_role = isset($_GET['role_id']) ? $_GET['role_id'] : '';
$filter_menu_tag = isset($_GET['menu_tag_id']) ? $_GET['menu_tag_id'] : '';

// Build the WHERE condition dynamically
$where_conditions = ["role_menu_options.flag = 'active'"];
if (!empty($filter_menu_option)) {
    $where_conditions[] = "role_menu_options.menu_option_id = '$filter_menu_option'";
}
if (!empty($filter_role)) {
    $where_conditions[] = "role_menu_options.role_id = '$filter_role'";
}
if (!empty($filter_menu_tag)) {
    $where_conditions[] = "role_menu_options.menu_tag_id = '$filter_menu_tag'";
}

// Final SQL Query
$sql = "SELECT role_menu_options.*, menu_options.menu_name, roles.role_name, menu_tags.tag_name 
        FROM role_menu_options 
        JOIN menu_options ON role_menu_options.menu_option_id = menu_options.id 
        JOIN roles ON role_menu_options.role_id = roles.id 
        JOIN menu_tags ON role_menu_options.menu_tag_id = menu_tags.id 
        WHERE " . implode(" AND ", $where_conditions);

$role_menu_options_result99 = $conn->query($sql);

// Fetch dropdown data
$menu_options_result = $conn->query("SELECT * FROM menu_options WHERE flag = 'active'");
$roles_result = $conn->query("SELECT * FROM roles WHERE flag = 'active'");
$menu_tags_result = $conn->query("SELECT * FROM menu_tags WHERE flag = 'active'");
?>

<!-- Filters -->
<form method="GET" action="manage_role_menu_options-2.php">
    <div class="row">
        <div class="col-md-3">
            <select class="form-control" name="menu_option_id">
                <option value="">Select Menu Option</option>
                <?php while ($menu_option = $menu_options_result->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $menu_option['id']; ?>" <?php if ($menu_option['id'] == $filter_menu_option) echo 'selected'; ?>>
                        <?php echo $menu_option['menu_name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3">
            <select class="form-control" name="role_id">
                <option value="">Select Role</option>
                <?php while ($role = $roles_result->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $filter_role) echo 'selected'; ?>>
                        <?php echo $role['role_name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3">
            <select class="form-control" name="menu_tag_id">
                <option value="">Select Menu Tag</option>
                <?php while ($menu_tag = $menu_tags_result->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $menu_tag['id']; ?>" <?php if ($menu_tag['id'] == $filter_menu_tag) echo 'selected'; ?>>
                        <?php echo $menu_tag['tag_name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="manage_role_menu_options-2.php" class="btn btn-secondary">Reset</a>
        </div>
    </div>
</form>

<hr>

<!-- Data Table -->
<table class="table">
    <thead>
        <tr>
            <th>Menu Option</th>
            <th>Role</th>
            <th>Menu Tag</th>
            <th>Role Order</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($role_menu_option = $role_menu_options_result99->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?php echo $role_menu_option['menu_name']; ?></td>
                <td><?php echo $role_menu_option['role_name']; ?></td>
                <td><?php echo $role_menu_option['tag_name']; ?></td>
                <td><?php echo $role_menu_option['roleorder']; ?></td>
                <td>
                    <a href="manage_role_menu_options-2.php?view=<?php echo $role_menu_option['id']; ?>" class="btn btn-info btn-sm">View</a>
                    <a href="manage_role_menu_options-2.php?edit=<?php echo $role_menu_option['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="manage_role_menu_options-2.php?delete=<?php echo $role_menu_option['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include('footer.php'); ?>
