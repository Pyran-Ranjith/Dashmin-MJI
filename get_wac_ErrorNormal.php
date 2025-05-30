<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

include('db.php');

$part_id = $_GET['part_id'] ?? 0;

try {
    // Current WAC (unsold only)
    $stmt = $conn->prepare("SELECT 
                            SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS current_wac
                           FROM fifo_queue1
                           WHERE part_id = ? 
                           AND is_processed = 0");
    $stmt->execute([$part_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Historical WAC (all items)
    $stmt = $conn->prepare("SELECT 
                            SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS historical_wac
                           FROM fifo_queue1
                           WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $historical = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode([
        'current_wac' => $current['current_wac'],
        'historical_wac' => $historical['historical_wac']
    ]);
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => $e->getMessage()]);
}