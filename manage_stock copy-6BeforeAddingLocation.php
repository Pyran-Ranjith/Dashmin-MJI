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

// Initialize $stocks as an empty array
$stocks = [];

// Handle Create/Update actions
if (isset($_POST['save_stock'])) {
    $part_number = $_POST['part_number'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $category_id = $_POST['category_id'];
    $model_id = $_POST['model_id'];
    $cost = $_POST['cost'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $supplier_id = $_POST['supplier_id'];

    try {
        if ($_POST['id']) {
            // Update existing stock
            $id = $_POST['id'];
            $sql = "UPDATE stocks SET part_number=:part_number, description=:description, image=:image, 
                    category_id=:category_id, model_id=:model_id, cost=:cost, selling_price=:selling_price, 
                    stock_quantity=:stock_quantity, supplier_id=:supplier_id WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'stock_quantity' => $stock_quantity,
                'supplier_id' => $supplier_id,
                'id' => $id
            ]);
        } else {
            // Create new stock entry
            $sql = "INSERT INTO stocks (part_number, description, image, category_id, model_id, cost, selling_price, stock_quantity, supplier_id)
                    VALUES (:part_number, :description, :image, :category_id, :model_id, :cost, :selling_price, :stock_quantity, :supplier_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'stock_quantity' => $stock_quantity,
                'supplier_id' => $supplier_id
            ]);
        }
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    try {
        $id = $_GET['delete'];
        $sql = "DELETE FROM stocks WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Handle view action
$view_stocks = null;
if (isset($_GET['view'])) {
    try {
        $id = $_GET['view'];
        $sql = "
        SELECT s.*
        , c.category_name, m.model_name, su.supplier_name 
        FROM stocks s 
        JOIN categories c ON s.category_id = c.id 
        JOIN models m ON s.model_id = m.id 
        JOIN suppliers su ON s.supplier_id = su.id
        WHERE s.id=:id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $view_stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Fetch filter values from GET request
$filter_part_number = $_GET['filter_part_number'] ?? '';
$filter_category = $_GET['filter_category'] ?? '';
$filter_model = $_GET['filter_model'] ?? '';
$filter_description = $_GET['filter_description'] ?? '';

// Build the base SQL query
$sql = "SELECT 
s.*
, c.category_name, m.model_name, su.supplier_name 
FROM stocks s 
JOIN categories c ON s.category_id = c.id 
JOIN models m ON s.model_id = m.id 
JOIN suppliers su ON s.supplier_id = su.id
WHERE 1=1";

// Add filters to the SQL query
if (!empty($filter_part_number)) {
    $sql .= " AND s.part_number = :filter_part_number";
}
if (!empty($filter_category)) {
    $sql .= " AND s.category_id = :filter_category";
}
if (!empty($filter_model)) {
    $sql .= " AND s.model_id = :filter_model";
}
if (!empty($filter_description)) {
    $sql .= " AND s.description LIKE :filter_description";
}

// Prepare and execute the filtered query
try {
    $stmt = $conn->prepare($sql);
    if (!empty($filter_part_number)) {
        $stmt->bindValue(':filter_part_number', $filter_part_number);
    }
    if (!empty($filter_category)) {
        $stmt->bindValue(':filter_category', $filter_category);
    }
    if (!empty($filter_model)) {
        $stmt->bindValue(':filter_model', $filter_model);
    }
    if (!empty($filter_description)) {
        $stmt->bindValue(':filter_description', '%' . $filter_description . '%');
    }
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Fetch categories, models, and suppliers for dropdowns
try {
    $categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    $models = $conn->query("SELECT * FROM models")->fetchAll(PDO::FETCH_ASSOC);
    $suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// If editing, fetch the stock data
$editing = false;
$view = false;
if (isset($_GET['view'])) {
    $view = true;
}

if (isset($_GET['edit']) || isset($_GET['view'])) {
    try {
        $id = $_GET['edit'] ?? $_GET['view'];
        $editing = true;
        $stmt = $conn->prepare("SELECT * FROM stocks WHERE id=:id");
        $stmt->execute(['id' => $id]);
        $edit_stock = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Manage Stock</title>
    <!-- Add your CSS styles here -->
    <style>
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-section h4 {
            margin-bottom: 15px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2>Manage Stock</h2>
        </div>

        <div class="card-body">
            <!-- Filter Form with Styled Section -->
            <div class="filter-section">
                <h4>Filter Stocks</h4>
                <form method="GET" action="manage_stock.php">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filter_part_number">Part Number</label>
                            <select class="form-control" name="filter_part_number" id="filter_part_number">
                                <option value="">All</option>
                                <?php
                                $part_numbers = $conn->query("SELECT DISTINCT part_number FROM stocks");
                                foreach ($part_numbers as $part) {
                                    $selected = ($filter_part_number == $part['part_number']) ? 'selected' : '';
                                    echo "<option value='{$part['part_number']}' $selected>{$part['part_number']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_category">Category</label>
                            <select class="form-control" name="filter_category" id="filter_category">
                                <option value="">All</option>
                                <?php foreach ($categories as $category) {
                                    $selected = ($filter_category == $category['id']) ? 'selected' : '';
                                    echo "<option value='{$category['id']}' $selected>{$category['category_name']}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_model">Model</label>
                            <select class="form-control" name="filter_model" id="filter_model">
                                <option value="">All</option>
                                <?php foreach ($models as $model) {
                                    $selected = ($filter_model == $model['id']) ? 'selected' : '';
                                    echo "<option value='{$model['id']}' $selected>{$model['model_name']}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_description">Description</label>
                            <input type="text" class="form-control" name="filter_description" id="filter_description" value="<?php echo $filter_description; ?>" placeholder="Search description">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="manage_stock.php" class="btn btn-secondary">Reset Filters</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- View Stock Details in Card -->
            <?php if ($view_stocks) { ?>
                <div class="card">
                    <div class="card-header">
                        Stock Details (View Only)
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tbody>
                                <?php foreach ($view_stocks as $view_stock) { ?>
                                    <tr>
                                        <th>Part ID:</th>
                                        <td><?php echo $view_stock['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Part Number:</th>
                                        <td><?php echo $view_stock['part_number']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Description:</th>
                                        <td><?php echo $view_stock['description']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Image:</th>
                                        <td><img src="<?php echo $view_stock['image']; ?>" alt="Stock Image" width="100"></td>
                                    </tr>
                                    <tr>
                                        <th>Category:</th>
                                        <td><?php echo $view_stock['category_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Model:</th>
                                        <td><?php echo $view_stock['model_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Cost:</th>
                                        <td><?php echo $view_stock['cost']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Selling Price:</th>
                                        <td><?php echo $view_stock['selling_price']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Stock Quantity:</th>
                                        <td><?php echo $view_stock['stock_quantity']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Supplier:</th>
                                        <td><?php echo $view_stock['supplier_name']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <a href="manage_stock.php" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
                <hr>
            <?php } else { ?>
                <!-- Form to add/update stock -->
                <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                    <form method="POST" action="manage_stock.php">
                        <div class="card">
                            <div class="card-header">
                                Stock Details (Add/Update)
                            </div>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $editing ? $edit_stock['id'] : ''; ?>">
                        <div class="form-group row mb-3">
                            <label for="part_number" class="col-sm-2 col-form-label"><strong>Part Number</strong></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="part_number" placeholder="Enter part number" value="<?php echo $editing ? $edit_stock['part_number'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="description" class="col-sm-2 col-form-label"><strong>Description</strong></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="description" placeholder="Enter description" value="<?php echo $editing ? $edit_stock['description'] : ''; ?>">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="image" class="col-sm-2 col-form-label"><strong>Image</strong></label>
                            <div class="col-sm-10">
                                <input readonly type="text" class="form-control" name="image" value="<?php echo $editing ? $edit_stock['image'] : ''; ?>" placeholder="Enter image URL or path">
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="category_id" class="col-sm-2 col-form-label"><strong>Category</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="category_id" required>
                                    <?php foreach ($categories as $category) { ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $editing && $edit_stock['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo $category['category_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="model_id" class="col-sm-2 col-form-label"><strong>Model</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="model_id" id="model_id" required>
                                    <?php foreach ($models as $model): ?>
                                        <option value="<?php echo $model['id']; ?>"><?php echo $model['model_name']; ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="cost" class="col-sm-2 col-form-label"><strong>Cost</strong></label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="cost" placeholder="Enter cost" value="<?php echo $editing ? $edit_stock['cost'] : ''; ?>" step="0.01" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="selling_price" class="col-sm-2 col-form-label"><strong>Selling Price</strong></label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="selling_price" placeholder="Enter selling price" value="<?php echo $editing ? $edit_stock['selling_price'] : ''; ?>" step="0.01" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="stock_quantity" class="col-sm-2 col-form-label"><strong>Stock Quantity</strong></label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" name="stock_quantity" placeholder="Enter stock quantity" value="<?php echo $editing ? $edit_stock['stock_quantity'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="supplier_id" class="col-sm-2 col-form-label"><strong>Supplier</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="supplier_id" required>
                                    <?php foreach ($suppliers as $supplier) { ?>
                                        <option value="<?php echo $supplier['id']; ?>" <?php echo $editing && $edit_stock['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                                            <?php echo $supplier['supplier_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="save_stock" class="btn btn-primary"><?php echo $editing ? 'Update' : 'Save'; ?> Stock</button>
                        <a href="manage_stock.php" class="btn btn-secondary">Cancel</a>
                    </form>
                    <hr>
                <?php endif; ?>
            <?php } ?>

            <!-- Stock List -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Part Number</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Model</th>
                        <th>Cost</th>
                        <th>Selling Price</th>
                        <th>Stock Quantity</th>
                        <th>Supplier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stocks)): ?>
                        <?php foreach ($stocks as $stock) { ?>
                            <tr>
                                <td><?php echo $stock['part_number']; ?></td>
                                <td><?php echo $stock['description']; ?></td>
                                <td><?php echo $stock['category_name']; ?></td>
                                <td><?php echo $stock['model_name']; ?></td>
                                <td><?php echo $stock['cost']; ?></td>
                                <td><?php echo $stock['selling_price']; ?></td>
                                <td><?php echo $stock['stock_quantity']; ?></td>
                                <td><?php echo $stock['supplier_name']; ?></td>
                                <td>
                                    <a href="manage_stock.php?view=<?php echo $stock['id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                        <a href="manage_stock.php?edit=<?php echo $stock['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                        <a href="manage_stock.php?delete=<?php echo $stock['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No stock records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="manage_categories.php" class="btn btn-primary btn-sm">Manage Categories</a>
            <a href="manage_models.php" class="btn btn-primary btn-sm">Manage Models</a>
            <a href="manage_suppliers.php" class="btn btn-primary btn-sm">Manage Suppliers</a>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>