<?php
ob_start();
include('db.php');
include('header.php');

// Fetch tables with at least one 'inactive' record
$list_information_schemas_result = $conn->query("
    SELECT table_name 
    FROM information_schema.columns 
    WHERE column_name = 'flag' 
    AND table_schema = 'chtgpt_dspare_dparts_dmanagement_new1'
");
?>

<h2>Manage Inactive Records from Tables</h2>
<table class="table">
    <thead>
        <tr>
            <th>Table Name</th>
            <th>Inactive Records Count</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        while ($information_schema = $list_information_schemas_result->fetch(PDO::FETCH_ASSOC)) {
            $table_name1 = $information_schema['table_name'];
            
            // Check for inactive records count
            $sql = "SELECT COUNT(*) as rec_count1 FROM `$table_name1` WHERE flag = 'inactive'";
            $count_table_result1 = $conn->query($sql);
            $count_table1 = $count_table_result1->fetch(PDO::FETCH_ASSOC);

            // Only display if inactive records exist
            if ($count_table1['rec_count1'] > 0) { 
        ?>
                <tr>
                    <td><?php echo htmlspecialchars($table_name1); ?></td>
                    <td><?php echo $count_table1['rec_count1']; ?></td>
                    <td>
                        <!-- Redirect to a separate file with both table name and action as parameters -->
                        <a href="manage_flags_selective.php?table_name=<?php echo urlencode($table_name1); ?>" 
                           class="btn btn-success btn-sm">Manage Flags</a>
                    </td>
                </tr>
        <?php 
            } 
        } 
        ?>
    </tbody>
</table>

<?php include('footer.php'); ?>
