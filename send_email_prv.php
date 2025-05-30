<?php
// Database connection (for getting recipient if needed)
// $host = 'localhost';
// $user = 'root';
// $pass = '';
// $db = 'your_database';
// $conn = mysqli_connect($host, $user, $pass, $db);
include('db.php');
include('header.php');

// Simple email sending function
function sendEmail($to, $subject, $message) {
    $headers = "From: inventory@yourcompany.com\r\n";
    $headers .= "Reply-To: no-reply@yourcompany.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Example usage with database integration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? 'manager@yourcompany.com';
    $subject = "Stock Alert Notification";
    
    // Get low stock items from database
    $sql = "SELECT part_number , stock_quantity FROM stocks WHERE stock_quantity <= 5";
    $stmt = $conn->prepare($sql);

    // $result = mysqli_query($conn, $query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $message = "<h2>Low Stock Alert</h2><ul>";
    while ($row = mysqli_fetch_assoc($result)) {
        $message .= "<li>{$row['part_name']} - Only {$row['quantity']} remaining</li>";
    }
    $message .= "</ul><p>Please restock soon!</p>";
    
    if (sendEmail($email, $subject, $message)) {
        echo "<div class='alert alert-success'>Email sent successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to send email. Check your XAMPP mail configuration.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Notification System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Send Low Stock Alert</h2>
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Recipient Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="manager@yourcompany.com" required>
            </div>
            <button type="submit" class="btn btn-primary">Send Alert Email</button>
        </form>
        
        <?php if(isset($_POST['email'])): ?>
        <div class="mt-4">
            <h4>Email Preview:</h4>
            <div class="border p-3">
                <?= nl2br($message) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php include('footer.php'); ?>
</body>

</html>