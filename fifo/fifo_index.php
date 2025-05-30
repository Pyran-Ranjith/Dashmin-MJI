<?php
// require_once(__DIR__ . '/../db1.php');
require_once('../db.php');

// Get current queue items
$queueItems = [];
try {
    $stmt = $conn->query("SELECT * FROM fifo_queue WHERE is_processed = 0 ORDER BY position ASC");
    $queueItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple FIFO Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="mb-4">Simple FIFO Queue</h1>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-info"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['processed'])): ?>
                <div class="alert alert-success">
                    Processed item: <?= htmlspecialchars($_GET['processed']) ?>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">Add to Queue</div>
                <div class="card-body">
                    <form action="enqueue.php" method="post">
                        <div class="input-group">
                            <input type="text" name="item_data" class="form-control" placeholder="Enter item" required>
                            <button type="submit" class="btn btn-primary">Enqueue</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Queue Operations</div>
                <div class="card-body">
                    <a href="dequeue.php" class="btn btn-success mb-3">Dequeue Next Item</a>
                    
                    <h5>Current Queue (<?= count($queueItems) ?> items)</h5>
                    <ul class="list-group">
                        <?php foreach ($queueItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($item['item_data']) ?>
                                <span class="badge bg-primary rounded-pill">#<?= $item['position'] ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($queueItems)): ?>
                            <li class="list-group-item text-muted">Queue is empty</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>