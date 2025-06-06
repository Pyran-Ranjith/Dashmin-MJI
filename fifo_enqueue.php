<?php
require_once('./db.php');
include('header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_data'])) {
    try {
        // Get current max position
        $stmt = $conn->query("SELECT IFNULL(MAX(position), 0) FROM fifo_queue1");
        $maxPos = $stmt->fetchColumn();
        $newPos = $maxPos + 1;
        
        // Insert new item
        $stmt = $conn->prepare("INSERT INTO fifo_queue1 (item_data, position) VALUES (?, ?)");
        $stmt->execute([$_POST['item_data'], $newPos]);
        
        header("Location: index.php?message=Item+added+to+queue");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
    <?php include('footer.php'); ?>
