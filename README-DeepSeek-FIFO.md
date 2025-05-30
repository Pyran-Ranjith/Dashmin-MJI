

________________________________________________________________________________________
# Implementing First-Come-First-Out (FIFO) in Spare Parts Management

Great to hear about your progress on the Spare Parts Management System! Implementing FIFO (First-In-First-Out) inventory management is a common requirement, especially for parts with expiration dates or where stock rotation is important.

## Tables Affected by FIFO Implementation

You'll likely need to modify these tables:

1. **Inventory Receipts Table** (or Purchase Orders)
   - Add fields like `receipt_date`, `batch_number`, `expiry_date` (if applicable)
   - Track quantity received per batch

2. **Inventory Stock Table**
   - May need restructuring to track batches/lots separately

3. **Sales/Issues Table**
   - Need to reference which batches were used for each sale

## Implementation Approaches

### Option 1: Batch Tracking (Recommended)

1. **Add a Batch/Lot table**:
   ```
   CREATE TABLE inventory_batches (
     batch_id INT PRIMARY KEY,
     part_id INT FOREIGN KEY REFERENCES parts(part_id),
     receipt_date DATETIME,
     expiry_date DATETIME NULL,
     quantity_received INT,
     quantity_remaining INT,
     unit_cost DECIMAL(10,2),
     supplier_id INT FOREIGN KEY REFERENCES suppliers(supplier_id)
   );
   ```

2. **Modify your sales process**:
   - When selling, query batches by receipt date (oldest first)
   - Deduct from oldest batches first
   - Update `quantity_remaining` in the batch table

### Option 2: Simplified FIFO without Batch Tracking

If you don't need full batch tracking:

1. Add `receipt_date` to your inventory table
2. When querying stock, always ORDER BY `receipt_date` ASC
3. Update your sales procedure to mark items from oldest stock first

## Sample SQL for FIFO Allocation

```sql
-- Get available batches for a part (oldest first)
SELECT batch_id, quantity_remaining 
FROM inventory_batches
WHERE part_id = @PartID AND quantity_remaining > 0
ORDER BY receipt_date ASC;

-- When processing a sale:
BEGIN TRANSACTION;

-- Find batches to fulfill the order
DECLARE @QuantityNeeded INT = 10;
DECLARE @BatchID INT;
DECLARE @Available INT;

DECLARE batch_cursor CURSOR FOR
SELECT batch_id, quantity_remaining 
FROM inventory_batches
WHERE part_id = @PartID AND quantity_remaining > 0
ORDER BY receipt_date ASC;

OPEN batch_cursor;
FETCH NEXT FROM batch_cursor INTO @BatchID, @Available;

WHILE @QuantityNeeded > 0 AND @@FETCH_STATUS = 0
BEGIN
    IF @Available >= @QuantityNeeded
    BEGIN
        UPDATE inventory_batches
        SET quantity_remaining = quantity_remaining - @QuantityNeeded
        WHERE batch_id = @BatchID;
        
        INSERT INTO sales_batch_allocation (sale_id, batch_id, quantity)
        VALUES (@SaleID, @BatchID, @QuantityNeeded);
        
        SET @QuantityNeeded = 0;
    END
    ELSE
    BEGIN
        UPDATE inventory_batches
        SET quantity_remaining = 0
        WHERE batch_id = @BatchID;
        
        INSERT INTO sales_batch_allocation (sale_id, batch_id, quantity)
        VALUES (@SaleID, @BatchID, @Available);
        
        SET @QuantityNeeded = @QuantityNeeded - @Available;
    END
    
    FETCH NEXT FROM batch_cursor INTO @BatchID, @Available;
END

CLOSE batch_cursor;
DEALLOCATE batch_cursor;

COMMIT TRANSACTION;
```

## Reporting Considerations

You'll want to add reports showing:
- Aging inventory (items in stock longest)
- Batch expiration alerts (if tracking expiry dates)
- FIFO valuation of remaining inventory

Would you like me to elaborate on any specific aspect of this FIFO implementation for your system?
___________________________________________________________________________________________
# FIFO Implementation for Your Spare Parts Management System

Based on your current database schema, here's how to implement First-In-First-Out (FIFO) inventory management:

## Affected Tables

You'll need to modify these existing tables and add new ones:

1. **New Tables Needed**:
   - `inventory_batches` - To track individual batches of stock
   - `sales_batch_allocation` - To track which batches were used in each sale

2. **Modified Tables**:
   - `stocks` - Will become a summary table (quantity will be calculated from batches)
   - `supplier_purchases` - Needs to create batch records when items are received
   - `sales` - Needs to reference batch allocations

## Implementation Steps

### 1. Create the Batch Tracking Table

```sql
CREATE TABLE `inventory_batches` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `stock_id` INT NOT NULL,
  `supplier_purchase_id` INT,
  `batch_number` VARCHAR(50),
  `receipt_date` DATETIME NOT NULL,
  `expiry_date` DATETIME NULL,
  `quantity_received` INT NOT NULL,
  `quantity_remaining` INT NOT NULL,
  `unit_cost` DECIMAL(10,2) NOT NULL,
  `selling_price` DECIMAL(10,2) NOT NULL,
  `location_id` INT NOT NULL,
  `flag` ENUM('active','inactive') DEFAULT 'active',
  FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`),
  FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases`(`id`),
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`)
);
```

### 2. Create the Sales Batch Allocation Table

```sql
CREATE TABLE `sales_batch_allocation` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `sale_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`),
  FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches`(`id`)
);
```

### 3. Modify the Supplier Purchases Process

When receiving inventory, create batch records:

```php
// After inserting into supplier_purchases:
$purchaseId = $db->lastInsertId();
$batchSql = "INSERT INTO inventory_batches 
             (stock_id, supplier_purchase_id, receipt_date, quantity_received, 
              quantity_remaining, unit_cost, selling_price, location_id)
             VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
$stmt = $db->prepare($batchSql);
$stmt->execute([
    $stockId, $purchaseId, $quantity, $quantity, 
    $unitCost, $sellingPrice, $locationId
]);
```

### 4. Modify the Sales Process for FIFO

When selling items, allocate from oldest batches first:

```php
function sellItems($stockId, $quantity, $saleId) {
    global $db;
    
    // Get available batches (oldest first)
    $batchQuery = "SELECT id, quantity_remaining 
                   FROM inventory_batches 
                   WHERE stock_id = ? AND quantity_remaining > 0
                   ORDER BY receipt_date ASC";
    $stmt = $db->prepare($batchQuery);
    $stmt->execute([$stockId]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $remainingQty = $quantity;
    
    foreach ($batches as $batch) {
        if ($remainingQty <= 0) break;
        
        $allocated = min($remainingQty, $batch['quantity_remaining']);
        
        // Deduct from batch
        $updateBatch = "UPDATE inventory_batches 
                        SET quantity_remaining = quantity_remaining - ?
                        WHERE id = ?";
        $db->prepare($updateBatch)->execute([$allocated, $batch['id']]);
        
        // Record allocation
        $insertAlloc = "INSERT INTO sales_batch_allocation 
                        (sale_id, batch_id, quantity, unit_price)
                        VALUES (?, ?, ?, 
                        (SELECT selling_price FROM inventory_batches WHERE id = ?))";
        $db->prepare($insertAlloc)->execute([
            $saleId, $batch['id'], $allocated, $batch['id']
        ]);
        
        $remainingQty -= $allocated;
    }
    
    if ($remainingQty > 0) {
        // Handle case where not enough stock available
        throw new Exception("Insufficient stock available");
    }
}
```

### 5. Update Stock Quantity Calculation

Modify how you calculate current stock levels - it should now be the sum of remaining quantities from all active batches:

```sql
SELECT SUM(quantity_remaining) as total_stock 
FROM inventory_batches 
WHERE stock_id = ? AND flag = 'active'
```

## Reporting Changes

You'll want to add these reports:

1. **Aging Inventory Report**:
   ```sql
   SELECT s.part_number, s.description, 
          ib.receipt_date, DATEDIFF(NOW(), ib.receipt_date) as days_in_stock,
          ib.quantity_remaining
   FROM inventory_batches ib
   JOIN stocks s ON ib.stock_id = s.id
   WHERE ib.quantity_remaining > 0
   ORDER BY ib.receipt_date ASC;
   ```

2. **Batch Expiration Alerts** (if tracking expiry dates):
   ```sql
   SELECT s.part_number, ib.batch_number, ib.expiry_date,
          DATEDIFF(ib.expiry_date, NOW()) as days_remaining
   FROM inventory_batches ib
   JOIN stocks s ON ib.stock_id = s.id
   WHERE ib.expiry_date IS NOT NULL 
   AND ib.expiry_date < DATE_ADD(NOW(), INTERVAL 30 DAY)
   AND ib.quantity_remaining > 0;
   ```

## Migration Considerations

For your existing data, you'll need to:

1. Create initial batch records for all current inventory
2. Update your application code to use the new batch-based system
3. Modify all reports to work with the new structure

Would you like me to provide more specific code samples for any of these components or explain any part in more detail?
________________________________________________________________________________________
# Detailed FIFO Implementation Guide

I'll provide more specific code samples and implementation details for your spare parts management system.

## 1. Database Schema Updates

### Complete Batch Tables with Additional Fields

