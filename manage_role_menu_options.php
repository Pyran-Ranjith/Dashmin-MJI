<?php
ob_start(); 
include('db.php');
include('header.php');

// Handle Create/Update actions
if (isset($_POST['save_role_menu_options'])) { //user pressed 'Save role_menu_options' button
    $role_id = $_POST['role_id'];
    $menu_tag_id = $_POST['menu_tag_id'];
    $roleorder = $_POST['roleorder'];
    $menu_option_id = $_POST['menu_option_id'];

    if ($_POST['id']) {
        // Update existing role_menu_options
        $id = $_POST['id'];
        $sql = "UPDATE role_menu_options SET  
                role_id=:role_id, menu_tag_id=:menu_tag_id, roleorder=:roleorder, 
                menu_option_id=:menu_option_id WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'role_id' => $role_id,
            'menu_tag_id' => $menu_tag_id,
            'roleorder' => $roleorder,
            'menu_option_id' => $menu_option_id,
            'id' => $id
        ]);
    } else { //user pressed view, edit or delete button
        // Create new role_menu_options entry
        $sql = "INSERT INTO role_menu_options role_id, menu_tag_id, roleorder, menu_option_id)
                VALUES (:role_id, :menu_tag_id, :roleorder, :menu_option_id)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'role_id' => $role_id,
            'menu_tag_id' => $menu_tag_id,
            'roleorder' => $roleorder,
            'menu_option_id' => $menu_option_id
        ]);
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM role_menu_options WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
}

