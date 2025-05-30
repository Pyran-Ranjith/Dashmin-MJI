<?php
$prj_folder_mji_ = dirname(__DIR__, 1) . '\Dashmin-MJI';
$source_folder = $prj_folder_mji_ . '\/';
$file_path = "dammika.png"; // File inside folder-1
$source_path = $source_folder . $file_path;
// $destination_folder = 'C:\destination\path'; // Existing destination folder
$destination_folder = 'C:\xampp\htdocs\JYI_DEV_6_DashMin\Dashmin_MJI_Remote\/';
$destination_path = $destination_folder . DIRECTORY_SEPARATOR . $file_path;

// Ensure the parent folder of the destination file exists
$destination_dir = dirname($destination_path);
if (!is_dir($destination_dir)) {
    mkdir($destination_dir, 0777, true);
}

// Copy the file if it exists
if (file_exists($source_path)) {
    if (copy($source_path, $destination_path)) {
        echo "File '$file_path' copied successfully.\n";
    } else {
        echo "Failed to copy '$file_path'.\n";
    }
} else {
    echo "File '$file_path' does not exist.\n";
}
?>