```sql
-- Inventory batches table with more detailed fields
CREATE TABLE `inventory_batches` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `stock_id` INT NOT NULL,
  `supplier_purchase_id` INT,
  `batch_number` VARCHAR(50) NOT NULL,
  `receipt_date` DATETIME NOT NULL,
  `expiry_date` DATETIME NULL,
  `manufacture_date` DATETIME NULL,
  `quantity_received` INT NOT NULL,
  `quantity_remaining` INT NOT NULL,
  `unit_cost` DECIMAL(10,2) NOT NULL,
  `selling_price` DECIMAL(10,2) NOT NULL,
  `location_id` INT NOT NULL,
  `supplier_id` INT NOT NULL,
  `notes` TEXT NULL,
  `flag` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`),
  FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases`(`id`),
  FOREIGN KEY (`location_id`) REFERENCES `locations`(`id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`)
);

-- Enhanced sales batch allocation table
CREATE TABLE `sales_batch_allocation` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `sale_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `allocated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`),
  FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches`(`id`)
);
```

## 2. Complete PHP Implementation

### Purchase Receiving Function

```php
function receivePurchase($supplierId, $stockId, $quantity, $unitCost, $sellingPrice, $locationId, $batchNumber = null, $expiryDate = null) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // 1. Record the supplier purchase
        $purchaseSql = "INSERT INTO supplier_purchases 
                       (supplier_id, part_id, quantity, cost, purchase_date)
                       VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($purchaseSql);
        $stmt->execute([$supplierId, $stockId, $quantity, $unitCost]);
        $purchaseId = $db->lastInsertId();
        
        // 2. Create inventory batch
        $batchNumber = $batchNumber ?? generateBatchNumber($stockId);
        
        $batchSql = "INSERT INTO inventory_batches 
                    (stock_id, supplier_purchase_id, batch_number, receipt_date,
                     expiry_date, quantity_received, quantity_remaining,
                     unit_cost, selling_price, location_id, supplier_id)
                    VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($batchSql);
        $stmt->execute([
            $stockId, $purchaseId, $batchNumber, $expiryDate,
            $quantity, $quantity, $unitCost, $sellingPrice, 
            $locationId, $supplierId
        ]);
        
        // 3. Update stock summary (optional - could be a view instead)
        updateStockSummary($stockId);
        
        $db->commit();
        return ['success' => true, 'purchase_id' => $purchaseId];
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function generateBatchNumber($stockId) {
    return 'BATCH-' . $stockId . '-' . date('Ymd-His');
}
```

### FIFO Sales Processing Function

```php
function processSale($saleData) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // 1. Create the sale record
        $saleSql = "INSERT INTO sales 
                   (stock_id, quantity_sold, selling_price, total_price, 
                    sale_date, customer_contact, customer_id)
                   VALUES (?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $db->prepare($saleSql);
        $stmt->execute([
            $saleData['stock_id'], 
            $saleData['quantity'],
            $saleData['selling_price'],
            $saleData['total_price'],
            $saleData['customer_contact'],
            $saleData['customer_id']
        ]);
        $saleId = $db->lastInsertId();
        
        // 2. Allocate stock from batches (FIFO)
        $remainingQty = $saleData['quantity'];
        $batches = getAvailableBatches($saleData['stock_id'], $saleData['location_id']);
        
        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;
            
            $allocated = min($remainingQty, $batch['quantity_remaining']);
            
            // Deduct from batch
            $updateBatch = "UPDATE inventory_batches 
                           SET quantity_remaining = quantity_remaining - ?
                           WHERE id = ?";
            $db->prepare($updateBatch)->execute([$allocated, $batch['id']]);
            
            // Record allocation
            $allocSql = "INSERT INTO sales_batch_allocation
                         (sale_id, batch_id, quantity, unit_price, total_price)
                         VALUES (?, ?, ?, ?, ?)";
            $unitPrice = $batch['selling_price']; // or use sale price if different
            $totalPrice = $unitPrice * $allocated;
            $db->prepare($allocSql)->execute([
                $saleId, $batch['id'], $allocated, $unitPrice, $totalPrice
            ]);
            
            $remainingQty -= $allocated;
        }
        
        if ($remainingQty > 0) {
            throw new Exception("Only " . ($saleData['quantity'] - $remainingQty) . 
                               " of " . $saleData['quantity'] . " could be allocated.");
        }
        
        // 3. Update stock summary
        updateStockSummary($saleData['stock_id']);
        
        $db->commit();
        return ['success' => true, 'sale_id' => $saleId];
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function getAvailableBatches($stockId, $locationId) {
    global $db;
    
    $sql = "SELECT id, quantity_remaining, selling_price
            FROM inventory_batches
            WHERE stock_id = ? 
            AND location_id = ?
            AND quantity_remaining > 0
            AND flag = 'active'
            ORDER BY receipt_date ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$stockId, $locationId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateStockSummary($stockId) {
    global $db;
    
    // Calculate current stock from batches
    $sql = "SELECT SUM(quantity_remaining) as total_stock,
                   AVG(unit_cost) as avg_cost,
                   MIN(selling_price) as min_price,
                   MAX(selling_price) as max_price
            FROM inventory_batches
            WHERE stock_id = ?
            AND flag = 'active'";
    $stmt = $db->prepare($sql);
    $stmt->execute([$stockId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update stocks table
    $updateSql = "UPDATE stocks
                 SET stock_quantity = ?,
                     cost = ?,
                     selling_price = ?
                 WHERE id = ?";
    $db->prepare($updateSql)->execute([
        $summary['total_stock'] ?? 0,
        $summary['avg_cost'] ?? 0,
        $summary['min_price'] ?? 0, // or max_price or your pricing logic
        $stockId
    ]);
}
```

### 3. Data Migration Script

For your existing inventory, you'll need to create initial batch records:

```php
function migrateExistingInventory() {
    global $db;
    
    try {
        $db->beginTransaction();
        
        // Get all current stock items
        $sql = "SELECT id, stock_quantity, cost, selling_price, location_id 
                FROM stocks 
                WHERE stock_quantity > 0";
        $stmt = $db->query($sql);
        $stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($stocks as $stock) {
            // Create a batch for existing inventory
            $batchSql = "INSERT INTO inventory_batches
                        (stock_id, batch_number, receipt_date,
                         quantity_received, quantity_remaining,
                         unit_cost, selling_price, location_id,
                         supplier_id, notes)
                        VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($batchSql);
            $stmt->execute([
                $stock['id'],
                'MIGRATED-' . $stock['id'],
                $stock['stock_quantity'],
                $stock['stock_quantity'],
                $stock['cost'],
                $stock['selling_price'],
                $stock['location_id'],
                1, // Default supplier ID
                'Migrated from existing inventory'
            ]);
        }
        
        $db->commit();
        return ['success' => true, 'migrated' => count($stocks)];
    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```

## 4. Reporting Queries

### Current Inventory with Aging

```sql
SELECT 
    s.id,
    s.part_number,
    s.description,
    b.brand_name,
    c.category_name,
    SUM(ib.quantity_remaining) as total_quantity,
    MIN(ib.receipt_date) as oldest_batch_date,
    DATEDIFF(NOW(), MIN(ib.receipt_date)) as days_in_stock_oldest,
    AVG(ib.unit_cost) as avg_cost,
    MIN(ib.selling_price) as min_price,
    MAX(ib.selling_price) as max_price
FROM 
    stocks s
JOIN 
    inventory_batches ib ON s.id = ib.stock_id
LEFT JOIN 
    brands b ON s.brand_id = b.id
LEFT JOIN 
    categories c ON s.category_id = c.id
WHERE 
    ib.quantity_remaining > 0
    AND ib.flag = 'active'
GROUP BY 
    s.id, s.part_number, s.description, b.brand_name, c.category_name
ORDER BY 
    days_in_stock_oldest DESC;
```

### Batch Expiration Report

```sql
SELECT 
    s.part_number,
    s.description,
    ib.batch_number,
    ib.receipt_date,
    ib.expiry_date,
    ib.quantity_remaining,
    DATEDIFF(ib.expiry_date, NOW()) as days_until_expiry,
    l.location_name,
    sup.supplier_name
FROM 
    inventory_batches ib
JOIN 
    stocks s ON ib.stock_id = s.id
JOIN 
    locations l ON ib.location_id = l.id
JOIN 
    suppliers sup ON ib.supplier_id = sup.id
WHERE 
    ib.expiry_date IS NOT NULL
    AND ib.quantity_remaining > 0
    AND ib.flag = 'active'
    AND ib.expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
ORDER BY 
    ib.expiry_date ASC;
```

### Sales by Batch Report

```sql
SELECT 
    s.invoice_number,
    c.first_name,
    c.last_name,
    st.part_number,
    st.description,
    ib.batch_number,
    ib.receipt_date,
    sba.quantity,
    sba.unit_price,
    sba.total_price,
    s.sale_date
FROM 
    sales_batch_allocation sba
JOIN 
    sales sa ON sba.sale_id = sa.id
JOIN 
    invoices s ON sa.invoice_id = s.id
JOIN 
    customers c ON s.customer_id = c.id
JOIN 
    inventory_batches ib ON sba.batch_id = ib.id
JOIN 
    stocks st ON ib.stock_id = st.id
WHERE 
    sa.sale_date BETWEEN :start_date AND :end_date
ORDER BY 
    sa.sale_date DESC, s.invoice_number;
```

