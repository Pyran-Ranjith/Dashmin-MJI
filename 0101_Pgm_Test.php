<?php
session_start();

$prj_folder_mji_ = dirname(__DIR__, 1).'\Dashmin-MJI';
$source_folder = $prj_folder_mji_.'\/';
$file = "file-1.php";
$source_path = $source_folder . $file;
$destination_path = $destination_folder . $file;
if (file_exists($source_path)) {
    if (copy($source_path, $destination_path)) {
        $log_entry = "File '$file' copied successfully.\n";
    } else {
        $log_entry = "Failed to copy '$file'.\n";
    }
} else {
    $log_entry = "File '$file' does not exist.\n";
}
