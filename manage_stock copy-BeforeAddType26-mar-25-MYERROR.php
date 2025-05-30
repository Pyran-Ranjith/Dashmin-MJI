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
    $oem_number = $_POST['oem_number'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $category_id = $_POST['category_id'];
    $model_id = $_POST['model_id'];
    $brand_id = $_POST['brand_id'];
    $cost = $_POST['cost'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $supplier_id = $_POST['supplier_id'];
    $location_id = $_POST['location_id']; // Add location_id

    try {
        if ($_POST['id']) {
            // Update existing stock
            $id = $_POST['id'];
            $sql = "UPDATE stocks SET part_number=:part_number, oem_number=:oem_number, description=:description, image=:image, 
                    category_id=:category_id, model_id=:model_id, brand_id=:brand_id, cost=:cost, selling_price=:selling_price, 
                    stock_quantity=:stock_quantity, supplier_id=:supplier_id, location_id=:location_id 
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'oem_number' => $oem_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'stock_quantity' => $stock_quantity,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id, // Add location_id
                'id' => $id
            ]);
        } else {
            // Create new stock entry
            $sql = "INSERT INTO stocks (part_number, oem_number, description, image, category_id, model_id, brand_id, cost, selling_price, stock_quantity, supplier_id, location_id)
                    VALUES (:part_number, :oem_number, :description, :image, :category_id, :model_id, :brand_id, :cost, :selling_price, :stock_quantity, :supplier_id, :location_id)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'oem_number' => $oem_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'stock_quantity' => $stock_quantity,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id // Add location_id
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
        // $sql = "DELETE FROM stocks WHERE id=:id";
        $sql = "UPDATE stocks SET  flag='inactive' 
        WHERE id=:id";
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
        , c.category_name, m.model_name, b.brand_name, su.supplier_name, l.location_name 
        FROM stocks s 
        JOIN categories c ON s.category_id = c.id 
        JOIN models m ON s.model_id = m.id 
        JOIN brands b ON s.brand_id = b.id 
        JOIN suppliers su ON s.supplier_id = su.id
        JOIN locations l ON s.location_id = l.id
        LEFT JOIN brands b ON s.brand_id = b.id
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
$filter_oem_number = $_GET['filter_oem_number'] ?? '';
$filter_part_number_omit = $_GET['filter_part_number_omit'] ?? ''; // New filter
$filter_category = $_GET['filter_category'] ?? '';
$filter_model = $_GET['filter_model'] ?? '';
$filter_description = $_GET['filter_description'] ?? '';
$filter_location = $_GET['filter_location'] ?? '';
$filter_brand = $_GET['filter_brand'] ?? '';

// Build the base SQL query
$sql = "SELECT 
s.*
, c.category_name, m.model_name, b.brand_name, su.supplier_name, l.location_name 
FROM stocks s 
JOIN categories c ON s.category_id = c.id 
JOIN models m ON s.model_id = m.id 
JOIN suppliers su ON s.supplier_id = su.id
JOIN locations l ON s.location_id = l.id
LEFT JOIN brands b ON s.brand_id = b.id
WHERE 1 = 1
";

$sql .= " AND s.flag = 'active'";
// Add filters to the SQL query
if (!empty($filter_part_number)) {
    $sql .= " AND s.part_number = :filter_part_number";
}
if (!empty($filter_oem_number)) {
    $sql .= " AND s.oem_number = :filter_oem_number";
}
// if (!empty($filter_part_number_omit)) {
//     $sql .= " AND SUBSTRING(s.part_number, 5) = :filter_part_number_omit"; // Omit first 4 characters
// }
if (!empty($filter_category)) {
    $sql .= " AND s.category_id = :filter_category";
}
if (!empty($filter_model)) {
    $sql .= " AND s.model_id = :filter_model";
}
if (!empty($filter_description)) {
    $sql .= " AND s.description LIKE :filter_description";
}
if (!empty($filter_location)) {
    $sql .= " AND s.location_id = :filter_location";
}
if (!empty($filter_brand)) {
    $sql .= " AND s.brand_id = :filter_brand";
}

// Prepare and execute the filtered query
try {
    $stmt = $conn->prepare($sql);
    if (!empty($filter_part_number)) {
        $stmt->bindValue(':filter_part_number', $filter_part_number);
    }
    if (!empty($filter_oem_number)) {
        $stmt->bindValue(':filter_oem_number', $filter_oem_number);
    }
    // if (!empty($filter_part_number_omit)) {
    //     $stmt->bindValue(':filter_part_number_omit', $filter_part_number_omit);
    // }
    if (!empty($filter_category)) {
        $stmt->bindValue(':filter_category', $filter_category);
    }
    if (!empty($filter_model)) {
        $stmt->bindValue(':filter_model', $filter_model);
    }
    if (!empty($filter_description)) {
        $stmt->bindValue(':filter_description', '%' . $filter_description . '%');
    }
    if (!empty($filter_location)) {
        $stmt->bindValue(':filter_location', $filter_location);
    }
    if (!empty($filter_brand)) {
        $stmt->bindValue(':filter_brand', $filter_brand);
    }
    $stmt->execute();
    $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Fetch categories, models, suppliers, and locations for dropdowns
try {
    $categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
    $models = $conn->query("SELECT * FROM models ORDER BY model_name")->fetchAll(PDO::FETCH_ASSOC);
    $suppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);
    $brands = $conn->query("SELECT * FROM brands WHERE flag = 'active' ORDER BY brand_name")->fetchAll(PDO::FETCH_ASSOC); // Fetch brands
    $locations = $conn->query("SELECT * FROM locations WHERE flag = 'active' ORDER BY location_name")->fetchAll(PDO::FETCH_ASSOC); // Fetch locations
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
    <!-- Add CSS for print-friendly layout and filter area styling -->
    <style>
        /* Style for the filter area */
        .filter-area {
            border: 2px solid #007bff;
            /* Blue border */
            border-radius: 10px;
            /* Rounded corners */
            padding: 20px;
            /* Add some padding */
            background-color: #f8f9fa;
            /* Light background color */
            margin-bottom: 20px;
            /* Space below the filter area */
        }

        /* Style for the filter buttons */
        .filter-area .btn {
            margin-right: 10px;
            /* Space between buttons */
        }

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
                <!-- Filter area with rounded border and colored frame -->
                <div class="filter-area">
                    <form method="GET" action="manage_stock.php">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="filter_part_number"><strong>Part Number</strong></label>
                                <select class="form-control" name="filter_part_number" id="filter_part_number">
                                    <option value="">All</option>
                                    <?php
                                    $part_numbers = $conn->query("SELECT DISTINCT part_number FROM stocks ORDER BY part_number");
                                    foreach ($part_numbers as $part) {
                                        $selected = ($filter_part_number == $part['part_number']) ? 'selected' : '';
                                        echo "<option value='{$part['part_number']}' $selected>{$part['part_number']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_oem_number"><strong>Oem Number</strong></label>
                                <select class="form-control" name="filter_oem_number" id="filter_oem_number">
                                    <option value="">All</option>
                                    <?php
                                    $oem_numbers = $conn->query("SELECT DISTINCT oem_number FROM stocks ORDER BY oem_number");
                                    foreach ($oem_numbers as $oem) {
                                        $selected = ($filter_oem_number == $oem['oem_number']) ? 'selected' : '';
                                        echo "<option value='{$part['oem_number']}' $selected>{$part['oem_number']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- New Filter: Part Number (Omit First 4 Characters) -->
                            <!-- <div class="col-md-3">
                                <label for="filter_part_number_omit"><strong>Oem Number</strong></label>
                                <select class="form-control" name="filter_part_number_omit" id="filter_part_number_omit">
                                    <option value="">All</option>
                                    <?php
                                    // $part_numbers_omit = $conn->query("SELECT DISTINCT oem_number FROM stocks ORDER BY oem_number");
                                    // foreach ($part_numbers_omit as $part) {
                                    //     $part_omit = $part['oemt_number']; 
                                    //     $selected = ($_GET['filter_part_number_omit'] ?? '') === $part_omit ? 'selected' : '';
                                    //     echo "<option value='{$part_omit}' $selected>{$part_omit}</option>";
                                    // }
                                    ?>
                                </select>
                            </div>  -->
                            <div class="col-md-3">
                                <label for="filter_category"><strong>Category</strong></label>
                                <select class="form-control" name="filter_category" id="filter_category">
                                    <option value="">All</option>
                                    <?php foreach ($categories as $category) {
                                        $selected = ($filter_category == $category['id']) ? 'selected' : '';
                                        echo "<option value='{$category['id']}' $selected>{$category['category_name']}</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_model"><strong>Model</strong></label>
                                <select class="form-control" name="filter_model" id="filter_model">
                                    <option value="">All</option>
                                    <?php foreach ($models as $model) {
                                        $selected = ($filter_model == $model['id']) ? 'selected' : '';
                                        echo "<option value='{$model['id']}' $selected>{$model['model_name']}</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_model"><strong>Brand</strong></label>
                                <select class="form-control" name="filter_brand" id="filter_brand">
                                    <option value="">All</option>
                                    <?php foreach ($brands as $brand) {
                                        $selected = ($filter_brand == $brand['id']) ? 'selected' : '';
                                        echo "<option value='{$brand['id']}' $selected>{$brand['brand_name']}</option>";
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <label for="filter_description"><strong>Description</strong></label>
                                <input type="text" class="form-control" name="filter_description" id="filter_description" value="<?php echo $filter_description; ?>" placeholder="Search description">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_location"><strong>Location</strong></label>
                                <select class="form-control" name="filter_location" id="filter_location">
                                    <option value="">All</option>
                                    <?php foreach ($locations as $location) {
                                        $selected = ($filter_location == $location['id']) ? 'selected' : '';
                                        echo "<option value='{$location['id']}' $selected>{$location['location_name']}</option>";
                                    } ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="manage_stock.php" class="btn btn-secondary">Reset Filters</a>
                            </div>
                        </div>
                    </form>
                </div>
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
                                        <th>Oem Number:</th>
                                        <td><?php echo $view_stock['oem_number']; ?></td>
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
                                        <th>Brand:</th>
                                        <td><?php echo $view_stock['brand_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Unit Cost:</th>
                                        <td><?php echo number_format($view_stock['cost'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Selling Price:</th>
                                        <td><?php echo number_format($view_stock['selling_price'], 2); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Stock Quantity:</th>
                                        <td><?php echo $view_stock['stock_quantity']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Supplier:</th>
                                        <td><?php echo $view_stock['supplier_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td><?php echo $view_stock['location_name']; ?></td>
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
                            <!-- <div class="card-header">
                                Stock Details (Add/Update)
                            </div> -->
                            <h4>Stock Details (Create/Update)</h4>

                        </div>
                        <input type="hidden" name="id" value="<?php echo $editing ? $edit_stock['id'] : ''; ?>">
                        <div class="form-group row mb-3">
                            <label for="part_number" class="col-sm-2 col-form-label"><strong>Part Number</strong></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="part_number" placeholder="Enter part number" value="<?php echo $editing ? $edit_stock['part_number'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="oem_number" class="col-sm-2 col-form-label"><strong>Oem Number</strong></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="oem_number" placeholder="Enter oem number" value="<?php echo $editing ? $edit_stock['oem_number'] : ''; ?>" required>
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
                            <label for="brand_id" class="col-sm-2 col-form-label"><strong>Brand</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="brand_id" id="brand_id" required>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo $brand['brand_name']; ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label for="cost" class="col-sm-2 col-form-label"><strong>Unit Cost</strong></label>
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
                        <div class="form-group row mb-3">
                            <label for="location_id" class="col-sm-2 col-form-label"><strong>Location</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control" name="location_id" required>
                                    <option value="" disabled selected>Select a Location</option>
                                    <?php foreach ($locations as $location) { ?>
                                        <option value="<?php echo $location['id']; ?>" <?php echo $editing && $edit_stock['location_id'] == $location['id'] ? 'selected' : ''; ?>>
                                            <?php echo $location['location_name']; ?>
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
                        <th>Brand</th>
                        <th>Unit Cost</th>
                        <th>Selling Price</th>
                        <th>Stock Quantity</th>
                        <th>Supplier</th>
                        <th>Location</th>
                        <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                            <th>#</th>
                            <th>plp</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stocks)): ?>
                        <?php foreach ($stocks as $stock) { ?>
                            <tr>
                                <td><?php echo $stock['part_number']; ?></td>
                                <td><?php echo $stock['oem_number']; ?></td>
                                <td><?php echo $stock['description']; ?></td>
                                <td><?php echo $stock['category_name']; ?></td>
                                <td><?php echo $stock['model_name']; ?></td>
                                <td><?php echo $stock['brand_name']; ?></td>
                                <td><?php echo number_format($stock['cost'], 2); ?></td>
                                <td><?php echo number_format($stock['selling_price'], 2); ?></td>
                                <td><?php echo $stock['stock_quantity']; ?></td>
                                <td><?php echo $stock['supplier_name']; ?></td>
                                <td><?php echo $stock['location_name']; ?></td>
                                <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                    <td><?php echo $stock['id']; ?></td>
                                    <td><?php echo $stock['pricelistpage']; ?></td>
                                <?php endif; ?>
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
                            <td colspan="10" class="text-center">No stock records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <a href="manage_categories.php" class="btn btn-primary btn-sm">Manage Categories</a>
            <a href="manage_models.php" class="btn btn-primary btn-sm">Manage Models</a>
            <a href="manage_suppliers.php" class="btn btn-primary btn-sm">Manage Suppliers</a>
            <a href="manage_stock.php" class="btn btn-success btn-sm">Refresh</a>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>

</html>