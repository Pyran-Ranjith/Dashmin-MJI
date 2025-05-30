<?php
session_start();
ob_start();
include('db.php');
include('header.php');

$crud_permissions = $_SESSION['crud_permissions'];

// Initialize empty variables to hold form data for pre-fill when editing
$category_name = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_category'])) {
    $category_name = $_POST['category_name'];

    if ($_POST['id']) {
        // Update existing category
        $id = $_POST['id'];
        $sql = "UPDATE categories 
                SET category_name='$category_name'
                WHERE id='$id'";
    } else {
        // Create new category entry
        $sql = "INSERT INTO categories (category_name)
                VALUES ('$category_name')";
    }
    $conn->query($sql);
    header('Location: manage_categories.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE categories 
    SET flag='inactive' 
    WHERE id='$id'";

    $conn->query($sql);
    header('Location: manage_categories.php');
    exit;
}

// Fetch fields
$list_categories_result = $conn->query("
    SELECT categories.* 
    FROM categories
     WHERE flag = 'active' 
    ");

// Pre-fill form if editing a category
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $category_result = $conn->query("SELECT * FROM categories WHERE id='$id' AND flag = 'active'");
    $category = $category_result->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $category_name = $category['category_name'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Makes</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
                <!-- <h2>Manage Makes</h2> -->
            </div>

            <div class="card-body">
                <!-- <h2>Manage Makes</h2> -->

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $category_result = $conn->query("
                        SELECT categories.* 
                        FROM categories 
                        WHERE id='$id' AND flag = 'active'
                    ");
                    $category = $category_result->fetch(PDO::FETCH_ASSOC);

                    if ($category) {
                        // View selected category details in a card
                ?>
                        <hr>
                        <h3>Make Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Make Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>Make:</strong> <?php echo $category['category_name']; ?></p>
                                <a href="manage_categories.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>

                    <!-- Form to add/update category -->
                    <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                        <form method="POST" action="manage_categories.php">
                            <h3>Makes</h3>
                            <div class="card">
                                <div class="card-header">
                                    Makes (Add/Update)
                                </div>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="form-group">
                                <label><strong>Makes</strong></label>
                                <input type="text" class="form-control" name="category_name" placeholder="Enter Make" value="<?php echo $category_name; ?>" required>
                            </div>
                            <button type="submit" name="save_category" class="btn btn-primary">Save Make</button>
                            <a href="manage_categories.php" class="btn btn-secondary">Back to List</a>
                        </form>
                    <?php endif; ?>
                <?php } ?>

                <hr>

                <!-- category List -->
                <table class=" table-striped table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Make</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $list_categories_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $category['category_name']; ?></td>
                                <td>
                                    <a href="manage_categories.php?view=<?php echo $category['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_categories.php?edit=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <a href="manage_stock.php" class="btn btn-primary btn-sm">Manage Stock</a>

            </div>
        </div>

    </div>
</div>
<?php include('footer.php'); ?>