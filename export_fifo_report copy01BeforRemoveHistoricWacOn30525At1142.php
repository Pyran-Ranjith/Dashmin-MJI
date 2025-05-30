<?php
//export_fifo_report.php
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
    // First get all parts that match filters to calculate proper WACs
    $parts_query = "SELECT DISTINCT fq.part_id, s.part_number
                   FROM fifo_queue1 fq
                   JOIN stocks s ON fq.part_id = s.id
                   WHERE 1=1";
    
    $params = [];
    
    if (!empty($part_id)) {
        $parts_query .= " AND fq.part_id = ?";
        $params[] = $part_id;
    }
    
    $stmt = $conn->prepare($parts_query);
    $stmt->execute($params);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate WAC for each part
    $wac_data = [];
    foreach ($parts as $part) {
        $stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN is_processed = 0 THEN quantity * cost ELSE 0 END) / 
                NULLIF(SUM(CASE WHEN is_processed = 0 THEN quantity ELSE 0 END), 0) AS current_wac,
                SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS historical_wac
            FROM fifo_queue1
            WHERE part_id = ?
        ");
        $stmt->execute([$part['part_id']]);
        $wac_data[$part['part_id']] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Now get the detailed records
    $query = "SELECT 
                fq.id AS fifo_id,
                fq.part_id,
                s.part_number,
                fq.quantity,
                fq.cost,
                fq.purchase_date,
                fq.is_processed,
                sup.supplier_name,
                sp.id AS supplier_id
              FROM fifo_queue1 fq
              LEFT JOIN stocks s ON fq.part_id = s.id
              LEFT JOIN supplier_purchases sp ON fq.supplier_id = sp.id
              LEFT JOIN suppliers sup ON sp.supplier_id = sup.id
              WHERE 1=1";
    
    if (!empty($part_id)) {
        $query .= " AND fq.part_id = ?";
        // params already set
    }
    
    if (!$show_zero_qty) {
        $query .= " AND fq.quantity > 0";
    }
    
    if ($status_filter !== 'all') {
        $query .= " AND fq.is_processed = ?";
        $params[] = ($status_filter === 'sold') ? 1 : 0;
    }
    
    $query .= " ORDER BY fq.part_id, fq.purchase_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="fifo_inventory_report_'.date('Y-m-d').'.xls"');
    
    // Excel header
    echo "FIFO Inventory Report\n";
    echo "FIFO ID\tPart Number\tQuantity\tUnit Cost\tPurchase Date\tSupplier\tPurchase ID\tStatus\tCurrent WAC\tHistorical WAC\n";
    
    // Data rows
    foreach ($inventory as $item) {
        $part_id = $item['part_id'];
        $current_wac = $wac_data[$part_id]['current_wac'] ?? 0;
        $historical_wac = $wac_data[$part_id]['historical_wac'] ?? 0;
        
        echo implode("\t", [
            $item['fifo_id'],
            $item['part_number'],
            number_format($item['quantity'], 2),
            number_format($item['cost'], 2),
            date('Y-m-d', strtotime($item['purchase_date'])),
            $item['supplier_name'] ?? 'N/A',
            $item['purchase_id'] ?? '',
            $item['is_processed'] ? 'Sold' : 'Unsold',
            number_format($current_wac, 2),
            number_format($historical_wac, 2)
        ]) . "\n";
    }
    
    exit;
    
} catch (PDOException $e) {
    die("Error generating export: " . $e->getMessage());
}