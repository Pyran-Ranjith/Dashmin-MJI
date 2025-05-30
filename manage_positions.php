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

// Initialize $positions as an empty array
$positions = [];

// Handle Create/Update actions
if (isset($_POST['save_position'])) {
    $position_name = $_POST['position_name'];

    try {
        if ($_POST['id']) {
            // Update existing position
            $id = $_POST['id'];
            $sql = "UPDATE positions SET position_name=:position_name WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'position_name' => $position_name,
                'id' => $id
            ]);
        } else {
            // Create new position
            $sql = "INSERT INTO positions (position_name) VALUES (:position_name)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'position_name' => $position_name
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
        $sql = "UPDATE positions SET flag='inactive' WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Fetch all active positions
try {
    $positions = $conn->query("SELECT * FROM positions WHERE flag = 'active' ORDER BY position_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// If editing, fetch the position data
$editing = false;
if (isset($_GET['edit'])) {
    try {
        $id = $_GET['edit'];
        $editing = true;
        $stmt = $conn->prepare("SELECT * FROM positions WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $edit_position = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Manage Positions</title>
    <!-- Add your CSS styles here -->
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2>Manage Positions</h2>
        </div>

        <div class="card-body">
            <!-- Form to add/update position -->
            <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                <form method="POST" action="manage_positions.php">
                    <input type="hidden" name="id" value="<?php echo $editing ? $edit_position['id'] : ''; ?>">
                    <div class="form-group row mb-3">
                        <label for="position_name" class="col-sm-2 col-form-label"><strong>Position Name</strong></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="position_name" placeholder="Enter position name" 
                                   value="<?php echo $editing ? $edit_position['position_name'] : ''; ?>" 
                                   required maxlength="25">
                        </div>
                    </div>
                    <button type="submit" name="save_position" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Save'; ?> Position</button>
                    <a href="manage_positions.php" class="btn btn-secondary">Cancel</a>
                </form>
                <hr>
            <?php endif; ?>

            <!-- position List -->
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Position Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($positions)): ?>
                        <?php foreach ($positions as $position) { ?>
                            <tr>
                                <td><?php echo $position['id']; ?></td>
                                <td><?php echo $position['position_name']; ?></td>
                                <td>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_positions.php?edit=<?php echo $position['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_positions.php?deactivate=<?php echo $position['id']; ?>" class="btn btn-danger btn-sm">Deactivate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No active positions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="mt-3">
                <a href="manage_stock.php" class="btn btn-secondary">Back to Stock</a>
                <?php if ($crud_permissions['flag_create'] === 'active'): ?>
                    <a href="manage_positions.php" class="btn btn-success">Add New Position</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>