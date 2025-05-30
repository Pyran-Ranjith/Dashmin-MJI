<?php
ob_start();
include('db.php');
// include('header1.php');

// Fetch fields
$stmt_ = $conn->prepare("
    SELECT mo.menu_name, mo.menu_link, mt.tag_name AS mt_tag_name, mo.flag FROM menu_options mo 
    LEFT JOIN role_menu_options rmo ON mo.id = rmo.menu_option_id 
    JOIN menu_tags mt ON rmo.menu_tag_id = mt.id
    WHERE rmo.role_id = :role_id
        -- For testing in Phpadmin
        -- WHERE rmo.role_id = 5 
            AND mo.flag = 'active'
    -- and (mo.menu_name LIKE ('%Manage%') ||  mo.menu_name LIKE ('%Dashboard%')) 
        -- and (mt.tag_name LIKE ('%Batch%') ||  mt.tag_name LIKE ('%Batch%')) 
    ORDER BY mo.menu_name, rmo.role_id, rmo.roleorder ASC
    ");
    $role_id1 = 2;
// $stmt_->bindParam(':role_id', $_SESSION['role_id'], PDO::PARAM_INT);
$stmt_->bindParam(':role_id', $role_id1, PDO::PARAM_INT);
$stmt_->execute();
// Fetch all results into the $menu_items array
$menu_items_ = $stmt_->fetchAll(PDO::FETCH_ASSOC);
$menu_items_result_ = $stmt_->fetch(PDO::FETCH_ASSOC);
?>
    <!-- role List -->
    <table class="table">
        <thead>
            <tr>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- <?php // while ($role = $list_roles_result->fetch(PDO::FETCH_ASSOC)) { ?> -->
                <?php foreach ($menu_items_ as $item_): ?>
                    <tr>
                    <td><?php echo $item_['menu_name']; ?></td>
                    <td>
                        <a href="manage_roles.php?view=<?php echo $role['id']; ?>" class="btn btn-info btn-sm">View</a>
                        <a href="manage_roles.php?edit=<?php echo $role['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="manage_roles.php?delete=<?php echo $role['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <!-- <?php //} ?> -->
        </tbody>
    </table>
