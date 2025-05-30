<?php
require_once('./db.php');
include('header.php');

try {
    // Start transaction
    $conn->beginTransaction();
    
    // Get oldest unprocessed item
    $stmt = $conn->prepare("SELECT * FROM fifo_queue1 WHERE is_processed = 0 ORDER BY position ASC LIMIT 1 FOR UPDATE");
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // Mark as processed
        $update = $conn->prepare("UPDATE fifo_queue1 SET is_processed = 1 WHERE id = ?");
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
    <?php include('footer.php'); ?>
