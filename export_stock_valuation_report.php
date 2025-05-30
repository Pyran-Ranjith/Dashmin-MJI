<?php
// export_stock_valuation_report.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

// Get the same filters as the report
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

try {
    // Get stock valuation data - same query as in stock_valuation_report.php
    $query = "SELECT 
                s.part_number,
                SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END) AS qty,
                SUM(CASE WHEN f.is_processed = 0 THEN f.quantity * f.cost ELSE 0 END) AS total_cost,
                CASE 
                    WHEN SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END) > 0 
                    THEN SUM(CASE WHEN f.is_processed = 0 THEN f.quantity * f.cost ELSE 0 END) / 
                         SUM(CASE WHEN f.is_processed = 0 THEN f.quantity ELSE 0 END)
                    ELSE 0
                END AS avg_cost
              FROM fifo_queue1 f
              JOIN stocks s ON f.part_id = s.id
              WHERE f.purchase_date <= ?
              GROUP BY s.part_number
              ORDER BY s.part_number";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$end_date]);
    $stock_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $total_qty = 0;
    $total_value = 0;
    foreach ($stock_data as $row) {
        $total_qty += $row['qty'];
        $total_value += $row['total_cost'];
    }

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="stock_valuation_report_'.date('Y-m-d').'.xls"');
    
    // Excel header
    echo "Stock Valuation Report\n";
    echo "Date Range: ".$start_date." to ".$end_date."\n\n";
    echo "Part Number\tQuantity\tAvg Cost (Rs.)\tTotal Value (Rs.)\n";
    
    // Data rows
    foreach ($stock_data as $item) {
        echo implode("\t", [
            $item['part_number'],
            number_format($item['qty'], 2),
            number_format($item['avg_cost'], 2),
            number_format($item['total_cost'], 2)
        ]) . "\n";
    }
    
    // Totals row
    echo "\n";
    echo implode("\t", [
        "Total",
        number_format($total_qty, 2),
        "",
        number_format($total_value, 2)
    ]) . "\n";
    
    exit;
    
} catch (PDOException $e) {
    die("Error generating export: " . $e->getMessage());
}