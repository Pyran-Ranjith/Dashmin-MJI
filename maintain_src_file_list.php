<?php
ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection

$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back()';
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
} else {
    $show = true;
}

// Set default table name
$table_src = isset($_GET['tablesrc']) ? $_GET['tablesrc'] : 'src_file_list_backup1';

// Parse the query string
if (!empty($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];

    // Split the query string into individual parameters
    $params = explode('|', $query_string);

    // Initialize an associative array to hold key-value pairs
    $parsed_params = [];

    foreach ($params as $param) {
        list($key, $value) = explode('=', $param);
        $parsed_params[$key] = $value;
        if ($key == 'tablesrc' || $key == 'table_1') {
            $table_src = $value;
            // } elseif ($key == 'table_2') {
            //             $table_src = $value;
        }
        // if ($key == 'description') {
        //     $description = $value;
        // } elseif ($key == 'table_2') {
        //             $table_src = $value;
        // }
    }
}
// Handle Create, Update, and Delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_file'])) {
        $file_name = $_POST['file_name'];
        $flag = $_POST['flag'];
        $sql = "INSERT INTO $table_src (file_name, flag) VALUES ('$file_name', '$flag')";
        $conn->query($sql);
        header("Location: maintain_src_file_list.php");
        exit;
    } elseif (isset($_POST['update_file'])) {
        $id = $_POST['id'];
        $file_name = $_POST['file_name'];
        $flag = $_POST['flag'];
        $stmt = $conn->prepare("UPDATE $table_src SET file_name=:file_name, flag=:flag WHERE id=:id");
        $stmt->bindParam(':file_name', $file_name);
        $stmt->bindParam(':flag', $flag);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}

// Handle Delete Action (Set flag to inactive)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("UPDATE $table_src SET flag='inactive' WHERE id=:id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    header("Location: maintain_src_file_list.php");
    exit;
} elseif (isset($_GET['tablesrc'])) {
    $tablesrc = $_GET['tablesrc'];
}

// Fetch Data for Display
$sql = "SELECT * FROM $table_src ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Source Files</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Custom Styling */
        .info-box {
            /* background: linear-gradient(135deg,rgb(168, 136, 203), #2575fc); */
            background: linear-gradient(135deg, rgb(193, 162, 227), rgb(87, 145, 244));
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .info-box h3 {
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-box .form-control {
            border: 2px solid #fff;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .info-box .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .info-box .form-control:focus {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: #fff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }

        .info-box button {
            background-color: #ff7f50;
            border: none;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s ease;
        }

        .info-box button:hover {
            background-color: #e56740;
        }
    </style>
</head>

<body>
    <?php if (isset($show)) : ?>
        <h2 class="text-center">Manage Source Files</h2>

        <div class="info-box">
            <div class="card">
                <div class="card-header">
                    <h4>Parameters</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <!-- <tr>
                            <td>
                                <strong>Calling pgm : </strong>
                            </td>
                            <td>
                                <?php echo basename(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH)); ?>
                            </td>
                        </tr> -->
                        <th class="table-info">Parameter-Key</th>
                        <th class="table-info">Parameter-Value</th>
                        <?php if (isset($params)) : ?>
                            <?php foreach ($params as $key => $part): ?>
                                <?php list($key, $value) = explode('=', $part); ?>
                                <tr>
                                    <?php if (($key == 'status')) : ?>
                                        <td><strong><?php echo $key; ?></strong></td>
                                        <td><strong><?php echo urldecode($value); ?></strong></td>
                                    <?php else : ?>
                                        <!-- <td><?php echo $part; ?></td> -->
                                        <td><?php echo $key; ?></td>
                                        <td><?php echo urldecode($value); ?></td>
                                    <?php endif;
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <!-- </br> -->
        <?php if (isset($description)) : ?>
            <div class="info-box">
                <div class="card">
                    <div class="card-header">
                        <h4>Description</h4>
                    </div>
                    <div class="card-body">

                        <table class="table table-borderless">
                            <tr>
                                <!-- <td><strong>Description:<?php //echo $key + 1; 
                                                                ?></strong></td> -->
                                <!-- <td><strong>Description:<?php //echo $key + 1; 
                                                                ?></strong></td> -->
                                <!-- <h5 class="mb-4 text-left"><?php //echo $description; 
                                                                ?></h5> -->
                                <td><?php echo $description; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <br>



        <!-- Add New File Form -->
        <form method="POST" action="maintain_src_file_list.php" class="mb-4">
            <input type="hidden" name="table_src" value="<?= htmlspecialchars($table_src); ?>">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="file_name" placeholder="Enter File Name" required>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="flag" required>
                        <option value="" disabled selected>Select Flag</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="add_file" class="btn btn-success">Add File</button>
                    <!-- <a href="<?php echo $previous_page ?>" class="btn btn-secondary">Previous page</a> -->
                    <!-- <a href="dashboard.php" class="btn btn-secondary">Dashboard</a> -->
                </div>
            </div>
        </form>


        <!-- File List Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-1">Source Table : <?php echo htmlspecialchars($table_src); ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Id</th>
                            <th>File Name</th>
                            <th>Flag</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        <?php foreach ($files as $file): ?>
                            <?php $i++; ?>
                            <tr>
                                <td><?= $i . "."; ?></td>
                                <td><?= htmlspecialchars($file['id']); ?></td>
                                <td>
                                    <!-- Editable File Name -->
                                    <form method="POST" action="maintain_src_file_list.php" style="display: inline-block;">
                                        <input type="hidden" name="table_src" value="<?= htmlspecialchars($table_src); ?>">
                                        <input type="hidden" name="id" value="<?= $file['id']; ?>">
                                        <input type="text" name="file_name" class="form-control" style="width: 600px;" value="<?= htmlspecialchars($file['file_name']); ?>" class="form-control" required>
                                </td>
                                <td>
                                    <!-- Dropdown for Flag -->
                                    <select name="flag" class="form-control" required>
                                        <option value="active" <?= $file['flag'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?= $file['flag'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <!-- Actions -->
                                    <button type="submit" name="update_file" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                    <a href="maintain_src_file_list.php?delete=<?= $file['id']; ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                                    <a href="<?= htmlspecialchars($file['file_name']); ?>" class="btn btn-success btn-sm">Execute</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <!-- </body> -->

    <!-- </html> -->

    <?php
    include 'footer1.php'; // Include footer
    ?>