Here's a complete implementation of a simple FIFO queue system using your existing `db.php` connection. I'll provide the code for both the queue operations and a basic Bootstrap interface.

## 1. First, create the queue table (run this once):

```php
<?php
// create_queue_table.php
require_once('./db.php');

try {
    $sql = "CREATE TABLE IF NOT EXISTS fifo_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_data VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_processed BOOLEAN DEFAULT 0,
        position INT DEFAULT 0
    )";
    
    $conn->exec($sql);
    echo "Queue table created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
```

## 2. Queue Operations (add to your existing files or create new ones):

### enqueue.php - Add items to the queue
```php
<?php
require_once('./db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_data'])) {
    try {
        // Get current max position
        $stmt = $conn->query("SELECT IFNULL(MAX(position), 0) FROM fifo_queue");
        $maxPos = $stmt->fetchColumn();
        $newPos = $maxPos + 1;
        
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO fifo_queue (item_data, position) VALUES (?, ?)");
        $stmt->execute([$_POST['item_data'], $newPos]);
        
        header("Location: index.php?message=Item+added+to+queue");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
```

### dequeue.php - Process the next item
```php
<?php
require_once('./db.php');

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Get oldest unprocessed item
    $stmt = $conn->prepare("SELECT * FROM fifo_queue WHERE is_processed = 0 ORDER BY position ASC LIMIT 1 FOR UPDATE");
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // Mark as processed
        $update = $conn->prepare("UPDATE fifo_queue SET is_processed = 1 WHERE id = ?");
        $update->execute([$item['id']]);
        
        $conn->commit();
        header("Location: index.php?processed=" . urlencode($item['item_data']));
    } else {
        $conn->rollBack();
        header("Location: index.php?message=Queue+is+empty");
    }
    exit();
} catch(PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
```

