<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

$action = $_GET['action'] ?? '';
$returnId = $_GET['id'] ?? 0;

try {
    switch ($action) {
        case 'approve':
            processReturn($returnId, 'approved');
            break;
            
        case 'reject':
            processReturn($returnId, 'rejected');
            break;
            
        case 'cancel':
            cancelReturn($returnId);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
    
    $_SESSION['success'] = "Return processed successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header("Location: supplier_return_list.php");
exit;

function processReturn($returnId, $status) {
    global $conn;
    
    $conn->beginTransaction();
    
    // Update return status
    $stmt = $conn->prepare("
        UPDATE supplier_returns 
        SET status = ?, processed_at = NOW(), processed_by = ?
        WHERE id = ? AND flag = 'active'
    ");
    $stmt->execute([$status, $_SESSION['user_id'], $returnId]);
    
    if ($status === 'approved') {
        // Adjust inventory
        $items = $conn->query("
            SELECT part_id, quantity, cost 
            FROM supplier_return_items 
            WHERE return_id = $returnId AND flag = 'active'
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($items as $item) {
            // Deduct from FIFO
            $conn->exec("
                UPDATE fifo_queue1 
                SET quantity = quantity - {$item['quantity']}
                WHERE part_id = {$item['part_id']}
                ORDER BY purchase_date ASC
                LIMIT 1
            ");
            
            // Update stock levels
            $conn->exec("
                UPDATE stocks 
                SET quantity = quantity - {$item['quantity']}
                WHERE id = {$item['part_id']}
            ");
        }
    }
    
    $conn->commit();
}

function cancelReturn($returnId) {
    global $conn;
    
    $conn->beginTransaction();
    
    // Mark return as inactive
    $stmt = $conn->prepare("
        UPDATE supplier_returns 
        SET flag = 'inactive', deleted_at = NOW(), deleted_by = ?
        WHERE id = ? AND status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id'], $returnId]);
    
    // Mark items as inactive
    $conn->exec("
        UPDATE supplier_return_items 
        SET flag = 'inactive'
        WHERE return_id = $returnId
    ");
    
    $conn->commit();
}
?>