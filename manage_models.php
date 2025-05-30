<?php
session_start();
ob_start();
include('db.php');
include('header.php');

$crud_permissions = $_SESSION['crud_permissions'];

// Initialize empty variables to hold form data for pre-fill when editing
$model_name = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_model'])) {
    $model_name = $_POST['model_name'];

    if ($_POST['id']) {
        // Update existing model
        $id = $_POST['id'];
        $sql = "UPDATE models 
                SET model_name='$model_name'
                WHERE id='$id'";
    } else {
        // Create new model entry
        $sql = "INSERT INTO models (model_name)
                VALUES ('$model_name')";
    }
    $conn->query($sql);
    header('Location: manage_models.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE models 
    SET flag='inactive' 
    WHERE id='$id'";

    $conn->query($sql);
    header('Location: manage_models.php');
    exit;
}

// Fetch fields
$list_models_result = $conn->query("SELECT * FROM models WHERE flag = 'active' ORDER BY model_name");

// Pre-fill form if editing a model
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $model_result = $conn->query("SELECT * FROM models WHERE id='$id' AND flag = 'active'");
    $model = $model_result->fetch(PDO::FETCH_ASSOC);

    if ($model) {
        $model_name = $model['model_name'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Models</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-header">
            </div>

            <div class="card-body">

                <?php
                // Handle View action
                if (isset($_GET['view'])) {
                    $id = $_GET['view'];
                    $model_result = $conn->query("
                        SELECT models.* 
                        FROM models 
                        WHERE models.id='$id' AND flag = 'active'
                    ");
                    $model = $model_result->fetch(PDO::FETCH_ASSOC);

                    if ($model) {
                        $back = true;
                        // View selected model details in a card
                ?>
                        <hr>
                        <h3>Model Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Model Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>model:</strong> <?php echo $model['model_name']; ?></p>
                                <?php if ($back) : ?>
                                    <a href="manage_models.php" class="btn btn-secondary">Back to List</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                    }
                } else { //update
                    $back = true;
                    ?>

                    <!-- Form to add/update model -->
                    <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                        <form method="POST" action="manage_models.php">
                            <h3>Models</h3>
                            <div class="card">
                                <div class="card-header">
                                    Models (Add/Update)
                                </div>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div class="form-group row mb-3">
                                <label for="model_name" class="col-sm-2 col-form-label"><strong>Model Name</strong></label>
                                <div class="col-sm-10">
                                    <input type="text" id="model_name" name="model_name" class="form-control" placeholder="Enter model name" value="<?php echo $model_name; ?>" required>
                                </div>
                            </div>

                            <button type="submit" name="save_model" class="btn btn-primary">Save model</button>
                            <a href="manage_models.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    <?php endif; ?>
                <?php } ?>

                <hr>

                <!-- model List -->
                <table class=" table-striped table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Model</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($models1 = $list_models_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $models1['model_name']; ?></td>
                                <td>
                                    <a href="manage_models.php?view=<?php echo $models1['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_models.php?edit=<?php echo $models1['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_models.php?delete=<?php echo $models1['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
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

    <?php include('footer.php'); ?>