## 5. User Interface Integration

### Purchase Receiving Form

```html
<div class="card">
    <div class="card-header">
        <h4>Receive Inventory</h4>
    </div>
    <div class="card-body">
        <form id="receiveForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Supplier</label>
                        <select class="form-control" name="supplier_id" required>
                            <?php 
                            $suppliers = $db->query("SELECT id, supplier_name FROM suppliers WHERE flag='active'");
                            foreach ($suppliers as $sup) {
                                echo "<option value='{$sup['id']}'>{$sup['supplier_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Part Number</label>
                        <select class="form-control select2" name="stock_id" required>
                            <option value="">Select Part</option>
                            <?php 
                            $stocks = $db->query("SELECT id, part_number, description FROM stocks WHERE flag='active'");
                            foreach ($stocks as $stock) {
                                echo "<option value='{$stock['id']}'>
                                    {$stock['part_number']} - {$stock['description']}
                                </option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Batch Number</label>
                        <input type="text" class="form-control" name="batch_number" 
                               placeholder="Auto-generated if empty">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" class="form-control" name="quantity" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label>Unit Cost</label>
                        <input type="number" class="form-control" name="unit_cost" required step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Selling Price</label>
                        <input type="number" class="form-control" name="selling_price" required step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date">
                    </div>
                    
                    <div class="form-group">
                        <label>Location</label>
                        <select class="form-control" name="location_id" required>
                            <?php 
                            $locations = $db->query("SELECT id, location_name FROM locations WHERE flag='active'");
                            foreach ($locations as $loc) {
                                echo "<option value='{$loc['id']}'>{$loc['location_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Receive Inventory</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
    
    $('#receiveForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'api/receive_inventory.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success('Inventory received successfully!');
                    // Reset form or redirect
                } else {
                    toastr.error('Error: ' + response.error);
                }
            }
        });
    });
});
</script>
```

### Sales Form with FIFO Allocation

```html
<div class="card">
    <div class="card-header">
        <h4>New Sale</h4>
    </div>
    <div class="card-body">
        <form id="saleForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Customer</label>
                        <select class="form-control select2" name="customer_id" required>
                            <option value="">Select Customer</option>
                            <?php 
                            $customers = $db->query("SELECT id, first_name, last_name FROM customers WHERE flag='active'");
                            foreach ($customers as $cust) {
                                echo "<option value='{$cust['id']}'>
                                    {$cust['first_name']} {$cust['last_name']}
                                </option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Part Number</label>
                        <select class="form-control select2" name="stock_id" required id="stockSelect">
                            <option value="">Select Part</option>
                            <?php 
                            $stocks = $db->query("SELECT s.id, s.part_number, s.description, l.location_name 
                                                 FROM stocks s
                                                 JOIN locations l ON s.location_id = l.id
                                                 WHERE s.flag='active' AND s.stock_quantity > 0");
                            foreach ($stocks as $stock) {
                                echo "<option value='{$stock['id']}' data-location='{$stock['location_id']}'>
                                    {$stock['part_number']} - {$stock['description']} ({$stock['location_name']})
                                </option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Available Quantity</label>
                        <input type="text" class="form-control" id="availableQty" readonly>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Quantity to Sell</label>
                        <input type="number" class="form-control" name="quantity" required min="1" id="quantity">
                    </div>
                    
                    <div class="form-group">
                        <label>Unit Price</label>
                        <input type="number" class="form-control" name="selling_price" required step="0.01" min="0" id="unitPrice">
                    </div>
                    
                    <div class="form-group">
                        <label>Total Price</label>
                        <input type="number" class="form-control" name="total_price" readonly id="totalPrice">
                    </div>
                    
                    <div class="form-group">
                        <label>Batches Available (FIFO)</label>
                        <div id="batchList" class="border p-2" style="max-height: 150px; overflow-y: auto;">
                            <p class="text-muted">Select a part to view available batches</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Process Sale</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.select2').select2();
    
    // Update available quantity and batches when part is selected
    $('#stockSelect').change(function() {
        const stockId = $(this).val();
        if (!stockId) return;
        
        const locationId = $(this).find(':selected').data('location');
        
        $.get('api/get_stock_info.php', { stock_id: stockId, location_id: locationId }, function(response) {
            $('#availableQty').val(response.available_quantity);
            $('#unitPrice').val(response.selling_price);
            
            // Display batches
            let batchHtml = '';
            if (response.batches.length > 0) {
                batchHtml = '<table class="table table-sm">';
                batchHtml += '<tr><th>Batch</th><th>Received</th><th>Available</th><th>Price</th></tr>';
                
                response.batches.forEach(batch => {
                    batchHtml += `<tr>
                        <td>${batch.batch_number}</td>
                        <td>${batch.receipt_date}</td>
                        <td>${batch.quantity_remaining}</td>
                        <td>${batch.selling_price}</td>
                    </tr>`;
                });
                
                batchHtml += '</table>';
            } else {
                batchHtml = '<p class="text-danger">No available batches for this part</p>';
            }
            
            $('#batchList').html(batchHtml);
        }, 'json');
    });
    
    // Calculate total price
    $('#quantity, #unitPrice').on('input', function() {
        const qty = parseFloat($('#quantity').val()) || 0;
        const price = parseFloat($('#unitPrice').val()) || 0;
        $('#totalPrice').val((qty * price).toFixed(2));
    });
    
    // Process sale
    $('#saleForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: 'api/process_sale.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success('Sale processed successfully! Invoice #' + response.invoice_number);
                    // Reset form or redirect
                } else {
                    toastr.error('Error: ' + response.error);
                }
            }
        });
    });
});
</script>
```

## 6. API Endpoints

### get_stock_info.php

