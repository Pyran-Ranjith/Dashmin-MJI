<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$supplier_id = $_GET['supplier_id'] ?? 0;
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$query = "
    SELECT sr.*, s.supplier_name, u.username AS created_by_name,
           (SELECT COUNT(*) FROM supplier_return_items WHERE return_id = sr.id AND flag = 'active') AS item_count
    FROM supplier_returns sr
    JOIN suppliers s ON sr.supplier_id = s.id
    JOIN users u ON sr.created_by = u.id
    WHERE sr.flag = 'active'
";

$params = [];

if ($status !== 'all') {
    $query .= " AND sr.status = ?";
    $params[] = $status;
}

if ($supplier_id > 0) {
    $query .= " AND sr.supplier_id = ?";
    $params[] = $supplier_id;
}

if (!empty($date_from)) {
    $query .= " AND sr.return_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND sr.return_date <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY sr.return_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get suppliers for filter dropdown
$suppliers = $conn->query("SELECT id, supplier_name FROM suppliers WHERE flag = 'active'")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Supplier Returns</h2>
    
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="all">All Statuses</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="0">All Suppliers</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= $supplier['id'] ?>" <?= $supplier_id == $supplier['id'] ? 'selected' : '' ?>>
                                <?= $supplier['supplier_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?= $date_from ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?= $date_to ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Returns List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($returns)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No returns found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returns as $return): ?>
                                <tr>
                                    <td><?= $return['reference_no'] ?></td>
                                    <td><?= $return['supplier_name'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($return['return_date'])) ?></td>
                                    <td><?= $return['item_count'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $return['status'] === 'approved' ? 'success' : 
                                            ($return['status'] === 'rejected' ? 'danger' : 'warning') 
                                        ?>">
                                            <?= ucfirst($return['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="supplier_return_view.php?id=<?= $return['id'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if ($return['status'] === 'pending' && $_SESSION['user_role'] === 'manager'): ?>
                                            <a href="supplier_return_process.php?action=approve&id=<?= $return['id'] ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="supplier_return_process.php?action=reject&id=<?= $return['id'] ?>" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($return['status'] === 'pending'): ?>
                                            <a href="supplier_return_process.php?action=cancel&id=<?= $return['id'] ?>" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-trash"></i> Cancel
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>