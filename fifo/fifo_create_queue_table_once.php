<?php
// create_queue_table.php
require_once('./db.php');

try {
    $sql = "CREATE TABLE IF NOT EXISTS fifo_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_data VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_processed BOOLEAN DEFAULT 0,
        position INT DEFAULT 0
    )";
    
    $conn->exec($sql);
    echo "Queue table created successfully!";
} catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>