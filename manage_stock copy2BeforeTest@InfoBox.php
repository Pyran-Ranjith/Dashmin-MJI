<?php
ob_start();
include('db.php');
include('header.php');

// Handle Create/Update actions
if (isset($_POST['save_stock'])) { //user pressed 'Save Stock' button
    $part_number = $_POST['part_number'];
    $description = $_POST['description'];
    $image = $_POST['image']; // Store image path or URL
    $category_id = $_POST['category_id'];
    $model_id = $_POST['model_id'];
    $cost = $_POST['cost'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $supplier_id = $_POST['supplier_id'];

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
    } else { //user pressed view, edit or delete button
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
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM stocks WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);
}

// Handle view action
$view_stocks = null;
if (isset($_GET['view'])) {
    $id = $_GET['view'];
    // echo "id = ".$id;

    $sql = "
    SELECT s.*
    , c.category_name, m.model_name, su.supplier_name 
    FROM stocks s 
    JOIN categories c ON s.category_id = c.id 
    JOIN models m ON s.model_id = m.id 
    JOIN suppliers su ON s.supplier_id = su.id
    WHERE s.id=$id
    ";

    $viewstocks_result = $conn->query($sql);
    $view_stocks = $viewstocks_result->fetchAll(PDO::FETCH_ASSOC);
}

/* Stock Partnumber Filter Result */
// $stock_result = $conn->query("SELECT * FROM stock WHERE flag = 'active'");
$st_pn_fi_re = $conn->query("SELECT * FROM stocks WHERE flag = 'active'");





// Fetch stocks, categories, and suppliers
$sql = "SELECT 
s.*
 , c.category_name, m.model_name, su.supplier_name 
 FROM stocks s 
 JOIN categories c ON s.category_id = c.id 
 JOIN models m ON s.model_id = m.id 
 JOIN suppliers su ON s.supplier_id = su.id
";
// echo $sql;
$stocks_result = $conn->query($sql);
$stocks = $stocks_result->fetchAll(PDO::FETCH_ASSOC);

$categories_result = $conn->query("SELECT * FROM categories");
$categories = $categories_result->fetchAll(PDO::FETCH_ASSOC);

$models_result = $conn->query("SELECT * FROM models");
$models = $models_result->fetchAll(PDO::FETCH_ASSOC);

$suppliers_result = $conn->query("SELECT * FROM suppliers");
$suppliers = $suppliers_result->fetchAll(PDO::FETCH_ASSOC);

// If editing, fetch the stock data
$editing = false;
$view = false;
if (isset($_GET['view'])) {
    $view = true;
}

if (isset($_GET['edit']) or isset($_GET['view'])) {
    if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
    };
    if (isset($_GET['view'])) {
        $id = $_GET['view'];
    };
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM stocks WHERE id=:id");
    $stmt->execute(['id' => $id]);
    $edit_stock = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<style>
    /* Custom Styling */
    .info-box {
    /* My */
        /* background: linear-gradient(135deg,rgb(168, 136, 203), #2575fc); */
        /* background: linear-gradient(135deg, rgb(89, 117, 227), rgb(87, 103, 244));
        color: #fff;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); */
    /* @ */
    background: linear-gradient(135deg, rgb(89, 117, 227), rgb(87, 103, 244));
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            max-width: 800px;

    }

    .info-box h3 {
    /* My */
        /* font-weight: bold;
        text-transform: uppercase; */
    /* @ */
}            border: 2px solid #fff;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            appearance: menulist;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            padding: 10px;
            border-radius: 5px;
            width: 100%;


    .info-box .form-control {
        border: 2px solid #fff;
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .info-box .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .info-box .form-control:focus {
        /* My */
        background-color: rgba(255, 255, 255, 0.3);
        border-color: #fff;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        /* @ */
        outline: none;
    }

    .info-box button {
        /* My */
        background-color:rgba(245, 151, 117, 0.94);
        border: none;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background-color 0.3s ease;
        /* @ */
        padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
    }

    .info-box button:hover {
        background-color: #e56740;
    }

/* @ */
.info-box a.btn {
            background-color: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
        }

        .info-box a.btn:hover {
            background-color: #5a6268;
        }
</style>

<div class="card">
    <div class="card-header">
        <h2>Manage Stock</h2>
    </div>

    <div class="card-body">
        <!-- View Stock Details in Card -->
        <?php if ($view_stocks) { ?>
            <div class="card">
                <div class="card-header">
                    Stock Details (View Only)
                </div>
                <div class="card-body">
                </div>
            </div>

            <div class="card-body">
                <?php foreach ($view_stocks as $view_stock) { ?>
                    <!-- <table class="table table-bordered"> -->
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
                <?php } ?>
                <a href="manage_stock.php" class="btn btn-secondary">Back to List</a>
            </div>
            <hr>

        <?php } else { ?>


            <div>
                <!-- Fltering -->
                <div class="info-box">
                    <form method="GET" action="manage_stock.php" id="filter">
                        <div class="row">

                            <!-- Stock Partnumber Filter Result -->
                            <!-- <div class="form-group"> //In column-->
                            <div class="col-md-3"> <!-- //In row-->
                                <label><strong>Flter by Part Number</strong></label>
                                <select class="form-control" name="filter_part_number">
                                    <option value="">Select Part number</option>
                                    <?php while ($st_pn_fi = $st_pn_fi_re->fetch(PDO::FETCH_ASSOC)) { ?>
                                        <option value="<?php echo $st_pn_fi['part_number']; ?>" <?php //if ($roles['id'] == $role_id) echo 'selected'; 
                                                                                                ?>>
                                            <?php echo $st_pn_fi['part_number']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" name="filter" class="btn btn-primary">Filter</button>
                                <a href="manage_stock.php" class="btn btn-secondary">Reset</a>
                            </div>

                        </div>
                    </form>
                </div>
                <br>
            </div>


            <!-- Form to add/update stock -->
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
                        <input type="text" class="form-control" name="part_number" placeholder="Enter part number"
                            value="<?php echo $editing ? $edit_stock['part_number'] : ''; ?>" required>
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
        <?php } ?>

        <!-- Stock List -->
        <table class="table">
            <thead>
                <tr>
                    <th>Part Number</th>
                    <th>Description</th>
                    <!-- <th>Image</th> -->
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
                <?php foreach ($stocks as $stock) { ?>
                    <tr>
                        <td><?php echo $stock['part_number']; ?></td>
                        <td><?php echo $stock['description']; ?></td>
                        <!-- <td><img src="<?php echo $stock['image']; ?>" alt="Image" width="50"></td> -->
                        <td><?php echo $stock['category_name']; ?></td>
                        <td><?php echo $stock['model_name']; ?></td>
                        <td><?php echo $stock['cost']; ?></td>
                        <td><?php echo $stock['selling_price']; ?></td>
                        <td><?php echo $stock['stock_quantity']; ?></td>
                        <td><?php echo $stock['supplier_name']; ?></td>
                        <td>
                            <a href="manage_stock.php?view=<?php echo $stock['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="manage_stock.php?edit=<?php echo $stock['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="manage_stock.php?delete=<?php echo $stock['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="manage_categories.php" class="btn btn-primary btn-sm">Manage Catogries</a>
        <a href="manage_models.php" class="btn btn-primary btn-sm">Manage Models</a>
        <a href="manage_suppliers.php" class="btn btn-primary btn-sm">Manage Suppliers</a>

    </div>
</div>


<?php include('footer.php'); ?>