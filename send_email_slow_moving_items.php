<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

ob_start();
include('db.php');
include('header.php');

// Email Configuration
define('DEFAULT_FROM_EMAIL', 'inventory@yourdomain.com');
define('DEFAULT_FROM_NAME', 'Nmc Spareparts Management System');
define('ADMIN_EMAIL', 'ranjithimas@gmail.com');
define('LOW_STOCK_THRESHOLD', 5); // Default threshold for low stock

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = filter_input(INPUT_POST, 'recipient', FILTER_VALIDATE_EMAIL) ? $_POST['recipient'] : ADMIN_EMAIL;
    $custom_message = $_POST['message'] ?? '';
    
    try {
        // Get low stock items
        $stmt = $conn->prepare("
            SELECT s.part_number, s.description, s.stock_quantity, 
                   c.category_name AS make, m.model_name, 
                   l.location_name, r.location_code AS rack_position,
                   COALESCE(s.alert_threshold, :default_threshold) AS threshold
            FROM stocks s
            LEFT JOIN categories c ON s.category_id = c.id
            LEFT JOIN models m ON s.model_id = m.id
            LEFT JOIN locations l ON s.location_id = l.id
            LEFT JOIN racks r ON s.rack_id = r.id
            WHERE s.stock_quantity <= COALESCE(s.alert_threshold, :default_threshold)
            AND s.flag = 'active'
            ORDER BY s.stock_quantity ASC
        ");
        $stmt->execute(['default_threshold' => LOW_STOCK_THRESHOLD]);
        $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build email content
        $subject = "Low Stock Alert - " . date('Y-m-d');
        
        $message = "<h2>Low Stock Alert</h2>";
        if (!empty($custom_message)) {
            $message .= "<p>" . nl2br(htmlspecialchars($custom_message)) . "</p>";
        }
        
        if (!empty($low_stock_items)) {
            $message .= "
            <p>The following items are below their stock threshold:</p>
            <table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th>Part Number</th>
                        <th>Description</th>
                        <th>Make</th>
                        <th>Model</th>
                        <th>Current Qty</th>
                        <th>Threshold</th>
                        <th>Location</th>
                        <th>Rack</th>
                    </tr>
                </thead>
                <tbody>";
            
            foreach ($low_stock_items as $item) {
                $message .= "
                    <tr>
                        <td>{$item['part_number']}</td>
                        <td>{$item['description']}</td>
                        <td>{$item['make']}</td>
                        <td>{$item['model_name']}</td>
                        <td style='color: red; font-weight: bold;'>{$item['stock_quantity']}</td>
                        <td>{$item['threshold']}</td>
                        <td>{$item['location_name']}</td>
                        <td>{$item['rack_position']}</td>
                    </tr>";
            }
            
            $message .= "
                </tbody>
            </table>
            <p>Please take action to restock these items.</p>";
        } else {
            $message .= "<p>No items are currently below stock threshold.</p>";
        }
        
        $message .= "
            <p><em>This is an automated message from the Inventory Management System.</em></p>";
        
        // Email headers
        $headers = [
            'From' => DEFAULT_FROM_NAME . ' <' . DEFAULT_FROM_EMAIL . '>',
            'Reply-To' => DEFAULT_FROM_EMAIL,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        // Flatten headers array
        $headers_string = implode("\r\n", array_map(
            function($k, $v) { return "$k: $v"; },
            array_keys($headers),
            $headers
        ));
        
        // Send email
        $sent = mail($recipient, $subject, $message, $headers_string);
        
        if ($sent) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Low stock alert email sent successfully'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'danger',
                'message' => 'Failed to send email. Check your mail server configuration.'
            ];
        }
        
    } catch (PDOException $e) {
        $_SESSION['notification'] = [
            'type' => 'danger',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
    
    // header('Location: send_email.php');
    header('Location: send_email_slow_moving_items.php');
    exit;
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3><i class="fas fa-envelope"></i> Send Low Stock Alert</h3>
        </div>
        
        <div class="card-body">
            <?php if (isset($_SESSION['notification'])): ?>
                <div class="alert alert-<?= $_SESSION['notification']['type'] ?> alert-dismissible fade show">
                    <?= $_SESSION['notification']['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="recipient" class="form-label">Recipient Email</label>
                    <input type="email" class="form-control" id="recipient" name="recipient" 
                           value="<?= ADMIN_EMAIL ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Additional Message (optional)</label>
                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Low Stock Alert
                </button>
            </form>
        </div>
        
        <div class="card-footer">
            <small class="text-muted">
                This will send an email listing all parts below their stock threshold (default: <?= LOW_STOCK_THRESHOLD ?> units).
            </small>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>