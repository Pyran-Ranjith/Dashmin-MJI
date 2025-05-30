<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ob_start();
include('db.php');
include('header.php');

// Initialize variables for form data
$role_id = '';
$flag_create = 'inactive';
$flag_read = 'inactive';
$flag_update = 'inactive';
$flag_delete = 'inactive';
$flag = 'active'; // Default flag
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_role_crud'])) {
    $role_id = intval($_POST['role_id']);
    $flag_create = $_POST['flag_create'];
    $flag_read = $_POST['flag_read'];
    $flag_update = $_POST['flag_update'];
    $flag_delete = $_POST['flag_delete'];
    $flag = $_POST['flag'];

    try {
        $conn->beginTransaction();

        if ($_POST['id']) {
            // Update existing role_crud entry
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("
                UPDATE role_crud 
                SET role_id=?, flag_create=?, flag_read=?, flag_update=?, flag_delete=?, flag=?
                WHERE id=?
            ");
            $stmt->execute([$role_id, $flag_create, $flag_read, $flag_update, $flag_delete, $flag, $id]);
        } else {
            // Create new role_crud entry
            $stmt = $conn->prepare("
                INSERT INTO role_crud (role_id, flag_create, flag_read, flag_update, flag_delete, flag)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$role_id, $flag_create, $flag_read, $flag_update, $flag_delete, $flag]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_role_crud.php');
    exit;
}

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        $conn->beginTransaction();

        // Set the flag to inactive
        $stmt = $conn->prepare("UPDATE role_crud SET flag = 'inactive' WHERE id=?");
        $stmt->execute([$id]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_role_crud.php');
    exit;
}

// Fetch all role_crud entries
try {
    $role_crud_result = $conn->query("
        SELECT role_crud.*, roles.role_name 
        FROM role_crud 
        JOIN roles ON role_crud.role_id = roles.id
        WHERE role_crud.flag = 'active'
    ");
    $role_crud_entries = $role_crud_result->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all roles for dropdown
    $roles_result = $conn->query("SELECT * FROM roles");
    $roles = $roles_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Pre-fill form if editing a role_crud entry
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM role_crud WHERE id=?");
        $stmt->execute([$id]);
        $role_crud = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($role_crud) {
            $role_id = $role_crud['role_id'];
            $flag_create = $role_crud['flag_create'];
            $flag_read = $role_crud['flag_read'];
            $flag_update = $role_crud['flag_update'];
            $flag_delete = $role_crud['flag_delete'];
            $flag = $role_crud['flag'];
        }
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
    <title>Maintain Role CRUD</title>
    <!-- Add your CSS styles here -->
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2>Maintain Role CRUD Permissions</h2>
        </div>

        <div class="card-body">
            <!-- Form to add/update role_crud entry -->
            <form method="POST" action="manage_role_crud.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="form-group">
                    <label><strong>Role</strong></label>
                    <select class="form-control" name="role_id" required>
                        <option value="" disabled selected>Select a Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $role_id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Create Permission</strong></label>
                    <select class="form-control" name="flag_create" required>
                        <option value="active" <?php if ($flag_create === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag_create === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Read Permission</strong></label>
                    <select class="form-control" name="flag_read" required>
                        <option value="active" <?php if ($flag_read === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag_read === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Update Permission</strong></label>
                    <select class="form-control" name="flag_update" required>
                        <option value="active" <?php if ($flag_update === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag_update === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Delete Permission</strong></label>
                    <select class="form-control" name="flag_delete" required>
                        <option value="active" <?php if ($flag_delete === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag_delete === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><strong>Status</strong></label>
                    <select class="form-control" name="flag" required>
                        <option value="active" <?php if ($flag === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" name="save_role_crud" class="btn btn-primary">Save</button>
                <a href="manage_role_crud.php" class="btn btn-secondary">Cancel</a>
            </form>

            <hr>

            <!-- Role CRUD List -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Create</th>
                        <th>Read</th>
                        <th>Update</th>
                        <th>Delete</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($role_crud_entries)): ?>
                        <?php foreach ($role_crud_entries as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['role_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['flag_create']); ?></td>
                                <td><?php echo htmlspecialchars($entry['flag_read']); ?></td>
                                <td><?php echo htmlspecialchars($entry['flag_update']); ?></td>
                                <td><?php echo htmlspecialchars($entry['flag_delete']); ?></td>
                                <td><?php echo htmlspecialchars($entry['flag']); ?></td>
                                <td>
                                    <a href="manage_role_crud.php?edit=<?php echo $entry['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_role_crud.php?delete=<?php echo $entry['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No role CRUD entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>