<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Get the same filters as the report
$part_id = $_GET['part_id'] ?? '';
$show_zero_qty = $_GET['show_zero_qty'] ?? 0;
$status_filter = $_GET['status'] ?? 'all';

try {
    // Same query as the report
    // $query = "SELECT ..."; // Use the same query from the report
    $query = "SELECT 
                fq.id AS fifo_id,
                fq.part_id,
                s.part_number,
                fq.quantity,
                fq.cost,
                fq.purchase_date,
                fq.is_processed,
                sp.id AS supplier_id,
                sup.supplier_name
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              LEFT JOIN supplier_purchases sp ON fq.supplier_id = sp.id
              LEFT JOIN suppliers sup ON sp.supplier_id = sup.id
              WHERE 1=1";
    
    $stmt = $conn->prepare($query);
    // Apply the same parameters as the report
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="fifo_inventory_report_'.date('Y-m-d').'.xls"');
    
    // Excel header
    echo "FIFO ID\tPart Number\tQuantity\tUnit Cost\tPurchase Date\tSupplier\tPurchase ID\tStatus\n";
    
    // Data rows
    foreach ($inventory as $item) {
        echo implode("\t", [
            $item['fifo_id'],
            $item['part_number'],
            $item['quantity'],
            $item['cost'],
            $item['purchase_date'],
            $item['supplier_name'] ?? 'N/A',
            $item['supplier_id'],
            $item['is_processed'] ? 'Sold' : 'Unsold'
        ]) . "\n";
    }
    
    exit;
    
} catch (PDOException $e) {
    die("Error generating export: " . $e->getMessage());
}