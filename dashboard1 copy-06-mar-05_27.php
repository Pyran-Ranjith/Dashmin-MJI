<?php
ob_start();
include('db.php');
include('header.php');
 
// Ensure only authorized users (admin/staff) can access this page
// if (!isset($_SESSION['user_id'])) {
//     header ("Location: ./login.php");
//     exit;
// } else {
// Fetch data for dashboard
$total_parts = $conn->query("SELECT COUNT(*) FROM stocks")->fetchColumn();
$total_sales = $conn->query("SELECT SUM(total_price) FROM sales")->fetchColumn();
?>

<div class="container">
    <h2>Dashboard</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Parts in Stock: <?php echo $total_parts; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Sales: $<?php echo number_format($total_sales, 2); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
// $stmt->execute([$user_id1]);
// $orders = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM stocks");
$stocks_result = $stmt->fetchAll();
?>
<noscript>
<table>
    <tr><th>Part Number</th><th>Description</th><th>Cost</th></tr>
    <?php foreach ($stocks_result as $stock): ?>
        <tr>
            <td><?= $stock['part_number'] ?></td>
            <td><?= $stock['description'] ?></td>
            <td>$<?= number_format($order['cost'], 2) ?></td>
            <!-- <td id="status_<?= $order['id'] ?>"> <?= $order['status'] ?> </td> -->
        </tr>
    <?php endforeach; ?>
</table>
?>
</noscript>


<?php include 'footer.php'; ?>
<?php //} ?>