```php
<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $stockId = $_GET['stock_id'] ?? null;
    $locationId = $_GET['location_id'] ?? null;
    
    if (!$stockId || !$locationId) {
        throw new Exception("Missing parameters");
    }
    
    // Get stock summary
    $stmt = $db->prepare("SELECT stock_quantity, selling_price FROM stocks WHERE id = ?");
    $stmt->execute([$stockId]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stock) {
        throw new Exception("Part not found");
    }
    
    // Get available batches (FIFO order)
    $batchStmt = $db->prepare("SELECT id, batch_number, receipt_date, quantity_remaining, selling_price
                              FROM inventory_batches
                              WHERE stock_id = ? AND location_id = ? AND quantity_remaining > 0
                              ORDER BY receipt_date ASC");
    $batchStmt->execute([$stockId, $locationId]);
    $batches = $batchStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'available_quantity' => $stock['stock_quantity'],
        'selling_price' => $stock['selling_price'],
        'batches' => $batches
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### process_sale.php

```php
<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['stock_id', 'quantity', 'selling_price', 'total_price', 'customer_id'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Get location from stock
    $stmt = $db->prepare("SELECT location_id FROM stocks WHERE id = ?");
    $stmt->execute([$input['stock_id']]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stock) {
        throw new Exception("Invalid part selected");
    }
    
    $input['location_id'] = $stock['location_id'];
    
    // Process the sale
    $result = processSale($input);
    
    if ($result['success']) {
        // Create invoice
        $invoiceStmt = $db->prepare("INSERT INTO invoices 
                                   (invoice_number, customer_id, issue_date, total_price)
                                   VALUES (?, ?, NOW(), ?)");
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($result['sale_id'], 4, '0', STR_PAD_LEFT);
        $invoiceStmt->execute([$invoiceNumber, $input['customer_id'], $input['total_price']]);
        $invoiceId = $db->lastInsertId();
        
        // Link sale to invoice
        $db->prepare("UPDATE sales SET invoice_id = ? WHERE id = ?")
           ->execute([$invoiceId, $result['sale_id']]);
        
        $result['invoice_number'] = $invoiceNumber;
    }
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// The processSale function would be the same as shown earlier
```

This comprehensive implementation covers all aspects of adding FIFO inventory management to your system. Would you like me to focus on any specific part in more detail or provide additional examples?
____________________________________________________________________________________________
____________________________________________________________________________________________
____________________________________________________________________________________________
# Updated Files for FIFO Implementation

I'll provide the updated versions of your files with FIFO inventory management implemented. Here are the complete updated files:

## 1. manage_sales.php (Updated for FIFO)

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$crud_permissions = $_SESSION['crud_permissions'];

include('db.php');
include('header.php');
$pdo = $conn;

// Initialize $sales as an empty array
$sales = [];

// Fetch sales records with batch information
try {
    $stmt = $pdo->query("
        SELECT s.id, c.first_name, c.last_name, 
               st.part_number, st.description, 
               s.quantity_sold, s.total_price, s.sale_date,
               GROUP_CONCAT(CONCAT(ib.batch_number, ' (', sba.quantity, ')') as batches
        FROM sales s
        JOIN customers c ON s.customer_id = c.id 
        JOIN stocks st ON s.stock_id = st.id
        LEFT JOIN sales_batch_allocation sba ON s.id = sba.sale_id
        LEFT JOIN inventory_batches ib ON sba.batch_id = ib.id
        WHERE s.flag = 'active'
        GROUP BY s.id
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred while fetching sales: " . $e->getMessage());
}

// Fetch customers and stocks for dropdowns
$customers = $pdo->query("SELECT id, first_name, last_name FROM customers WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);
$stocks = $pdo->query("
    SELECT s.id, s.part_number, s.description, 
           SUM(ib.quantity_remaining) as available_quantity,
           s.selling_price, l.location_name
    FROM stocks s
    JOIN inventory_batches ib ON s.id = ib.stock_id
    JOIN locations l ON ib.location_id = l.id
    WHERE s.flag = 'active' AND ib.quantity_remaining > 0 AND ib.flag = 'active'
    GROUP BY s.id, s.part_number, s.description, s.selling_price, l.location_name
")->fetchAll(PDO::FETCH_ASSOC);

// Handle create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $customer_id = intval($_POST['customer_id']);
    $stock_id = intval($_POST['stock_id']);
    $quantity_sold = intval($_POST['quantity_sold']);
    $total_price = floatval($_POST['total_price']);
    $sale_date = $_POST['sale_date'];
    $location_id = intval($_POST['location_id']);

    try {
        $pdo->beginTransaction();

        if ($id) {
            // Update existing sale - more complex with FIFO
            // For simplicity, we'll treat updates as delete+create in this example
            throw new Exception("Editing sales with FIFO inventory is complex. Please delete and recreate the sale.");
        } else {
            // Insert the new sale
            $stmt = $pdo->prepare("
                INSERT INTO sales (customer_id, stock_id, quantity_sold, total_price, sale_date, flag) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$customer_id, $stock_id, $quantity_sold, $total_price, $sale_date]);
            $sale_id = $pdo->lastInsertId();

            // Allocate stock from batches (FIFO)
            $remaining_qty = $quantity_sold;
            
            // Get available batches (oldest first)
            $batch_stmt = $pdo->prepare("
                SELECT ib.id, ib.quantity_remaining, ib.selling_price
                FROM inventory_batches ib
                WHERE ib.stock_id = ? AND ib.location_id = ? AND ib.quantity_remaining > 0 AND ib.flag = 'active'
                ORDER BY ib.receipt_date ASC
            ");
            $batch_stmt->execute([$stock_id, $location_id]);
            $batches = $batch_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($batches as $batch) {
                if ($remaining_qty <= 0) break;
                
                $allocated = min($remaining_qty, $batch['quantity_remaining']);
                
                // Deduct from batch
                $update_stmt = $pdo->prepare("
                    UPDATE inventory_batches 
                    SET quantity_remaining = quantity_remaining - ?
                    WHERE id = ?
                ");
                $update_stmt->execute([$allocated, $batch['id']]);

                // Record allocation
                $alloc_stmt = $pdo->prepare("
                    INSERT INTO sales_batch_allocation (sale_id, batch_id, quantity, unit_price, total_price)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $alloc_stmt->execute([
                    $sale_id, 
                    $batch['id'], 
                    $allocated, 
                    $batch['selling_price'], 
                    $allocated * $batch['selling_price']
                ]);

                $remaining_qty -= $allocated;
            }

            if ($remaining_qty > 0) {
                throw new Exception("Could not allocate all items. Only " . ($quantity_sold - $remaining_qty) . " of $quantity_sold were available.");
            }
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header("Location: manage_sales.php");
    exit;
}

// Handle "delete" (set flag to inactive and return stock)
if (isset($_GET['delete'])) {
    try {
        $pdo->beginTransaction();

        // Fetch the sale and allocations
        $stmt = $pdo->prepare("
            SELECT s.id, s.stock_id, sba.batch_id, sba.quantity
            FROM sales s
            LEFT JOIN sales_batch_allocation sba ON s.id = sba.sale_id
            WHERE s.id = ?
        ");
        $stmt->execute([$_GET['delete']]);
        $sale_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($sale_data) {
            $stock_id = $sale_data[0]['stock_id'];
            
            // Return stock to batches
            foreach ($sale_data as $allocation) {
                if ($allocation['batch_id']) {
                    $update_stmt = $pdo->prepare("
                        UPDATE inventory_batches 
                        SET quantity_remaining = quantity_remaining + ?
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$allocation['quantity'], $allocation['batch_id']]);
                }
            }

            // Set the sale flag to inactive
            $stmt = $pdo->prepare("UPDATE sales SET flag = 'inactive' WHERE id = ?");
            $stmt->execute([$_GET['delete']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header("Location: manage_sales.php");
    exit;
}

// Handle edit fetch (only active records)
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT s.*, 
               GROUP_CONCAT(CONCAT(ib.batch_number, ' (', sba.quantity, ')') as batches,
               ib.location_id
        FROM sales s
        LEFT JOIN sales_batch_allocation sba ON s.id = sba.sale_id
        LEFT JOIN inventory_batches ib ON sba.batch_id = ib.id
        WHERE s.id = ? AND s.flag = 'active'
        GROUP BY s.id
    ");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch locations for dropdown
$locations = $pdo->query("SELECT id, location_name FROM locations WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
    <h2>Sales Management</h2>
    <?php if ($crud_permissions['flag_create'] === 'active'): ?>
        <h3><?= isset($_GET['edit']) ? 'Edit' : 'Add' ?> Sale</h3>
        <form method="POST" class="mt-3">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <div class="mb-3">
                <label class="form-label"><strong>Customer</strong></label>
                <select name="customer_id" class="form-select" required>
                    <option value="" disabled selected>Select a Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>" <?= isset($edit) && $edit['customer_id'] == $customer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Location</strong></label>
                <select name="location_id" class="form-select" required>
                    <option value="" disabled selected>Select a Location</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>" <?= isset($edit) && $edit['location_id'] == $location['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($location['location_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Stock</strong></label>
                <select name="stock_id" class="form-select" required id="stockSelect">
                    <option value="" disabled selected>Select a Stock Item</option>
                    <?php foreach ($stocks as $stock): ?>
                        <option value="<?= $stock['id'] ?>" 
                                data-available="<?= $stock['available_quantity'] ?>"
                                data-price="<?= $stock['selling_price'] ?>"
                                <?= isset($edit) && $edit['stock_id'] == $stock['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stock['part_number'] . ' - ' . $stock['description'] . ' (Available: ' . $stock['available_quantity'] . ' @ ' . $stock['location_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Available Quantity</strong></label>
                <input type="text" class="form-control" id="availableQty" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Quantity to Sell</strong></label>
                <input type="number" name="quantity_sold" class="form-control" placeholder="Enter Quantity Sold" required 
                       min="1" id="quantity" value="<?= $edit['quantity_sold'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Unit Price</strong></label>
                <input type="number" name="unit_price" class="form-control" placeholder="Enter Unit Price" required 
                       step="0.01" min="0" id="unitPrice" value="<?= $edit['unit_price'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Total Price</strong></label>
                <input type="text" name="total_price" class="form-control" placeholder="Enter Total Price" required 
                       readonly id="totalPrice" value="<?= $edit['total_price'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Sale Date</strong></label>
                <input type="date" name="sale_date" class="form-control" placeholder="Enter Sale Date" required 
                       value="<?= $edit['sale_date'] ?? date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="manage_sales.php" class="btn btn-secondary btn-md">Back to list</a>
        </form>
    <?php else: ?>
        <p>You do not have permission to create or edit sales.</p>
    <?php endif; ?>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Part Number</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Sale Date</th>
            <th>Batches</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($sales)): ?>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']) ?></td>
                    <td><?= htmlspecialchars($sale['part_number']) ?></td>
                    <td><?= htmlspecialchars($sale['description']) ?></td>
                    <td><?= htmlspecialchars($sale['quantity_sold']) ?></td>
                    <td><?= htmlspecialchars($sale['total_price']) ?></td>
                    <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                    <td><?= htmlspecialchars($sale['batches'] ?? 'N/A') ?></td>
                    <td>
                        <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                            <a href="?edit=<?= $sale['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <?php endif; ?>
                        <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                            <a href="?delete=<?= $sale['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sale?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No sales records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stockSelect = document.getElementById('stockSelect');
    const availableQty = document.getElementById('availableQty');
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unitPrice');
    const totalPriceInput = document.getElementById('totalPrice');

    // Update available quantity when stock is selected
    stockSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        availableQty.value = selectedOption.dataset.available || '0';
        unitPriceInput.value = selectedOption.dataset.price || '0';
        calculateTotal();
    });

    // Calculate total price when quantity or unit price changes
    quantityInput.addEventListener('input', calculateTotal);
    unitPriceInput.addEventListener('input', calculateTotal);

    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        totalPriceInput.value = (quantity * unitPrice).toFixed(2);
    }

    // Initialize form if editing
    <?php if (isset($edit)): ?>
        if (stockSelect.value) {
            const selectedOption = stockSelect.options[stockSelect.selectedIndex];
            availableQty.value = selectedOption.dataset.available || '0';
        }
    <?php endif; ?>
});
</script>

<?php include('footer.php'); ?>
```

## 2. manage_supplier_purchases.php (Updated for FIFO)

```php
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

// Initialize empty variables to hold form data for pre-fill when editing
$supplier_id = '';
$part_id = '';
$quantity = '';
$cost = '';
$selling_price = '';
$purchase_date = '';
$batch_number = '';
$location_id = '';
$expiry_date = '';
$id = '';

// Initialize $purchases as an empty array
$purchases = [];

// Handle Create/Update actions
if (isset($_POST['save_purchase'])) {
    $supplier_id = intval($_POST['supplier_id']);
    $part_id = intval($_POST['part_id']);
    $quantity = intval($_POST['quantity']);
    $cost = floatval($_POST['cost']);
    $selling_price = floatval($_POST['selling_price']);
    $purchase_date = $_POST['purchase_date'];
    $batch_number = $_POST['batch_number'] ?? '';
    $location_id = intval($_POST['location_id']);
    $expiry_date = $_POST['expiry_date'] ?? null;

    try {
        $conn->beginTransaction();

        if ($_POST['id']) {
            // Update existing supplier purchase - complex with FIFO
            // For simplicity, we'll treat updates as delete+create in this example
            throw new Exception("Editing purchases with FIFO inventory is complex. Please delete and recreate the purchase.");
        } else {
            // Create new supplier purchase entry
            $stmt = $conn->prepare("
                INSERT INTO supplier_purchases 
                (supplier_id, part_id, quantity, cost, purchase_date, flag) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$supplier_id, $part_id, $quantity, $cost, $purchase_date]);
            $purchaseId = $conn->lastInsertId();

            // Create inventory batch record
            if (empty($batch_number)) {
                $batch_number = 'BATCH-' . date('Ymd-His') . '-' . $part_id;
            }

            $batchStmt = $conn->prepare("
                INSERT INTO inventory_batches 
                (stock_id, supplier_purchase_id, batch_number, receipt_date, expiry_date, 
                 quantity_received, quantity_remaining, unit_cost, selling_price, 
                 location_id, supplier_id, flag)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            $batchStmt->execute([
                $part_id, $purchaseId, $batch_number, $purchase_date, $expiry_date,
                $quantity, $quantity, $cost, $selling_price, 
                $location_id, $supplier_id
            ]);

            // Update stock summary
            $updateStockStmt = $conn->prepare("
                UPDATE stocks 
                SET cost = ?, selling_price = ?
                WHERE id = ?
            ");
            $updateStockStmt->execute([$cost, $selling_price, $part_id]);
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_supplier_purchases.php');
    exit;
}

// Handle "delete" (set flag to inactive)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    try {
        $conn->beginTransaction();

        // Fetch the purchase and related batch
        $stmt = $conn->prepare("
            SELECT sp.id, sp.part_id, sp.quantity, ib.id as batch_id
            FROM supplier_purchases sp
            LEFT JOIN inventory_batches ib ON sp.id = ib.supplier_purchase_id
            WHERE sp.id = ?
        ");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($purchase) {
            // Set the purchase flag to inactive
            $stmt = $conn->prepare("UPDATE supplier_purchases SET flag = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);

            // Set the batch flag to inactive
            if ($purchase['batch_id']) {
                $stmt = $conn->prepare("UPDATE inventory_batches SET flag = 'inactive' WHERE id = ?");
                $stmt->execute([$purchase['batch_id']]);
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        die("An error occurred: " . $e->getMessage());
    }

    header('Location: manage_supplier_purchases.php');
    exit;
}

// Fetch supplier purchases with batch info (only active records)
try {
    $purchases_result = $conn->query("
        SELECT sp.*, s.supplier_name, st.part_number, 
               ib.batch_number, ib.receipt_date, ib.expiry_date,
               l.location_name
        FROM supplier_purchases sp
        JOIN suppliers s ON sp.supplier_id = s.id 
        JOIN stocks st ON sp.part_id = st.id
        LEFT JOIN inventory_batches ib ON sp.id = ib.supplier_purchase_id
        LEFT JOIN locations l ON ib.location_id = l.id
        WHERE sp.flag = 'active'
    ");
    $purchases = $purchases_result->fetchAll(PDO::FETCH_ASSOC);

    $suppliers_result = $conn->query("SELECT * FROM suppliers WHERE flag = 'active'");
    $suppliers = $suppliers_result->fetchAll(PDO::FETCH_ASSOC);

    $parts_result = $conn->query("SELECT * FROM stocks WHERE flag = 'active'");
    $parts = $parts_result->fetchAll(PDO::FETCH_ASSOC);

    $locations_result = $conn->query("SELECT * FROM locations WHERE flag = 'active'");
    $locations = $locations_result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("An error occurred: " . $e->getMessage());
}

// Pre-fill form if editing a purchase
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    try {
        $stmt = $conn->prepare("
            SELECT sp.*, ib.batch_number, ib.expiry_date, ib.location_id, ib.selling_price
            FROM supplier_purchases sp
            LEFT JOIN inventory_batches ib ON sp.id = ib.supplier_purchase_id
            WHERE sp.id = ? AND sp.flag = 'active'
        ");
        $stmt->execute([$id]);
        $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($purchase) {
            $supplier_id = $purchase['supplier_id'];
            $part_id = $purchase['part_id'];
            $quantity = $purchase['quantity'];
            $cost = $purchase['cost'];
            $selling_price = $purchase['selling_price'];
            $purchase_date = $purchase['purchase_date'];
            $batch_number = $purchase['batch_number'];
            $location_id = $purchase['location_id'];
            $expiry_date = $purchase['expiry_date'];
        }
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Manage Supplier Purchases</h2>
    </div>

    <div class="card-body">
        <div class="card">
            <div class="card-body">
                <!-- Form to add/update supplier purchase -->
                <?php if ($crud_permissions['flag_create'] === 'active' || $crud_permissions['flag_update'] === 'active'): ?>
                    <form method="POST" action="manage_supplier_purchases.php">
                        <h3>Supplier Purchases</h3>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="form-group">
                            <label><strong>Supplier</strong></label>
                            <select class="form-control" name="supplier_id" required>
                                <option value="" disabled selected>Select a Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>" <?php if ($supplier['id'] == $supplier_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Part</strong></label>
                            <select class="form-control" name="part_id" required>
                                <option value="" disabled selected>Select a Part</option>
                                <?php foreach ($parts as $part): ?>
                                    <option value="<?php echo $part['id']; ?>" <?php if ($part['id'] == $part_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($part['part_number'] . ' - ' . $part['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Location</strong></label>
                            <select class="form-control" name="location_id" required>
                                <option value="" disabled selected>Select a Location</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location['id']; ?>" <?php if ($location['id'] == $location_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><strong>Batch Number</strong></label>
                            <input type="text" class="form-control" name="batch_number" 
                                   placeholder="Leave blank to auto-generate" value="<?php echo $batch_number; ?>">
                        </div>
                        <div class="form-group">
                            <label><strong>Quantity</strong></label>
                            <input type="number" class="form-control" name="quantity" 
                                   placeholder="Enter quantity" value="<?php echo $quantity; ?>" required min="1">
                        </div>
                        <div class="form-group">
                            <label><strong>Unit Cost</strong></label>
                            <input type="number" class="form-control" name="cost" 
                                   placeholder="Enter cost" value="<?php echo $cost; ?>" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Selling Price</strong></label>
                            <input type="number" class="form-control" name="selling_price" 
                                   placeholder="Enter selling price" value="<?php echo $selling_price; ?>" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Purchase Date</strong></label>
                            <input type="date" class="form-control" name="purchase_date" 
                                   value="<?php echo $purchase_date ?: date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Expiry Date (if applicable)</strong></label>
                            <input type="date" class="form-control" name="expiry_date" 
                                   value="<?php echo $expiry_date; ?>">
                        </div>
                        <button type="submit" name="save_purchase" class="btn btn-primary">Save Purchase</button>
                        <a href="manage_supplier_purchases.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php endif; ?>

                <hr>

                <!-- Supplier Purchase List -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Part Number</th>
                            <th>Batch</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Cost</th>
                            <th>Selling Price</th>
                            <th>Purchase Date</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($purchases)): ?>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['part_number']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['batch_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['location_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['cost']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['selling_price'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['expiry_date'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($crud_permissions['flag_update'] === 'active'): ?>
                                            <a href="manage_supplier_purchases.php?edit=<?php echo $purchase['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                        <?php endif; ?>
                                        <?php if ($crud_permissions['flag_delete'] === 'active'): ?>
                                            <a href="manage_supplier_purchases.php?delete=<?php echo $purchase['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this purchase?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center">No supplier purchases found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
```

## 3. manage_stock.php (Updated for FIFO)

```php
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
    $brand_id = $_POST['brand_id'];
    $type_id = $_POST['type_id'];
    $cost = $_POST['cost'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $supplier_id = $_POST['supplier_id'];
    $location_id = $_POST['location_id'];
    $oem_number = $_POST['oem_number'];

    try {
        if ($_POST['id']) {
            // Update existing stock - only metadata, not quantity (quantity comes from batches)
            $id = $_POST['id'];
            $sql = "UPDATE stocks SET part_number=:part_number, description=:description, image=:image, 
                    category_id=:category_id, model_id=:model_id, brand_id=:brand_id, type_id=:type_id, cost=:cost, 
                    selling_price=:selling_price, supplier_id=:supplier_id, 
                    location_id=:location_id, oem_number=:oem_number 
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'type_id' => $type_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id,
                'oem_number' => $oem_number,
                'id' => $id
            ]);
        } else {
            // Create new stock entry (with 0 quantity - actual inventory comes from batches)
            $sql = "INSERT INTO stocks (part_number, description, image, category_id, model_id, brand_id, type_id, 
                    cost, selling_price, stock_quantity, supplier_id, location_id, oem_number)
                    VALUES (:part_number, :description, :image, :category_id, :model_id, :brand_id, :type_id, 
                    :cost, :selling_price, 0, :supplier_id, :location_id, :oem_number)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'type_id' => $type_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id,
                'oem_number' => $oem_number
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
        
        // Check if there are any batches with remaining quantity
        $check_stmt = $conn->prepare("
            SELECT SUM(quantity_remaining) as total_remaining 
            FROM inventory_batches 
            WHERE stock_id = :id AND flag = 'active'
        ");
        $check_stmt->execute(['id' => $id]);
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_remaining'] > 0) {
            die("Cannot delete stock item with remaining inventory. Please deplete inventory first.");
        }
        
        // Set stock flag to inactive
        $sql = "UPDATE stocks SET flag='inactive' WHERE id=:id";
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
        SELECT s.*, c.category_name, m.model_name, b.brand_name, t.type_name, su.supplier_name, l.location_name,
               (SELECT SUM(quantity_remaining) FROM inventory_batches WHERE stock_id = s.id AND flag = 'active') as total_quantity
        FROM stocks s 
        JOIN categories c ON s.category_id = c.id 
        JOIN models m ON s.model_id = m.id 
        JOIN brands b ON s.brand_id = b.id 
        JOIN types t ON s.type_id = t.id 
        JOIN suppliers su ON s.supplier_id = su.id
        JOIN locations l ON s.location_id = l.id
        WHERE s.id=:id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $view_stocks = $stmt->fetchAll(PDO::FETCH_ASSOC


<?php
// manage_stock.php - FIFO Inventory Management System

require_once 'db_connection.php';

class FIFOStockManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Add stock to inventory using FIFO principle
     */
    public function addStock($product_id, $quantity, $purchase_price, $expiry_date = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory (product_id, quantity, purchase_price, expiry_date, date_added) 
                VALUES (:product_id, :quantity, :purchase_price, :expiry_date, NOW())
            ");
            
            $stmt->execute([
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':purchase_price' => $purchase_price,
                ':expiry_date' => $expiry_date
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error adding stock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sell stock using FIFO principle
     */
    public function sellStock($product_id, $quantity) {
        $this->pdo->beginTransaction();
        
        try {
            // Get available batches ordered by date_added (oldest first)
            $stmt = $this->pdo->prepare("
                SELECT id, quantity, purchase_price 
                FROM inventory 
                WHERE product_id = :product_id AND quantity > 0 
                ORDER BY date_added ASC
                FOR UPDATE
            ");
            
            $stmt->execute([':product_id' => $product_id]);
            $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($batches)) {
                throw new Exception("No stock available for product ID: $product_id");
            }
            
            $remaining = $quantity;
            $total_cost = 0;
            $batch_updates = [];
            
            foreach ($batches as $batch) {
                if ($remaining <= 0) break;
                
                $deduct = min($remaining, $batch['quantity']);
                $remaining -= $deduct;
                $total_cost += $deduct * $batch['purchase_price'];
                
                $batch_updates[] = [
                    'id' => $batch['id'],
                    'new_quantity' => $batch['quantity'] - $deduct
                ];
            }
            
            if ($remaining > 0) {
                throw new Exception("Insufficient stock. Available: " . ($quantity - $remaining) . ", Requested: $quantity");
            }
            
            // Update inventory quantities
            foreach ($batch_updates as $update) {
                $stmt = $this->pdo->prepare("
                    UPDATE inventory 
                    SET quantity = :new_quantity 
                    WHERE id = :id
                ");
                $stmt->execute($update);
            }
            
            // Record the sale
            $stmt = $this->pdo->prepare("
                INSERT INTO sales (product_id, quantity, total_cost, sale_date) 
                VALUES (:product_id, :quantity, :total_cost, NOW())
            ");
            $stmt->execute([
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':total_cost' => $total_cost
            ]);
            
            $this->pdo->commit();
            return [
                'success' => true,
                'total_cost' => $total_cost,
                'quantity_sold' => $quantity
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get current stock level for a product
     */
    public function getStockLevel($product_id) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(quantity) as total_quantity 
            FROM inventory 
            WHERE product_id = :product_id
        ");
        
        $stmt->execute([':product_id' => $product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_quantity'] ?? 0;
    }
    
    /**
     * Get inventory valuation using FIFO method
     */
    public function getInventoryValuation() {
        $stmt = $this->pdo->query("
            SELECT 
                p.id as product_id,
                p.name as product_name,
                SUM(i.quantity) as total_quantity,
                SUM(i.quantity * i.purchase_price) as total_value
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            WHERE i.quantity > 0
            GROUP BY p.id, p.name
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Example usage:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockManager = new FIFOStockManager($pdo);
    $response = [];
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_stock':
                    $required = ['product_id', 'quantity', 'purchase_price'];
                    foreach ($required as $field) {
                        if (empty($_POST[$field])) {
                            throw new Exception("Missing required field: $field");
                        }
                    }
                    
                    $success = $stockManager->addStock(
                        $_POST['product_id'],
                        $_POST['quantity'],
                        $_POST['purchase_price'],
                        $_POST['expiry_date'] ?? null
                    );
                    
                    $response = [
                        'success' => $success,
                        'message' => $success ? 'Stock added successfully' : 'Failed to add stock'
                    ];
                    break;
                    
                case 'sell_stock':
                    $required = ['product_id', 'quantity'];
                    foreach ($required as $field) {
                        if (empty($_POST[$field])) {
                            throw new Exception("Missing required field: $field");
                        }
                    }
                    
                    $result = $stockManager->sellStock(
                        $_POST['product_id'],
                        $_POST['quantity']
                    );
                    
                    $response = $result;
                    break;
                    
                case 'get_stock':
                    if (empty($_POST['product_id'])) {
                        throw new Exception("Missing required field: product_id");
                    }
                    
                    $quantity = $stockManager->getStockLevel($_POST['product_id']);
                    $response = [
                        'success' => true,
                        'quantity' => $quantity
                    ];
                    break;
                    
                default:
                    throw new Exception("Invalid action");
            }
        } else {
            throw new Exception("No action specified");
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIFO Stock Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 150px; }
        input, select { padding: 5px; width: 200px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background: #45a049; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>FIFO Stock Management</h1>
    
    <div class="tabs">
        <button onclick="showTab('add')">Add Stock</button>
        <button onclick="showTab('sell')">Sell Stock</button>
        <button onclick="showTab('check')">Check Stock</button>
    </div>
    
    <div id="add" class="tab-content">
        <h2>Add Stock</h2>
        <form id="addForm">
            <div class="form-group">
                <label for="add_product_id">Product ID:</label>
                <input type="number" id="add_product_id" name="product_id" required>
            </div>
            <div class="form-group">
                <label for="add_quantity">Quantity:</label>
                <input type="number" id="add_quantity" name="quantity" required>
            </div>
            <div class="form-group">
                <label for="add_purchase_price">Purchase Price:</label>
                <input type="number" step="0.01" id="add_purchase_price" name="purchase_price" required>
            </div>
            <div class="form-group">
                <label for="add_expiry_date">Expiry Date:</label>
                <input type="date" id="add_expiry_date" name="expiry_date">
            </div>
            <button type="button" onclick="submitForm('add')">Add Stock</button>
        </form>
        <div id="addResult" class="result"></div>
    </div>
    
    <div id="sell" class="tab-content" style="display:none;">
        <h2>Sell Stock</h2>
        <form id="sellForm">
            <div class="form-group">
                <label for="sell_product_id">Product ID:</label>
                <input type="number" id="sell_product_id" name="product_id" required>
            </div>
            <div class="form-group">
                <label for="sell_quantity">Quantity:</label>
                <input type="number" id="sell_quantity" name="quantity" required>
            </div>
            <button type="button" onclick="submitForm('sell')">Sell Stock</button>
        </form>
        <div id="sellResult" class="result"></div>
    </div>
    
    <div id="check" class="tab-content" style="display:none;">
        <h2>Check Stock Level</h2>
        <form id="checkForm">
            <div class="form-group">
                <label for="check_product_id">Product ID:</label>
                <input type="number" id="check_product_id" name="product_id" required>
            </div>
            <button type="button" onclick="submitForm('check')">Check Stock</button>
        </form>
        <div id="checkResult" class="result"></div>
    </div>
    
    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            document.getElementById(tabName).style.display = 'block';
        }
        
        function submitForm(action) {
            const form = document.getElementById(action + 'Form');
            const formData = new FormData(form);
            formData.append('action', action + '_stock');
            
            fetch('manage_stock.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById(action + 'Result');
                if (data.success) {
                    resultDiv.innerHTML = `<p style="color: green;">Success: ${data.message || 'Operation completed'}</p>`;
                    if (action === 'check') {
                        resultDiv.innerHTML += `<p>Current stock: ${data.quantity}</p>`;
                    }
                    if (action === 'sell' && data.total_cost) {
                        resultDiv.innerHTML += `<p>Total cost: $${data.total_cost.toFixed(2)}</p>`;
                    }
                } else {
                    resultDiv.innerHTML = `<p style="color: red;">Error: ${data.message}</p>`;
                }
            })
            .catch(error => {
                document.getElementById(action + 'Result').innerHTML = 
                    `<p style="color: red;">Error: ${error.message}</p>`;
            });
        }
    </script>
</body>
</html>        


____________________________________________________________________________
**********************************************************************
_________________________________________________________________________________
I'll help you update your existing `manage_stock.php` with FIFO (First-In, First-Out) inventory management functionality while preserving all your current features. Here's the integrated solution:

```php
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

class FIFOStockManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Add stock to inventory using FIFO batches
     */
    public function addStockBatch($product_id, $quantity, $cost, $supplier_id, $location_id, $expiry_date = null) {
        try {
            $sql = "INSERT INTO stock_batches (product_id, quantity, remaining_quantity, cost, supplier_id, location_id, expiry_date, date_added) 
                    VALUES (:product_id, :quantity, :remaining_quantity, :cost, :supplier_id, :location_id, :expiry_date, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':remaining_quantity' => $quantity,
                ':cost' => $cost,
                ':supplier_id' => $supplier_id,
                ':location_id' => $location_id,
                ':expiry_date' => $expiry_date
            ]);
            
            // Update the main stock quantity
            $this->updateStockQuantity($product_id);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error adding stock batch: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Sell stock using FIFO principle
     */
    public function sellStock($product_id, $quantity) {
        $this->conn->beginTransaction();
        
        try {
            // Get available batches ordered by date_added (oldest first)
            $stmt = $this->conn->prepare("
                SELECT id, remaining_quantity, cost 
                FROM stock_batches 
                WHERE product_id = :product_id AND remaining_quantity > 0 
                ORDER BY date_added ASC
                FOR UPDATE
            ");
            
            $stmt->execute([':product_id' => $product_id]);
            $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($batches)) {
                throw new Exception("No stock available for product ID: $product_id");
            }
            
            $remaining = $quantity;
            $total_cost = 0;
            $batch_updates = [];
            
            foreach ($batches as $batch) {
                if ($remaining <= 0) break;
                
                $deduct = min($remaining, $batch['remaining_quantity']);
                $remaining -= $deduct;
                $total_cost += $deduct * $batch['cost'];
                
                $batch_updates[] = [
                    'id' => $batch['id'],
                    'new_quantity' => $batch['remaining_quantity'] - $deduct
                ];
            }
            
            if ($remaining > 0) {
                throw new Exception("Insufficient stock. Available: " . ($quantity - $remaining) . ", Requested: $quantity");
            }
            
            // Update batch quantities
            foreach ($batch_updates as $update) {
                $stmt = $this->conn->prepare("
                    UPDATE stock_batches 
                    SET remaining_quantity = :new_quantity 
                    WHERE id = :id
                ");
                $stmt->execute($update);
            }
            
            // Update the main stock quantity
            $this->updateStockQuantity($product_id);
            
            // Record the sale
            $stmt = $this->conn->prepare("
                INSERT INTO sales (product_id, quantity, total_cost, sale_date) 
                VALUES (:product_id, :quantity, :total_cost, NOW())
            ");
            $stmt->execute([
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':total_cost' => $total_cost
            ]);
            
            $this->conn->commit();
            return [
                'success' => true,
                'total_cost' => $total_cost,
                'quantity_sold' => $quantity
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update the main stock quantity from batches
     */
    private function updateStockQuantity($product_id) {
        $stmt = $this->conn->prepare("
            UPDATE stocks 
            SET stock_quantity = (
                SELECT SUM(remaining_quantity) 
                FROM stock_batches 
                WHERE product_id = :product_id
            ) 
            WHERE id = :product_id
        ");
        $stmt->execute([':product_id' => $product_id]);
    }
    
    /**
     * Get current stock level from batches for a product
     */
    public function getStockLevel($product_id) {
        $stmt = $this->conn->prepare("
            SELECT SUM(remaining_quantity) as total_quantity 
            FROM stock_batches 
            WHERE product_id = :product_id
        ");
        
        $stmt->execute([':product_id' => $product_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total_quantity'] ?? 0;
    }
    
    /**
     * Get inventory valuation using FIFO method
     */
    public function getInventoryValuation() {
        $stmt = $this->conn->query("
            SELECT 
                s.id as product_id,
                s.part_number,
                s.description,
                SUM(b.remaining_quantity) as total_quantity,
                SUM(b.remaining_quantity * b.cost) as total_value
            FROM stock_batches b
            JOIN stocks s ON b.product_id = s.id
            WHERE b.remaining_quantity > 0
            GROUP BY s.id, s.part_number, s.description
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$fifoManager = new FIFOStockManager($conn);

// Handle Create/Update actions
if (isset($_POST['save_stock'])) {
    $part_number = $_POST['part_number'];
    $description = $_POST['description'];
    $image = $_POST['image'];
    $category_id = $_POST['category_id'];
    $model_id = $_POST['model_id'];
    $brand_id = $_POST['brand_id'];
    $type_id = $_POST['type_id'];
    $cost = $_POST['cost'];
    $selling_price = $_POST['selling_price'];
    $stock_quantity = $_POST['stock_quantity'];
    $supplier_id = $_POST['supplier_id'];
    $location_id = $_POST['location_id'];
    $oem_number = $_POST['oem_number'];

    try {
        if ($_POST['id']) {
            // Update existing stock
            $id = $_POST['id'];
            $sql = "UPDATE stocks SET part_number=:part_number, description=:description, image=:image, 
                    category_id=:category_id, model_id=:model_id, brand_id=:brand_id, type_id=:type_id, cost=:cost, 
                    selling_price=:selling_price, supplier_id=:supplier_id, 
                    location_id=:location_id, oem_number=:oem_number 
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'type_id' => $type_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id,
                'oem_number' => $oem_number,
                'id' => $id
            ]);
            
            // If quantity changed, add a new batch
            if ($stock_quantity > 0) {
                $fifoManager->addStockBatch($id, $stock_quantity, $cost, $supplier_id, $location_id);
            }
        } else {
            // Create new stock entry
            $sql = "INSERT INTO stocks (part_number, description, image, category_id, model_id, brand_id, type_id, 
                    cost, selling_price, stock_quantity, supplier_id, location_id, oem_number)
                    VALUES (:part_number, :description, :image, :category_id, :model_id, :brand_id, :type_id, 
                    :cost, :selling_price, :stock_quantity, :supplier_id, :location_id, :oem_number)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'part_number' => $part_number,
                'description' => $description,
                'image' => $image,
                'category_id' => $category_id,
                'model_id' => $model_id,
                'brand_id' => $brand_id,
                'type_id' => $type_id,
                'cost' => $cost,
                'selling_price' => $selling_price,
                'stock_quantity' => $stock_quantity,
                'supplier_id' => $supplier_id,
                'location_id' => $location_id,
                'oem_number' => $oem_number
            ]);
            
            $product_id = $conn->lastInsertId();
            
            // Add initial stock batch
            if ($stock_quantity > 0) {
                $fifoManager->addStockBatch($product_id, $stock_quantity, $cost, $supplier_id, $location_id);
            }
        }
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }
}

// Handle stock movement actions
if (isset($_POST['add_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $cost = $_POST['cost'];
    $supplier_id = $_POST['supplier_id'];
    $location_id = $_POST['location_id'];
    
    $result = $fifoManager->addStockBatch($product_id, $quantity, $cost, $supplier_id, $location_id);
    
    if ($result) {
        $_SESSION['message'] = "Stock added successfully";
    } else {
        $_SESSION['error'] = "Failed to add stock";
    }
    header("Location: manage_stock.php");
    exit;
}

if (isset($_POST['sell_stock'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    $result = $fifoManager->sellStock($product_id, $quantity);
    
    if ($result['success']) {
        $_SESSION['message'] = "Sold $quantity items successfully. Total cost: " . $result['total_cost'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header("Location: manage_stock.php");
    exit;
}

// [Rest of your existing code remains the same until the HTML section...]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [Your existing head section remains the same] -->
    <style>
        /* Add to your existing styles */
        .stock-movement-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .batch-history {
            margin-top: 20px;
        }
        .batch-history table {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- [Your existing body content remains the same until the view/edit section] -->

    <?php if ($view_stocks) { ?>
        <!-- [Your existing view stock details card] -->
        
        <!-- Add Stock Movement Forms -->
        <div class="card mt-4">
            <div class="card-header">
                Stock Movements
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 stock-movement-form">
                        <h5>Add Stock</h5>
                        <form method="POST" action="manage_stock.php">
                            <input type="hidden" name="product_id" value="<?php echo $view_stock['id']; ?>">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Cost</label>
                                <input type="number" step="0.01" name="cost" class="form-control" value="<?php echo $view_stock['cost']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Supplier</label>
                                <select name="supplier_id" class="form-control" required>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier['id'] == $view_stock['supplier_id'] ? 'selected' : ''; ?>>
                                            <?php echo $supplier['supplier_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <select name="location_id" class="form-control" required>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo $location['id']; ?>" <?php echo $location['id'] == $view_stock['location_id'] ? 'selected' : ''; ?>>
                                            <?php echo $location['location_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_stock" class="btn btn-primary">Add Stock</button>
                        </form>
                    </div>
                    
                    <div class="col-md-6 stock-movement-form">
                        <h5>Sell Stock</h5>
                        <form method="POST" action="manage_stock.php">
                            <input type="hidden" name="product_id" value="<?php echo $view_stock['id']; ?>">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>
                            <button type="submit" name="sell_stock" class="btn btn-danger">Sell Stock</button>
                        </form>
                    </div>
                </div>
                
                <!-- Batch History -->
                <div class="batch-history mt-4">
                    <h5>Stock Batch History (FIFO)</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Batch ID</th>
                                <th>Initial Qty</th>
                                <th>Remaining Qty</th>
                                <th>Cost</th>
                                <th>Supplier</th>
                                <th>Location</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $batch_stmt = $conn->prepare("
                                SELECT b.*, s.supplier_name, l.location_name 
                                FROM stock_batches b
                                LEFT JOIN suppliers s ON b.supplier_id = s.id
                                LEFT JOIN locations l ON b.location_id = l.id
                                WHERE b.product_id = :product_id
                                ORDER BY b.date_added ASC
                            ");
                            $batch_stmt->execute([':product_id' => $view_stock['id']]);
                            $batches = $batch_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (empty($batches)): ?>
                                <tr>
                                    <td colspan="7">No stock batches found</td>
                                </tr>
                            <?php else:
                                foreach ($batches as $batch): ?>
                                    <tr>
                                        <td><?php echo $batch['id']; ?></td>
                                        <td><?php echo $batch['quantity']; ?></td>
                                        <td><?php echo $batch['remaining_quantity']; ?></td>
                                        <td><?php echo number_format($batch['cost'], 2); ?></td>
                                        <td><?php echo $batch['supplier_name']; ?></td>
                                        <td><?php echo $batch['location_name']; ?></td>
                                        <td><?php echo $batch['date_added']; ?></td>
                                    </tr>
                                <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <!-- [Rest of your existing code for the edit/create form] -->
    <?php } ?>

    <!-- [Rest of your existing code] -->

    <?php include('footer.php'); ?>
</body>
</html>
```

Key changes made to integrate FIFO:

1. **Added the `FIFOStockManager` class** that handles all FIFO-related operations:
   - `addStockBatch()` - Adds new stock batches with cost and quantity
   - `sellStock()` - Implements FIFO logic when selling items
   - `updateStockQuantity()` - Updates the main stock quantity from batches
   - `getStockLevel()` - Gets current stock level from batches
   - `getInventoryValuation()` - Calculates inventory value using FIFO

2. **Modified the stock save logic**:
   - When creating/updating stock, it now adds a new batch if quantity is provided
   - The main stock quantity is calculated from batches rather than stored directly

3. **Added stock movement forms** in the view section:
   - Form to add new stock (creates new batches)
   - Form to sell stock (uses FIFO logic)
   - Display of all stock batches with remaining quantities

4. **Database changes needed**:
   - You'll need to create a `stock_batches` table with fields: 
     ```sql
     CREATE TABLE stock_batches (
         id INT AUTO_INCREMENT PRIMARY KEY,
         product_id INT NOT NULL,
         quantity DECIMAL(10,2) NOT NULL,
         remaining_quantity DECIMAL(10,2) NOT NULL,
         cost DECIMAL(10,2) NOT NULL,
         supplier_id INT,
         location_id INT,
         expiry_date DATE,
         date_added DATETIME NOT NULL,
         FOREIGN KEY (product_id) REFERENCES stocks(id),
         FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
         FOREIGN KEY (location_id) REFERENCES locations(id)
     );
     ```
   - And a `sales` table if you don't have one:
     ```sql
     CREATE TABLE sales (
         id INT AUTO_INCREMENT PRIMARY KEY,
         product_id INT NOT NULL,
         quantity DECIMAL(10,2) NOT NULL,
         total_cost DECIMAL(10,2) NOT NULL,
         sale_date DATETIME NOT NULL,
         FOREIGN KEY (product_id) REFERENCES stocks(id)
     );
     ```

This implementation maintains all your existing functionality while adding FIFO inventory management. The changes are focused on:
- Tracking inventory by batches
- Selling oldest stock first
- Maintaining accurate cost calculations
- Showing batch history for each product

The UI remains largely the same with the addition of the stock movement forms in the view section.
________________________________________________________________________
07-04-2025
_______________________________________________
# Detailed Explanation of FIFO Implementation in `manage_sales.php`

Let me break down exactly how the FIFO system works in your sales process, step by step.

## The Core FIFO Logic in Sales

When you sell items, the system needs to:
1. Identify which inventory batches to use (oldest first)
2. Mark them as sold (or partially sold)
3. Ensure you don't oversell your inventory

Here's the detailed flow:

### 1. When a Sale is Created

```php
// Insert the new sale record
$stmt = $pdo->prepare("INSERT INTO sales (...) VALUES (...)");
$stmt->execute([...]);
$sale_id = $pdo->lastInsertId();

// FIFO Processing Starts Here
$remaining = $quantity_sold; // How many items we still need to allocate
```

### 2. Fetch Available Inventory (Oldest First)

```php
$stmt = $pdo->prepare("SELECT * FROM fifo_queue 
                     WHERE part_id = ?      // Only items for this product
                     AND is_processed = 0   // Only unsold items
                     ORDER BY id ASC");     // Oldest first (FIFO)
$stmt->execute([$stock_id]);
$fifo_items = $stmt->fetchAll();
```

### 3. Process Each Inventory Batch

```php
foreach ($fifo_items as $item) {
    if ($remaining <= 0) break; // Done processing
    
    $available = $item['quantity']; // Qty available in this batch
    $used = min($available, $remaining); // How much we'll take from this batch
    
    if ($used == $available) {
        // COMPLETELY CONSUME this batch
        $update = $pdo->prepare("UPDATE fifo_queue SET is_processed = 1 WHERE id = ?");
        $update->execute([$item['id']]);
    } else {
        // PARTIALLY CONSUME this batch
        $new_quantity = $available - $used;
        $update = $pdo->prepare("UPDATE fifo_queue SET quantity = ? WHERE id = ?");
        $update->execute([$new_quantity, $item['id']]);
    }
    
    $remaining -= $used; // Reduce our remaining need
}
```

### 4. Validate All Inventory Was Allocated

```php
if ($remaining > 0) {
    throw new Exception("Not enough stock available in FIFO queue");
}
```

## Visual Example

Let's say you're selling 15 units of Part #123, and your inventory has:

| FIFO ID | Part ID | Qty | Purchase Date | Status |
|---------|---------|-----|---------------|--------|
| 1       | 123     | 10  | 2023-01-01    | Unsold |
| 2       | 123     | 5   | 2023-02-01    | Unsold | 
| 3       | 123     | 8   | 2023-03-01    | Unsold |

The system will:

1. Take 10 from FIFO ID 1 (oldest) - marks as fully sold
2. Take 5 from FIFO ID 2 - marks as fully sold
3. Now all 15 are allocated, so it stops

Resulting inventory:

| FIFO ID | Part ID | Qty | Purchase Date | Status |
|---------|---------|-----|---------------|--------|
| 1       | 123     | 0   | 2023-01-01    | Sold   |
| 2       | 123     | 0   | 2023-02-01    | Sold   | 
| 3       | 123     | 8   | 2023-03-01    | Unsold |

## Handling Sale Deletions/Cancellations

When a sale is deleted, we reverse the process:

```php
// 1. Get how many items need to be returned
$quantity_sold = $sale['quantity_sold'];

// 2. Find the most recently sold batches for this part
$stmt = $pdo->prepare("UPDATE fifo_queue SET is_processed = 0 
                      WHERE part_id = ? 
                      AND is_processed = 1
                      ORDER BY id DESC  // Newest first (LIFO reversal)
                      LIMIT ?");
$stmt->execute([$stock_id, $quantity_sold]);
```

This ensures inventory returns to the system in the reverse order it was sold.

## Key Benefits

1. **Accurate Cost Tracking**: Since you know exactly which batches were sold, you can calculate true profit
2. **Tax Compliance**: Meets accounting standards for inventory valuation
3. **Stock Aging**: Automatically sells older inventory first
4. **Prevents Overselling**: Validates inventory exists before completing sale

## Real-World Example Flow

1. You purchase:
   - Jan 1: 10 units @ $5 each
   - Feb 1: 5 units @ $6 each

2. You sell 12 units:
   - System takes all 10 from Jan 1 batch
   - Then takes 2 from Feb 1 batch
   - Cost basis = (10×$5) + (2×$6) = $62

3. If you cancel this sale:
   - Returns 2 units to Feb 1 batch
   - Then returns 10 units to Jan 1 batch

Would you like me to explain any part of this process in more detail?
________________________________________________________
WAC 09-04-2025
________________________________________________________
## Visual Example

Let's say you're selling 18 units of Part #123, and your inventory has:

| FIFO ID | Part ID | Qty |Unit Price| Purchase Date | Status |
|---------|---------|-----|----------|---------------|--------|
| 1       | 123     | 10  | 100      | 2023-01-01    | Unsold |
| 2       | 123     | 5   | 110      | 2023-02-01    | Unsold | 
| 3       | 123     | 10  | 120      | 2023-03-01    | Unsold |

Plese show me STEP BY STEP how WAC works for this example. According to my boss's calcuation WAC should be 110 ???


I hv changed your code according to my system. Please correct this if it is not ok

Generated app password fo Mail_php
Your app password for your device
rles azuj cyjy vgsu (remove spaces when used)