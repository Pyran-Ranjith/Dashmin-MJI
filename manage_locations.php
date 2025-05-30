<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ob_start();
include('db.php');
include('header.php');

$crud_permissions = $_SESSION['crud_permissions'];

// Initialize variables for form data
$location_name = '';
$address = '';
$city = '';
$state = '';
$country = '';
$flag = 'active';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_location'])) {
    $location_name = $_POST['location_name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $flag = $_POST['flag'];

    try {
        $conn->beginTransaction();

        if ($_POST['id']) {
            // Update existing location
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("
                UPDATE locations 
                SET location_name=?, address=?, city=?, state=?, country=?, flag=?
                WHERE id=?
            ");
            $stmt->execute([$location_name, $address, $city, $state, $country, $flag, $id]);
        } else {
            // Create new location
            $stmt = $conn->prepare("
                INSERT INTO locations (location_name, address, city, state, country, flag)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$location_name, $address, $city, $state, $country, $flag]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_locations.php');
    exit;
}

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        $conn->beginTransaction();

        // Set the flag to inactive
        $stmt = $conn->prepare("UPDATE locations SET flag = 'inactive' WHERE id=?");
        $stmt->execute([$id]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_locations.php');
    exit;
}

// Fetch all locations
try {
    $locations_result = $conn->query("SELECT * FROM locations WHERE flag = 'active' ORDER BY location_name");
    $locations = $locations_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Pre-fill form if editing a location
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("SELECT * FROM locations WHERE id=?");
        $stmt->execute([$id]);
        $location = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($location) {
            $location_name = $location['location_name'];
            $address = $location['address'];
            $city = $location['city'];
            $state = $location['state'];
            $country = $location['country'];
            $flag = $location['flag'];
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
    <title>Manage Locations</title>
    <!-- Add your CSS styles here -->
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2>Manage Locations</h2>
        </div>

        <div class="card-body">
            <!-- Form to add/update location -->
            <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                <form method="POST" action="manage_locations.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="form-group">
                    <label><strong>Location Name</strong></label>
                    <input type="text" class="form-control" name="location_name" value="<?php echo $location_name; ?>" required>
                </div>
                <div class="form-group">
                    <label><strong>Address</strong></label>
                    <input type="text" class="form-control" name="address" value="<?php echo $address; ?>">
                </div>
                <div class="form-group">
                    <label><strong>City</strong></label>
                    <input type="text" class="form-control" name="city" value="<?php echo $city; ?>">
                </div>
                <div class="form-group">
                    <label><strong>State</strong></label>
                    <input type="text" class="form-control" name="state" value="<?php echo $state; ?>">
                </div>
                <div class="form-group">
                    <label><strong>Country</strong></label>
                    <input type="text" class="form-control" name="country" value="<?php echo $country; ?>">
                </div>
                <!-- <div class="form-group">
                    <label><strong>Status</strong></label>
                    <select class="form-control" name="flag" required>
                        <option value="active" <?php if ($flag === 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($flag === 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                </div> -->
                <button type="submit" name="save_location" class="btn btn-primary">Save</button>
                <a href="manage_locations.php" class="btn btn-secondary">Cancel</a>
            </form>
            <?php endif; ?>

            <hr>

            <!-- Locations List -->
            <table class=" table-striped table table-bordered">
            <thead class="table-dark">
                    <tr>
                        <th>Location Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Country</th>
                        <!-- <th>Status</th> -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($locations)): ?>
                        <?php foreach ($locations as $location): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($location['location_name']); ?></td>
                                <td><?php echo htmlspecialchars($location['address']); ?></td>
                                <td><?php echo htmlspecialchars($location['city']); ?></td>
                                <td><?php echo htmlspecialchars($location['state']); ?></td>
                                <td><?php echo htmlspecialchars($location['country']); ?></td>
                                <!-- <td><?php echo htmlspecialchars($location['flag']); ?></td> -->
                                <td>
                                <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                    <a href="manage_locations.php?edit=<?php echo $location['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_locations.php?delete=<?php echo $location['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to deactivate this location?')">Deactivate</a>
                                        <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No locations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>