## 3. index.php - The Bootstrap Interface
```php
<?php
require_once('./db.php');

// Get current queue items
$queueItems = [];
try {
    $stmt = $conn->query("SELECT * FROM fifo_queue WHERE is_processed = 0 ORDER BY position ASC");
    $queueItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple FIFO Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Simple FIFO Queue</h1>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['processed'])): ?>
                <div class="alert alert-success">
                    Processed item: <?= htmlspecialchars($_GET['processed']) ?>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">Add to Queue</div>
                <div class="card-body">
                    <form action="enqueue.php" method="post">
                        <div class="input-group">
                            <input type="text" name="item_data" class="form-control" placeholder="Enter item" required>
                            <button type="submit" class="btn btn-primary">Enqueue</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Queue Operations</div>
                <div class="card-body">
                    <a href="dequeue.php" class="btn btn-success mb-3">Dequeue Next Item</a>
                    
                    <h5>Current Queue (<?= count($queueItems) ?> items)</h5>
                    <ul class="list-group">
                        <?php foreach ($queueItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($item['item_data']) ?>
                                <span class="badge bg-primary rounded-pill">#<?= $item['position'] ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($queueItems)): ?>
                            <li class="list-group-item text-muted">Queue is empty</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

## Key Features of This Implementation:

1. **Simple FIFO Logic**: Uses `position` and `is_processed` flags to manage the queue
2. **Transaction Safety**: Uses database transactions for safe dequeuing
3. **Bootstrap UI**: Clean, responsive interface
4. **Uses Your Existing Connection**: Works with your current `db.php` setup
5. **Basic Error Handling**: Simple error messages for debugging

To use this system:
1. Run `create_queue_table.php` once to create the table
2. Use `index.php` as your main interface
3. The system will automatically handle the FIFO logic through the database operations

Would you like me to modify any part of this implementation to better suit your needs?
__________________________________________________________________________________
## Extend this idea to use in sales, perchase and stocks
Here's how to integrate the FIFO system into your `sales_purchases.php` and `sales.php` files for managing inventory:

## 1. First, modify your database structure for inventory tracking:

```sql
ALTER TABLE products ADD COLUMN fifo_queue_id INT DEFAULT NULL;
```

## 2. supplyer_purchases.php (Adding inventory to FIFO queue)

```php
<?php
require_once(__DIR__ . '/../config.php'); // Assuming you're using Solution 3 from previous answer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $purchasePrice = $_POST['purchase_price'];
        
        // Create FIFO queue items for each unit purchased
        for ($i = 0; $i < $quantity; $i++) {
            $stmt = $conn->prepare("INSERT INTO fifo_queue (item_data) VALUES (?)");
            $itemData = json_encode([
                'product_id' => $productId,
                'purchase_price' => $purchasePrice,
                'purchase_date' => date('Y-m-d H:i:s')
            ]);
            $stmt->execute([$itemData]);
            
            $fifoId = $conn->lastInsertId();
            
            // Optional: Link to product table
            $conn->exec("UPDATE products SET fifo_queue_id = $fifoId WHERE id = $productId");
        }
        
        header("Location: sales_purchases.php?success=Inventory+added+using+FIFO");
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Display form and existing inventory...
?>
```

## 3. sales.php (Processing sales with FIFO)

```php
<?php
require_once(__DIR__ . '/../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $productId = $_POST['product_id'];
        $saleQuantity = $_POST['quantity'];
        $salePrice = $_POST['sale_price'];
        
        // Start transaction
        $conn->beginTransaction();
        
        // Get oldest unprocessed items for this product
        $stmt = $conn->prepare("
            SELECT f.id, f.item_data 
            FROM fifo_queue f
            WHERE f.is_processed = 0 
            AND JSON_EXTRACT(f.item_data, '$.product_id') = ?
            ORDER BY f.position ASC 
            LIMIT ?
            FOR UPDATE
        ");
        $stmt->execute([$productId, $saleQuantity]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($items) < $saleQuantity) {
            throw new Exception("Not enough inventory available");
        }
        
        // Process each item
        foreach ($items as $item) {
            $itemData = json_decode($item['item_data'], true);
            
            // Record the sale
            $stmt = $conn->prepare("
                INSERT INTO sales 
                (product_id, quantity, sale_price, purchase_price, profit, fifo_processed) 
                VALUES (?, 1, ?, ?, ?, TRUE)
            ");
            $profit = $salePrice - $itemData['purchase_price'];
            $stmt->execute([
                $productId, 
                $salePrice, 
                $itemData['purchase_price'], 
                $profit
            ]);
            
            // Mark FIFO item as processed
            $conn->exec("UPDATE fifo_queue SET is_processed = 1 WHERE id = {$item['id']}");
        }
        
        $conn->commit();
        header("Location: sales.php?success=Sale+processed+using+FIFO");
    } catch(Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

// Display sales form and history...
?>
```

## 4. Reporting (Add to either file)

```php
// Get current FIFO inventory for a product
function getFifoInventory($productId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count, 
               AVG(JSON_EXTRACT(item_data, '$.purchase_price')) as avg_cost
        FROM fifo_queue
        WHERE is_processed = 0
        AND JSON_EXTRACT(item_data, '$.product_id') = ?
    ");
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get FIFO cost of goods sold
function getFifoCostOfGoodsSold($productId) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT SUM(JSON_EXTRACT(f.item_data, '$.purchase_price')) as total_cost
        FROM fifo_queue f
        JOIN sales s ON f.id = s.fifo_queue_id
        WHERE s.product_id = ?
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchColumn();
}
```

## Key Integration Points:

1. **Purchases**:
   - Each purchased unit gets a FIFO queue entry
   - Store purchase details in JSON format in `item_data`

2. **Sales**:
   - Process oldest unprocessed items first
   - Calculate profit based on original purchase price
   - Mark items as processed when sold

3. **Reporting**:
   - Track current inventory value
   - Calculate accurate cost of goods sold
   - Maintain profit/loss records

Would you like me to adapt any part of this specifically for your existing sales_purchases.php and sales.php file structure?
____________________________________________________________________________________________
____________________________________________________________________________________________
# Simplified FIFO Integration for Your Purchases and Sales System

I'll modify both your `manage_supplier_purchases.php` and `manage_sales.php` to implement a simple FIFO approach using your existing `fifo_queue` table.

## 1. First, modify your `manage_supplier_purchases.php`

Add this right after the stock quantity update in the "Create new supplier purchase entry" section:

```php
// After updating stock quantity in the "Create new supplier purchase entry" section:

// Add to FIFO queue (simple approach - one record per purchase)
try {
    $stmt = $conn->prepare("INSERT INTO fifo_queue 
                          (item_data, position) 
                          VALUES (?, ?)");
    
    // Store purchase details as JSON
    $item_data = json_encode([
        'purchase_id' => $conn->lastInsertId(), // Get the ID of the purchase we just created
        'part_id' => $part_id,
        'quantity' => $quantity,
        'purchase_price' => $cost,
        'purchase_date' => $purchase_date
    ]);
    
    // Get next position in queue
    $maxPos = $conn->query("SELECT IFNULL(MAX(position), 0) FROM fifo_queue")->fetchColumn();
    
    $stmt->execute([$item_data, $maxPos + 1]);
    
} catch (PDOException $e) {
    // If FIFO recording fails, roll back the transaction
    $conn->rollBack();
    die("Error recording FIFO: " . $e->getMessage());
}
```

## 2. Then modify your `manage_sales.php`

Replace the stock deduction logic with FIFO-based deduction:

```php
// In manage_sales.php, replace the stock deduction code with:

// Get all available FIFO items for this stock, oldest first
$stmt = $pdo->prepare("SELECT * FROM fifo_queue 
                      WHERE JSON_EXTRACT(item_data, '$.part_id') = ? 
                      AND is_processed = 0
                      ORDER BY position ASC");
$stmt->execute([$stock_id]);
$fifo_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$remaining_to_sell = $quantity_sold;
$total_cost = 0;

foreach ($fifo_items as $item) {
    if ($remaining_to_sell <= 0) break;
    
    $item_data = json_decode($item['item_data'], true);
    $available_qty = $item_data['quantity'];
    
    $qty_to_use = min($available_qty, $remaining_to_sell);
    
    // Mark as processed (either fully or partially)
    if ($qty_to_use == $available_qty) {
        // Fully consume this batch
        $update = $pdo->prepare("UPDATE fifo_queue SET is_processed = 1 WHERE id = ?");
        $update->execute([$item['id']]);
    } else {
        // Partially consume - update the remaining quantity
        $new_qty = $available_qty - $qty_to_use;
        $item_data['quantity'] = $new_qty;
        
        $update = $pdo->prepare("UPDATE fifo_queue 
                               SET item_data = ? 
                               WHERE id = ?");
        $update->execute([json_encode($item_data), $item['id']]);
    }
    
    // Calculate cost for this portion
    $total_cost += $qty_to_use * $item_data['purchase_price'];
    $remaining_to_sell -= $qty_to_use;
}

if ($remaining_to_sell > 0) {
    $pdo->rollBack();
    die("Error: Not enough stock available in FIFO queue to fulfill sale");
}

// Now update the stock quantity (as before)
$stmt = $pdo->prepare("UPDATE stocks SET stock_quantity = stock_quantity - ? WHERE id=?");
$stmt->execute([$quantity_sold, $stock_id]);

// You might want to store the cost basis in the sale record
$stmt = $pdo->prepare("UPDATE sales SET cost_basis = ? WHERE id = ?");
$stmt->execute([$total_cost, $pdo->lastInsertId()]);
```

## 3. Additional Considerations

1. **For the edit/update functionality** in both files:
   - You'll need to handle FIFO queue updates when quantities change
   - This would involve reversing previous FIFO entries and creating new ones

2. **For the delete functionality**:
   - You'll need to reverse the FIFO processing when undoing a sale

3. **Database Compatibility**:
   - The `JSON_EXTRACT` function works in MySQL 5.7+. For older versions, you'd need to use LIKE queries or store data differently.

## Simplified Alternative

If you want an even simpler approach that just tracks purchase order without cost calculation:

```php
// In manage_supplier_purchases.php (after stock update):
$stmt = $conn->prepare("INSERT INTO fifo_queue (item_data) VALUES (?)");
$stmt->execute(["Purchase ID: " . $conn->lastInsertId() . " | Part: $part_id | Qty: $quantity"]);

// In manage_sales.php:
// Just deduct from stock as before without FIFO cost tracking
```

This would maintain a purchase history but wouldn't do cost calculations. Would you prefer this simpler version?

