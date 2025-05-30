<?php
//batch_copy_files_src_to_destination_folder.php
// list the files ina folder and write to text file and
// add records to table

ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection
// include 'msg_window_bootstrap.php'; // Include database connection
?>

<?php
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
}
// header("Location: msg_window_bootstrap.php");
// include 'msgModelBootstrap.php'; // Include the function file
include 'functions_ran.php'; // Include the function file

$msg = "This program needs to set some parameters.";
// msgModelBootstrap($msg); // Call function with your message
?>

<?php
$curr_pgm_name = basename($_SERVER['PHP_SELF']);
$pgm_description = "List of files copied from source folder to destination folder PROD";
$table_1 = "sfl_batch_transfer_all";
$table_2 = "src_file_list_folder";

/* This entry should be included in ./batch_copy_files_src_to_destination_folder.php */
$destination_folder = 'C:\xampp\htdocs\JYI_DEV_6_DashMin\nmc\/';

$parm_string = "./batch_copy_files_src_to_destination_folder.php"
    . "?parm=parm"
    . "&curr_pgm_name=" . $curr_pgm_name
    . "&pgm_description=" . $pgm_description
    . "&table_1=" . $table_1
    . "&table_2=" . $table_2
    ;
// header("Location: " . $parm_string);
$title = "Objects used in this pgm";
// $msg = "Input table : " . $table_1 ."|Output table : " . $table_2;
$msg = "Input table : " . $table_1 ."|Output table : " . $table_2  ."|Destination folder : " . $destination_folder;
$continuePgm = $parm_string;
showModalNotify(title:$title, msg:$msg, continuePgm:$continuePgm, modalWidth:"lg"); // included in functions_ran.php
exit;


?>

<?php
include_once('footer1.php'); 
?>
