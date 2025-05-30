<?php
/* CUSTOMER DASHBOARD (dashboard2.php) */
ob_start();
include('db.php');
include('header.php');
// if (!isset($_SESSION['user'])) {
//     header("Location: login.php");
//     exit();
// }
$user_id1 = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
$stmt->execute([$user_id1]);
$orders = $stmt->fetchAll();
?>
<h2>My Orders</h2>
<table>
    <tr><th>Order ID</th><th>Total Price</th><th>Status</th></tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td>$<?= number_format($order['total_price'], 2) ?></td>
            <td id="status_<?= $order['id'] ?>"> <?= $order['status'] ?> </td>
        </tr>
    <?php endforeach; ?>
</table>

<script>
function updateStatus() {
    fetch('track_order_ajax.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(order => {
                document.getElementById('status_' + order.id).innerText = order.status;
            });
        });
}
setInterval(updateStatus, 5000);
</script>
