<?php
include('db.php');
$part_id = 36; // Test with a known part ID
$stmt = $conn->prepare("SELECT SUM(quantity * cost) / NULLIF(SUM(quantity), 0) AS wac FROM fifo_queue1 WHERE part_id = ? AND is_processed = 0");
$stmt->execute([$part_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($result);
?>