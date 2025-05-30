<?php
ob_start();
include('db.php');
include('header.php');

// Initialize empty variables to hold form data for pre-fill when editing
$tag_name = '';
$id = '';

// Handle Create/Update actions
if (isset($_POST['save_menutag'])) {
    $tag_name = $_POST['tag_name'];

    if ($_POST['id']) {
        // Update existing menutag
        $id = $_POST['id'];
        $sql = "UPDATE menu_tags 
                SET tag_name='$tag_name'
                WHERE id='$id'";
    } else {
        // Create new menutag entry
        $sql = "INSERT INTO menu_tags (tag_name)
                VALUES ('$tag_name')";
    }
    $conn->query($sql);
    header('Location: manage_menutags.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "UPDATE menu_tags 
    SET flag='inactive' 
    WHERE id='$id'";
   
    $conn->query($sql);
    header('Location: manage_menutags.php');
    exit;
}

// Fetch fields
$list_menutags_result = $conn->query("SELECT * FROM menu_tags WHERE flag = 'active'");

// Pre-fill form if editing a menutag
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $menutag_result = $conn->query("SELECT * FROM menu_tags WHERE id='$id' AND flag = 'active'");
    $menutag = $menutag_result->fetch(PDO::FETCH_ASSOC);

    if ($menutag) {
        $tag_name = $menutag['tag_name'];
    }
}

?>

<div class="card">
    <div class="card-header">
        <h2>Manage Menutags</h2>
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
                    $menutag_result = $conn->query("
                        SELECT menu_tags.* 
                        FROM menu_tags 
                        WHERE menu_tags.id='$id' AND flag = 'active'
                    ");
                    $menutag = $menutag_result->fetch(PDO::FETCH_ASSOC);

                    if ($menutag) {
                        $back = true;
                        // View selected menutag details in a card
                ?>
                        <hr>
                        <h3>Menutag Details</h3>
                        <div class="card">
                            <div class="card-header">
                                Menutag Details (View Only)
                            </div>
                            <div class="card-body">
                                <p><strong>Menutag:</strong> <?php echo $menutag['tag_name']; ?></p>
                                <?php if ($back) : ?>
                                    <a href="manage_menutags.php" class="btn btn-secondary">Back to List</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                    }
                } else { //update
                    $back = true;
                    ?>

                    <!-- Form to add/update menutag -->
                    <form method="POST" action="manage_menutags.php">
                        <h3>Menutags</h3>
                        <div class="card">
                            <div class="card-header">
                                Menutags (Add/Update)
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="form-group">
                            <label><strong>Tag Name</strong></label>
                            <input type="text" class="form-control" name="tag_name" placeholder="Enter Menutag" value="<?php echo $tag_name; ?>" required>
                        </div>
                        <button type="submit" name="save_menutag" class="btn btn-primary">Save menutag</button>
                        <a href="manage_menutags.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php } ?>

                <hr>

                <!-- menutag List -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>menutag</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menutags1 = $list_menutags_result->fetch(PDO::FETCH_ASSOC)) { ?>
                            <tr>
                                <td><?php echo $menutags1['tag_name']; ?></td>
                                <td>
                                    <a href="manage_menutags.php?view=<?php echo $menutags1['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="manage_menutags.php?edit=<?php echo $menutags1['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="manage_menutags.php?delete=<?php echo $menutags1['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>

    <?php include('footer.php'); ?>