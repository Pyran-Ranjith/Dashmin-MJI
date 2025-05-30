<?php 
ob_start();
include('db.php');
include('header.php');

// Initialize empty variables for form pre-fill
$username = '';
$email = '';
$password = '';
$role_id = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt password
    $role_id = $_POST['role_id'];

    if (!empty($_POST['id'])) {
        // Update existing user
        $id = $_POST['id'];
        $sql = "UPDATE users 
                SET username = :username, email = :email, password = :password, role_id = :role_id  
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role_id' => $role_id,
            ':id' => $id
        ]);
    } else {
        // Create new user
        $sql = "INSERT INTO users (username, email, password, role_id) 
                VALUES (:username, :email, :password, :role_id)";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':role_id' => $role_id
        ]);
    }

    if ($success) {
        header('Location: manage_users.php');
        exit;
    } else {
        echo "<p class='alert alert-danger'>Error saving user.</p>";
    }
}

// Handle Delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    header('Location: manage_users.php');
    exit;
}

// Fetch users for list
$list_users_result = $conn->query("
SELECT users.*, roles.role_name as role_name FROM users
JOIN roles ON USERS.role_id = roles.id
 ORDER BY username");

// Fetch roles for dropdown
$roles_result = $conn->query("SELECT * FROM roles ORDER BY role_name");
$roles = $roles_result->fetchAll(PDO::FETCH_ASSOC);

// Pre-fill form if editing a user
$editing = false;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id ORDER BY username");
    $stmt->execute([':id' => $id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($edit_user) {
        $username = $edit_user['username'];
        $email = $edit_user['email'];
        $role_id = $edit_user['role_id'];
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Manage Users</h2>
    </div>
    <div class="card-body">
        <?php
        if (isset($_GET['view'])) {
            $id = $_GET['view'];
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $view_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($view_user) {
        ?>
        <hr>
        <h3>User Details</h3>
        <div class="card">
            <div class="card-header">User Details (View Only)</div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($view_user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($view_user['email']); ?></p>
                <a href="manage_users.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
        <?php
            }
        } else {
        ?>
        <form method="POST" action="manage_users.php">
            <h3><?php echo $editing ? 'Edit User' : 'Add User'; ?></h3>
            <div class="card">
                <div class="card-header">Users (Add/Update)</div>
            </div>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

            <div class="form-group">
                <label><strong>Username</strong></label>
                <input type="text" class="form-control" placeholder="Enter username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label><strong>Email</strong></label>
                <input type="email" class="form-control" name="email" placeholder="Enter Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label><strong>Password</strong></label>
                <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select class="form-control" name="role_id" required>
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $role) { ?>
                    <option value="<?php echo $role['id']; ?>" <?php echo ($editing && $role_id == $role['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" name="save_user" class="btn btn-primary">Save User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php } ?>
        <hr>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $list_users_result->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                    <td>
                        <a href="manage_users.php?view=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">View</a>
                        <a href="manage_users.php?edit=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