// Handle view action
$view_role_menu_options = null;
if (isset($_GET['view'])) {
    $id = $_GET['view'];
    // echo "id = ".$id;

    $sql = "
    SELECT
    -- s.*,
    s.id, c.role_name, su.menu_name, s.roleorder, m.tag_name 
    FROM role_menu_options s 
    JOIN roles c ON s.role_id = c.id 
    JOIN menu_tags m ON s.menu_tag_id = m.id 
    JOIN menu_options su ON s.menu_option_id = su.id
    -- ORDER BY c.role_name, s.roleorder
    ORDER BY s.role_id, s.menu_tag_id
   WHERE s.id=$id flag = 'active'
    ";

    $viewrole_menu_options_result = $conn->query($sql);
    $view_role_menu_options = $viewrole_menu_options_result->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch role_menu_options, roles, and menu_options
$sql = "SELECT 
   -- s.*,
   s.id, c.role_name, su.menu_name, s.roleorder, m.tag_name 
    FROM role_menu_options s 
    JOIN roles c ON s.role_id = c.id 
    JOIN menu_tags m ON s.menu_tag_id = m.id 
    JOIN menu_options su ON s.menu_option_id = su.id
    -- ORDER BY c.role_name, s.roleorder
    ORDER BY s.role_id, m.tag_name
";
// echo $sql;
$role_menu_options_result = $conn->query($sql);
$role_menu_options = $role_menu_options_result->fetchAll(PDO::FETCH_ASSOC);

$roles_result = $conn->query("SELECT * FROM roles");
$roles = $roles_result->fetchAll(PDO::FETCH_ASSOC);

$menu_tags_result = $conn->query("SELECT * FROM menu_tags");
$menu_tags = $menu_tags_result->fetchAll(PDO::FETCH_ASSOC);

$menu_options_result = $conn->query("SELECT * FROM menu_options");
$menu_options = $menu_options_result->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the role_menu_options data
$editing = false;
$view = false;
if (isset($_GET['view'])) {
    $view = true;
}

if (isset($_GET['edit']) or isset($_GET['view'])) {
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
    };
    if (isset($_GET['view'])) {
        $id = $_GET['view'];
    };
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM role_menu_options WHERE id=:id");
    $stmt->execute(['id' => $id]);
    $edit_role_menu_options = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<style>
/* Style for the filter area */
.filter-area {
    border: 2px solid #007bff; /* Blue border */
    border-radius: 10px; /* Rounded corners */
    padding: 20px; /* Add some padding */
    background-color: #f8f9fa; /* Light background color */
    margin-bottom: 20px; /* Space below the filter area */
}

/* Style for the filter buttons */
.filter-area .btn {
    margin-right: 10px; /* Space between buttons */
}
</style>

<div class="card">
    <div class="card-header">
        <h2>Manage Role Menu Opitons</h2>
    </div>

    <div class="card-body">
        <!-- View Details in Card -->
        <?php if ($view_role_menu_options) { ?>
            <div class="card">
                <div class="card-header">
                    role_menu_options Details (View Only)
                </div>
                <div class="card-body">
                </div>
            </div>

            <div class="card-body">
                <?php foreach ($view_role_menu_options as $view_role_menu_option) { ?>
                    <table class="table table-bordered">
                        <tbody>
                            <?php foreach ($view_role_menu_options as $view_role_menu_option) { ?>
                                <tr>
                                    <th style="width: 20%;">Id:</th>
                                    <td><?php echo $view_role_menu_option['id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Role name:</th>
                                    <td><?php echo $view_role_menu_option['role_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Menu name:</th>
                                    <td><?php echo $view_role_menu_option['menu_name']; ?></td>
                                </tr>
                                <tr>
                                    <th>Role order:</th>
                                    <td><?php echo $view_role_menu_option['roleorder']; ?></td>
                                </tr>
                                <tr>
                                    <th>Menu tag name:</th>
                                    <td><?php echo $view_role_menu_option['tag_name']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <a href="manage_role_menu_options.php" class="btn btn-secondary">Back to List</a>
            </div>

            <hr>

        <?php } else { ?>

            <!-- Form to add/update role_menu_options -->
    <!-- Filter area with rounded border and colored frame -->
    <div class="filter-area">
            <form method="POST" action="manage_role_menu_options.php">
                <div class="card">
                    <div class="card-header">
                        Role Menu Options Details (Add/Update)
                    </div>
                </div>

                <input type="hidden" name="id" value="<?php echo $editing ? $edit_role_menu_options['id'] : ''; ?>">

                <div class="form-group row mb-3">
                    <label for="Role_menu_tag_id" class="col-sm-2 col-form-label"><strong>Role menu tag id</strong></label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="roleorder" placeholder="Enter Roleorder" readonly value="<?php echo $editing ? $edit_role_menu_options['id'] : ''; ?>" step="0.01" required>
                    </div>
                </div>

                <div class="form-group row mb-3">
                    <label for="role_id" class="col-sm-2 col-form-label"><strong>Role</strong></label>
                    <div class="col-sm-10">
                        <select class="form-control" name="role_id" required>
                            <?php foreach ($roles as $role) { ?>
                                <option value="<?php echo $role['id']; ?>" <?php echo $editing && $edit_role_menu_options['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                    <?php echo $role['role_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row mb-3">
                    <label for="menu_tag_id" class="col-sm-2 col-form-label"><strong>Menu tag</strong></label>
                    <div class="col-sm-10">
                        <select class="form-control" name="menu_tag_id" id="menu_tag_id" required>
                            <?php foreach ($menu_tags as $menu_tag): ?>
                                <option value="<?php echo $menu_tag['id']; ?>"><?php echo $menu_tag['tag_name']; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group row mb-3">
                    <label for="roleorder" class="col-sm-2 col-form-label"><strong>Roleorder</strong></label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="roleorder" placeholder="Enter Roleorder" value="<?php echo $editing ? $edit_role_menu_options['roleorder'] : ''; ?>" step="0.01" required>
                    </div>
                </div>
                <div class="form-group row mb-3">
                    <label for="menu_option_id" class="col-sm-2 col-form-label"><strong>Mmenu Option</strong></label>
                    <div class="col-sm-10">
                        <select class="form-control" name="menu_option_id" required>
                            <?php foreach ($menu_options as $menu_option) { ?>
                                <option value="<?php echo $menu_option['id']; ?>" <?php echo $editing && $edit_role_menu_options['menu_option_id'] == $menu_option['id'] ? 'selected' : ''; ?>>
                                    <?php echo $menu_option['menu_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="save_role_menu_options" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Save'; ?> role_menu_options</button>
                <a href="manage_role_menu_options.php" class="btn btn-secondary">Cancel</a>
            </form>
            </div>

            <hr>
        <?php } ?>

        <!-- role_menu_options List -->
        <table class="table">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Role name</th>
                    <th>Menu name</th>
                    <th>Role order</th>
                    <th>Menu tag name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($role_menu_options as $role_menu_option) { ?>
                    <tr>
                        <td><?php echo $role_menu_option['id']; ?></td>
                        <td><?php echo $role_menu_option['role_name']; ?></td>
                        <td><?php echo $role_menu_option['menu_name']; ?></td>
                        <td><?php echo $role_menu_option['roleorder']; ?></td>
                        <td><?php echo $role_menu_option['tag_name']; ?></td>
                        <td>
                            <a href="manage_role_menu_options.php?view=<?php echo $role_menu_option['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="manage_role_menu_options.php?edit=<?php echo $role_menu_option['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="manage_role_menu_options.php?delete=<?php echo $role_menu_option['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</div>


<?php include('footer.php'); ?>