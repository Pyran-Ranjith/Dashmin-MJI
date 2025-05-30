<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');

$returnId = $_GET['id'] ?? 0;

// Get return details
$stmt = $conn->prepare("
    SELECT sr.*, s.supplier_name, s.address, s.phone, s.email,
           u.username AS created_by_name
    FROM supplier_returns sr
    JOIN suppliers s ON sr.supplier_id = s.id
    JOIN users u ON sr.created_by = u.id
    WHERE sr.id = ? AND sr.flag = 'active'
");
$stmt->execute([$returnId]);
$return = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$return) {
    die("Return not found or deleted");
}

// Get return items
$items = $conn->query("
    SELECT sri.*, s.part_number, s.description
    FROM supplier_return_items sri
    JOIN stocks s ON sri.part_id = s.id
    WHERE sri.return_id = $returnId AND sri.flag = 'active'
")->fetchAll(PDO::FETCH_ASSOC);

// Generate PDF or HTML output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Return #<?= $return['reference_no'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; }
        .document-title { font-size: 18px; margin: 10px 0; }
        .details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; font-size: 12px; }
        .signature { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Your Company Name</div>
        <div class="document-title">SUPPLIER RETURN</div>
    </div>
    
    <div class="details">
        <table width="100%">
            <tr>
                <td width="50%">
                    <strong>Return No:</strong> <?= $return['reference_no'] ?><br>
                    <strong>Date:</strong> <?= date('d/m/Y', strtotime($return['return_date'])) ?><br>
                    <strong>Status:</strong> <?= ucfirst($return['status']) ?>
                </td>
                <td width="50%">
                    <strong>Supplier:</strong> <?= $return['supplier_name'] ?><br>
                    <strong>Address:</strong> <?= $return['address'] ?><br>
                    <strong>Contact:</strong> <?= $return['phone'] ?>
                </td>
            </tr>
        </table>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Part No</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= $item['part_number'] ?></td>
                    <td><?= $item['description'] ?></td>
                    <td class="text-right"><?= number_format($item['quantity'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['cost'], 2) ?></td>
                    <td class="text-right"><?= number_format($item['quantity'] * $item['cost'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total:</th>
                <th class="text-right">
                    <?= number_format(array_sum(array_map(function($item) { 
                        return $item['quantity'] * $item['cost']; 
                    }, $items)), 2) ?>
                </th>
            </tr>
        </tfoot>
    </table>
    
    <div class="reason">
        <strong>Return Reason:</strong> <?= $return['return_reason'] ?>
    </div>
    
    <div class="signature">
        <table width="100%">
            <tr>
                <td width="50%">
                    <strong>Prepared By:</strong><br><br>
                    ________________________<br>
                    <?= $return['created_by_name'] ?><br>
                    <?= date('d/m/Y H:i', strtotime($return['created_at'])) ?>
                </td>
                <td width="50%">
                    <?php if ($return['status'] === 'approved'): ?>
                        <strong>Approved By:</strong><br><br>
                        ________________________<br>
                        <?= $return['processed_by'] ?><br>
                        <?= date('d/m/Y H:i', strtotime($return['processed_at'])) ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        Document generated on <?= date('d/m/Y H:i') ?>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>