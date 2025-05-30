<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require 'db.php';

try {
    $part_id = filter_input(INPUT_GET, 'part_id', FILTER_VALIDATE_INT);
    
    if (!$part_id || $part_id < 1) {
        throw new Exception('Invalid part ID');
    }

    // Current WAC (unsold only)
    $stmt = $conn->prepare("SELECT 
            COALESCE(SUM(quantity * cost), 0) AS total_cost,
            COALESCE(SUM(quantity), 0) AS total_quantity,
            CASE WHEN SUM(quantity) > 0 
                 THEN SUM(quantity * cost) / SUM(quantity) 
                 ELSE 0 
            END AS current_wac
        FROM fifo_queue1
        WHERE part_id = ? AND is_processed = 0");
    $stmt->execute([$part_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    // Historical WAC (all items)
    $stmt = $conn->prepare("SELECT 
            COALESCE(SUM(quantity * cost), 0) AS total_cost,
            COALESCE(SUM(quantity), 0) AS total_quantity,
            CASE WHEN SUM(quantity) > 0 
                 THEN SUM(quantity * cost) / SUM(quantity) 
                 ELSE 0 
            END AS historical_wac
        FROM fifo_queue1
        WHERE part_id = ?");
    $stmt->execute([$part_id]);
    $historical = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'current_wac' => $current['current_wac'],
        'current_quantity' => $current['total_quantity'],
        'historical_wac' => $historical['historical_wac'],
        'historical_quantity' => $historical['total_quantity']
    ]);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}