<?php
//batch_copy_files_src_to_destination_folder.php
// list the files ina folder and write to text file and
// add records to table

ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection
?>

<?php
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
}
$curr_pgm_name = basename($_SERVER['PHP_SELF']);
$pgm_description = "List of files copied from source folder to destination folder test";

$parm_string = "./batch_copy_files_src_to_destination_folder.php"
    . "?parm=parm"
    . "&curr_pgm_name=" . $curr_pgm_name
    . "&pgm_description=" . $pgm_description
    . "&source_folder=C:\AA_tEST\source_folder/"
    . "&destination_folder=C:\AA_tEST/"
    . "&table_1=src_file_list_test_table_1"
    . "&table_2=src_file_list_test_table_2";
header("Location: " . $parm_string);
exit;
