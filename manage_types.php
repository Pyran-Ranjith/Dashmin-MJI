<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$crud_permissions = $_SESSION['crud_permissions'];

ob_start();
include('db.php');
include('header.php');

// Initialize $types as an empty array
$types = [];

// Handle Create/Update actions
if (isset($_POST['save_type'])) {
    $type_name = $_POST['type_name'];

    try {
        if ($_POST['id']) {
            // Update existing type
            $id = $_POST['id'];
            $sql = "UPDATE types SET type_name=:type_name WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'type_name' => $type_name,
                'id' => $id
            ]);
        } else {
            // Create new type
            $sql = "INSERT INTO types (type_name) VALUES (:type_name)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'type_name' => $type_name
            ]);
        }
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Handle soft delete (update flag to inactive)
if (isset($_GET['deactivate'])) {
    try {
        $id = $_GET['deactivate'];
        $sql = "UPDATE types SET flag='inactive' WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Fetch all active types
try {
    $types = $conn->query("SELECT * FROM types WHERE flag = 'active' ORDER BY type_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// If editing, fetch the type data
$editing = false;
if (isset($_GET['edit'])) {
    try {
        $id = $_GET['edit'];
        $editing = true;
        $stmt = $conn->prepare("SELECT * FROM types WHERE id=:id AND flag = 'active'");
        $stmt->execute(['id' => $id]);
        $edit_type = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Types</title>
    <!-- Add your CSS styles here -->
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h2>Manage Types</h2>
        </div>

        <div class="card-body">
            <!-- Form to add/update type -->
            <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                <form method="POST" action="manage_types.php">
                    <input type="hidden" name="id" value="<?php echo $editing ? $edit_type['id'] : ''; ?>">
                    <div class="form-group row mb-3">
                        <label for="type_name" class="col-sm-2 col-form-label"><strong>Type Name</strong></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="type_name" placeholder="Enter type name"
                                value="<?php echo $editing ? $edit_type['type_name'] : ''; ?>"
                                required maxlength="25">
                        </div>
                    </div>
                    <button type="submit" name="save_type" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Save'; ?> Type</button>
                    <a href="manage_types.php" class="btn btn-secondary">Cancel</a>
                </form>
                <hr>
            <?php endif; ?>

            <!-- Type List -->
            <table class=" table-striped table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Type Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($types)): ?>
                        <?php foreach ($types as $type) { ?>
                            <tr>
                                <td><?php echo $type['id']; ?></td>
                                <td><?php echo $type['type_name']; ?></td>
                                <td>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_types.php?edit=<?php echo $type['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_types.php?deactivate=<?php echo $type['id']; ?>" class="btn btn-danger btn-sm">Deactivate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No active types found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="mt-3">
                <a href="manage_stock.php" class="btn btn-secondary">Back to Stock</a>
                <?php if ($crud_permissions['flag_create'] === 'active'): ?>
                    <a href="manage_types.php" class="btn btn-success">Add New Type</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>