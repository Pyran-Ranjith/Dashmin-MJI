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

// Initialize $brands as an empty array
$brands = [];

// Handle Create/Update actions
if (isset($_POST['save_brand'])) {
    $brand_name = $_POST['brand_name'];

    try {
        if ($_POST['id']) {
            // Update existing brand
            $id = $_POST['id'];
            $sql = "UPDATE brands SET brand_name=:brand_name WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'brand_name' => $brand_name,
                'id' => $id
            ]);
        } else {
            // Create new brand
            $sql = "INSERT INTO brands (brand_name) VALUES (:brand_name)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'brand_name' => $brand_name
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
        $sql = "UPDATE brands SET flag='inactive' WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Fetch all active brands
try {
    $brands = $conn->query("SELECT * FROM brands WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// If editing, fetch the brand data
$editing = false;
if (isset($_GET['edit'])) {
    try {
        $id = $_GET['edit'];
        $editing = true;
        $stmt = $conn->prepare("SELECT * FROM brands WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $edit_brand = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Manage Brands</title>
    <!-- Add your CSS styles here -->
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h2>Manage Brands</h2>
        </div>

        <div class="card-body">
            <!-- Form to add/update brand -->
            <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                <form method="POST" action="manage_brands.php">
                    <input type="hidden" name="id" value="<?php echo $editing ? $edit_brand['id'] : ''; ?>">
                    <div class="form-group row mb-3">
                        <label for="brand_name" class="col-sm-2 col-form-label"><strong>Brand Name</strong></label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="brand_name" placeholder="Enter brand name" value="<?php echo $editing ? $edit_brand['brand_name'] : ''; ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="save_brand" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Save'; ?> Brand</button>
                    <a href="manage_brands.php" class="btn btn-secondary">Cancel</a>
                </form>
                <hr>
            <?php endif; ?>

            <!-- Brand List -->
            <table class=" table-striped table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Brand Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($brands)): ?>
                        <?php foreach ($brands as $brand) { ?>
                            <tr>
                                <td><?php echo $brand['id']; ?></td>
                                <td><?php echo $brand['brand_name']; ?></td>
                                <td>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_brands.php?edit=<?php echo $brand['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_brands.php?deactivate=<?php echo $brand['id']; ?>" class="btn btn-danger btn-sm">Deactivate</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No active brands found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>