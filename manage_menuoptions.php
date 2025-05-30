<?php
ob_start();
include('db.php');
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$menu_name = '';
$menu_link = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_menuoption'])) {
    $menu_name = $_POST['menu_name'];
    $menu_link = $_POST['menu_link'];

    if ($_POST['id']) {
        // Update existing menuoption
        $id = $_POST['id'];
        $sql = "UPDATE menu_options 
                SET menu_name='$menu_name', menu_link='$menu_link'
                WHERE id='$id'";
    } else {
        // Create new menuoption entry
        $sql = "INSERT INTO menu_options (menu_name, menu_link)
                VALUES ('$menu_name', '$menu_link')";
    }
    $conn->query($sql);
    header('Location: manage_menuoptions.php');
    exit;
}

// Handle Delete action (Set flag to inactive)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE menu_options 
            SET flag='inactive' 
            WHERE id='$id'";
    $conn->query($sql);
    header('Location: manage_menuoptions.php');
    exit;
}

// Handle Flag Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flag'])) {
    $id = $_POST['id'];
    $flag = $_POST['flag'];

    $stmt = $conn->prepare("UPDATE menu_options SET flag = :flag WHERE id = :id");
    $stmt->bindParam(':flag', $flag);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    header('Location: manage_menuoptions.php');
    exit;
}

// Fetch fields
// $list_menuoptions_result = $conn->query("SELECT * FROM menu_options WHERE flag = 'active'");
$list_menuoptions_result = $conn->query("SELECT * FROM menu_options");

// Pre-fill form if editing a menuoption
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $menuoption_result = $conn->query("SELECT * FROM menu_options WHERE id='$id'");
    $menuoption = $menuoption_result->fetch(PDO::FETCH_ASSOC);

    if ($menuoption) {
        $menu_name = $menuoption['menu_name'];
        $menu_link = $menuoption['menu_link'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Menuoptions</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header"></div>
            <div class="card-body">

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $menuoption_result = $conn->query("
                        SELECT * 
                        FROM menu_options 
                        WHERE id='$id' AND flag = 'active'
                    ");
                    $menuoption = $menuoption_result->fetch(PDO::FETCH_ASSOC);

                    if ($menuoption) {
                        // View selected menuoption details in a card
                 ?>
                        <hr>
                        <h3>Menuoption Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Menuoption Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>Menuoption:</strong> <?php echo $menuoption['menu_name']; ?></p>
                                <p><strong>Menu Link:</strong> <?php echo $menuoption['menu_link']; ?></p>
                                <a href="manage_menuoptions.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    if (isset($_GET['edit'])) {
                        $id = $_GET['edit'];
                        $menuoption_result = $conn->query("SELECT * FROM menu_options WHERE id='$id'");
                        $menuoption = $menuoption_result->fetch(PDO::FETCH_ASSOC);
                        $menu_name = $menuoption['menu_name'];
                        $menu_link = $menuoption['menu_link'];
                    }
                    ?>

                    <!-- Form to add/update menuoption -->
                    <form method="POST" action="manage_menuoptions.php">
                        <h3>Menuoptions</h3>
                        <div class="card">
                            <div class="card-header">
                                Menuoptions (Add/Update)
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="form-group">
                            <label><strong>Menu Option</strong></label>
                            <input type="text" class="form-control form-control-lg" name="menu_name" placeholder="Enter Menuoption" value="<?php echo $menu_name; ?>" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Menu Link</strong></label>
                            <input type="text" class="form-control" name="menu_link" placeholder="Enter Menu Link" value="<?php echo $menu_link; ?>" >
                        </div>
                        <button type="submit" name="save_menuoption" class="btn btn-primary">Save Menuoption</button>
                        <a href="manage_menuoptions.php" class="btn btn-secondary">Back to List</a>
                        <a href="manage_role_menu_options-2.php" class="btn btn-warning">Manage role menu options</a>
                    </form>
                <?php } ?>

                <hr>

                <!-- Menuoption List -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Menuoption</th>
                            <th>Menu Link</th>
                            <th>Flag</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menuoptions1 = $list_menuoptions_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $menuoptions1['id']; ?></td>
                                <td><?php echo $menuoptions1['menu_name']; ?></td>
                                <td><?php echo $menuoptions1['menu_link']; ?></td>
                                <td>
                                    <form method="POST" action="manage_menuoptions.php" style="display: inline-block;">
                                        <input type="hidden" name="id" value="<?php echo $menuoptions1['id']; ?>">
                                        <select name="flag" class="form-control" onchange="this.form.submit()">
                                            <option value="active" <?php echo $menuoptions1['flag'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $menuoptions1['flag'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="manage_menuoptions.php?view=<?php echo $menuoptions1['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="manage_menuoptions.php?edit=<?php echo $menuoptions1['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_menuoptions.php?delete=<?php echo $menuoptions1['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    <a href="<?php echo $menuoptions1['menu_link']; ?>" class="btn btn-success btn-sm">Execute</a>
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
