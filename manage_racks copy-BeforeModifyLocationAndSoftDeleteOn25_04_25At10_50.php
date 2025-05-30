<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include('db.php');
include('header.php');

// Get filter parameter
$part_id = $_GET['part_id'] ?? '';

try {
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_rack'])) {
            // Add new rack
            $stmt = $conn->prepare("INSERT INTO racks (floor, rack_number, row_number, column_number, side, description) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['floor'],
                $_POST['rack_number'],
                $_POST['row_number'],
                $_POST['column_number'],
                $_POST['side'],
                $_POST['description']
            ]);
            $_SESSION['message'] = "Rack added successfully!";
        } elseif (isset($_POST['update_rack'])) {
            // Update rack
            $stmt = $conn->prepare("UPDATE racks SET 
                                  floor = ?,
                                  rack_number = ?,
                                  row_number = ?,
                                  column_number = ?,
                                  side = ?,
                                  description = ?
                                  WHERE id = ?");
            $stmt->execute([
                $_POST['floor'],
                $_POST['rack_number'],
                $_POST['row_number'],
                $_POST['column_number'],
                $_POST['side'],
                $_POST['description'],
                $_POST['rack_id']
            ]);
            $_SESSION['message'] = "Rack updated successfully!";
        } elseif (isset($_POST['delete_rack'])) {
            // Delete rack
            $stmt = $conn->prepare("DELETE FROM racks WHERE id = ?");
            $stmt->execute([$_POST['rack_id']]);
            $_SESSION['message'] = "Rack deleted successfully!";
        }
        header("Location: manage_racks.php");
        exit;
    }

    // Base query for racks
    $query = "SELECT r.*, 
              COUNT(fq.id) AS assigned_parts,
              GROUP_CONCAT(DISTINCT s.part_number ORDER BY s.part_number SEPARATOR ', ') AS part_numbers
              FROM racks r
              LEFT JOIN fifo_queue1 fq ON r.id = fq.rack_id
              LEFT JOIN stocks s ON fq.part_id = s.id";

    $params = [];
    
    // Apply part filter if specified
    if (!empty($part_id)) {
        $query .= " WHERE fq.part_id = ?";
        $params[] = $part_id;
    }

    $query .= " GROUP BY r.id ORDER BY r.floor, r.rack_number, r.row_number, r.column_number, r.side";

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $racks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all parts for filter dropdown
    $parts = $conn->query("SELECT id, part_number FROM stocks ORDER BY part_number")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <h2>Manage Rack Locations</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>Filter Racks</h3>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Filter by Part Number</label>
                    <select name="part_id" class="form-select">
                        <option value="">All Racks</option>
                        <?php foreach ($parts as $part): ?>
                            <option value="<?= $part['id'] ?>" <?= $part['id'] == $part_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($part['part_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                    <a href="manage_racks.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Racks Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Rack Locations</h3>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRackModal">
                <i class="fas fa-plus"></i> Add New Rack
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Location Code</th>
                            <th>Description</th>
                            <th>Assigned Parts</th>
                            <th>Part Numbers</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($racks)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No racks found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($racks as $rack): ?>
                                <tr>
                                    <td><?= $rack['location_code'] ?></td>
                                    <td><?= htmlspecialchars($rack['description']) ?></td>
                                    <td class="text-center"><?= $rack['assigned_parts'] ?></td>
                                    <td><?= $rack['part_numbers'] ? htmlspecialchars($rack['part_numbers']) : 'None' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-rack" 
                                                data-id="<?= $rack['id'] ?>"
                                                data-floor="<?= $rack['floor'] ?>"
                                                data-rack="<?= $rack['rack_number'] ?>"
                                                data-row="<?= $rack['row_number'] ?>"
                                                data-column="<?= $rack['column_number'] ?>"
                                                data-side="<?= $rack['side'] ?>"
                                                data-desc="<?= htmlspecialchars($rack['description']) ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="rack_id" value="<?= $rack['id'] ?>">
                                            <button type="submit" name="delete_rack" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this rack?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
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

<!-- Add Rack Modal -->
<div class="modal fade" id="addRackModal" tabindex="-1" aria-labelledby="addRackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRackModalLabel">Add New Rack Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Floor</label>
                        <input type="number" class="form-control" name="floor" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rack Number</label>
                        <input type="number" class="form-control" name="rack_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Row Number</label>
                        <input type="number" class="form-control" name="row_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Column Number</label>
                        <input type="number" class="form-control" name="column_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Side</label>
                        <select class="form-select" name="side" required>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                            <option value="middle">Middle</option>
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_rack" class="btn btn-primary">Save Rack</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rack Modal -->
<div class="modal fade" id="editRackModal" tabindex="-1" aria-labelledby="editRackModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post">
                <input type="hidden" name="rack_id" id="edit_rack_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRackModalLabel">Edit Rack Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Floor</label>
                        <input type="number" class="form-control" name="floor" id="edit_floor" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rack Number</label>
                        <input type="number" class="form-control" name="rack_number" id="edit_rack_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Row Number</label>
                        <input type="number" class="form-control" name="row_number" id="edit_row_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Column Number</label>
                        <input type="number" class="form-control" name="column_number" id="edit_column_number" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Side</label>
                        <select class="form-select" name="side" id="edit_side" required>
                            <option value="left">Left</option>
                            <option value="right">Right</option>
                            <option value="middle">Middle</option>
                            <option value="top">Top</option>
                            <option value="bottom">Bottom</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_rack" class="btn btn-primary">Update Rack</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit button clicks
document.querySelectorAll('.edit-rack').forEach(button => {
    button.addEventListener('click', function() {
        const rackId = this.getAttribute('data-id');
        const floor = this.getAttribute('data-floor');
        const rackNumber = this.getAttribute('data-rack');
        const rowNumber = this.getAttribute('data-row');
        const columnNumber = this.getAttribute('data-column');
        const side = this.getAttribute('data-side');
        const description = this.getAttribute('data-desc');
        
        document.getElementById('edit_rack_id').value = rackId;
        document.getElementById('edit_floor').value = floor;
        document.getElementById('edit_rack_number').value = rackNumber;
        document.getElementById('edit_row_number').value = rowNumber;
        document.getElementById('edit_column_number').value = columnNumber;
        document.getElementById('edit_side').value = side;
        document.getElementById('edit_description').value = description;
        
        var editModal = new bootstrap.Modal(document.getElementById('editRackModal'));
        editModal.show();
    });
});
</script>

<?php include('footer.php'); ?>