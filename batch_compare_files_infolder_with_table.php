<?php
//File name: batch_copy_files_src_to_destination_folder.php

ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
} else {
    // Set source and destination folders
    $source_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\batch_system\source_folder/';
    $destination_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\batch_system\destination_folder/';
    $log_file = 'batch_compare_files_infolder_with_table.txt'; // Log file 
    // startTask('Custom message: Task is running...', 3000);

    try {
        // Open log file for writing
        $log_handle = fopen($log_file, 'w');
        if (!$log_handle) {
            throw new Exception("Unable to open log file for writing.");
        }

        // Fetch file names from the database
        $stmt = $conn->query("SELECT file_name FROM src_file_list WHERE flag = 'active'");
        $file_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Loop through the files and copy them
        foreach ($file_list as $file) {
            $source_path = $source_folder . $file;
            $destination_path = $destination_folder . $file;

            if (file_exists($source_path)) {
                // $log_entry = "Source path: '$source_path' \n";
                // $log_entry = "Destination path: '$destination_path' \n";
                // $log_entry = " '' \n";
            }

            if (file_exists($source_path)) {
                if (copy($source_path, $destination_path)) {
                    $log_entry = "File '$file' copied successfully.\n";
                } else {
                    $log_entry = "Failed to copy '$file'.\n";
                }
            } else {
                $log_entry = "File '$file' does not exist.\n";
            }

            // Write log entry to the log file
            fwrite($log_handle, $log_entry);
        }

        // Close the log file
        fclose($log_handle);

        // Read and display the log file content
        // $log_content = file_get_contents($log_file);
        // echo nl2br($log_content); // Convert newlines to <br> for display
        // header("Location: ./batch_copy_files_src_to_destination_folder.php");
        header("Location: ./login.php");
        // exit;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Database error occurred. Check the logs for more details.";
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "An error occurred. Check the logs for more details.";
    }
}
