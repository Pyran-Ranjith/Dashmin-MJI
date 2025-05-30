<?php
//batch_copy_files_src_to_destination_folder.php
// list te files ina folder and write to text file and
// add records to table

ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection
?>
<!-- Bootstrap Modal -->

<?php
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
} else {
    // Defaults
    // Get the current script name without the .php extension
    $curr_pgm_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

    // $source_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\/';
    // $destination_folder = 'C:\xampp\htdocs\NSPMS_DEV_2\NSPMS\/';
 
    $source_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\batch_system\source_folder\/';
    $destination_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\batch_system\destination_folder\/';
   
    $log_file = $curr_pgm_name . '_log.txt'; // Log file path
    // startTask('Custom message: Task is running...', 3000);
    $table_1 = 'src_file_list_backup1'; // Source table
    $table_2 = 'src_file_list_folder';

    // Folder path
  $folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System/'; // Replace with your folder path
  $table = 'src_file_list_backup1';
  $table_2 = 'src_file_list_folder';

    // Check for parameters
    if (isset($_GET['parm'])) {
        if (isset($_GET['source_folder'])) {
            $source_folder = $_GET['source_folder'];
        }
        if (isset($_GET['destination_folder'])) {
            $destination_folder = $_GET['destination_folder'];
        }
        if (isset($_GET['log_file'])) {
            $log_file = $_GET['log_file'];
        }
        if (isset($_GET['table_2'])) {
            $table_2 = $_GET['table_2'];
        }
    }

        if (is_dir($folder)) {
            /* Get all files in the folder */
            $all_items = scandir($folder);
            $files = [];
    
            // Filter out folders
            foreach ($all_items as $item) {
                if (is_file($folder . $item)) {
                    $files[] = $item;
                }
            }
    
            // Fetch entries from the 1st-table
            $stmt = $conn->query("SELECT file_name FROM " . $table); // Replace 'your_table' with your table name
            $db_files = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $db_files_arr = [];
            foreach ($db_files as $file) {
                $db_files_arr[] = $file;
            }

            // Fetch entries from the 2nd-table
            $stmt = $conn->query("SELECT file_name FROM " . $table_2); // Replace 'your_table' with your table name
            $db_files_2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $sql1 = "DELETE FROM  " . $table_2 . " WHERE 1 ";
            $conn->query($sql1);
    
            // Find files that are not in the database
            // $missing_files = array_diff($files, $db_files);
            $files = scandir($folder);
            // $missing_files = array_diff($files, array('.', '..')); // Remove . and ..
            $missing_files = array_diff($files, array('..')); // Remove . and ..
            $missing_files = array_diff($missing_files,$db_files_arr); 
        
            // Display the missing files
            echo '<div class="container mt-3">';
            echo '<h4>Folder: ' . $folder . '</h4>'; //BM:FITD
            echo '<h4>Table: ' . $table . '</h4>'; //BM:FITD
            echo '<hr>'; //BM:FITD
            echo '<h4>Files not in the database:</h4>';
            if (!empty($missing_files)) {
                echo '<ul class="list-group">';
                foreach ($missing_files as $file) {
                    echo '<li class="list-group-item">' . htmlspecialchars($file) . '</li>';
                    // Add record to 2nd table
                    $sql1 = "INSERT INTO " . $table_2. " (file_name)
                    VALUES ('$file')";
                    $conn->query($sql1);
                }
                echo '</ul>';
            } else {
                echo '<div class="alert alert-success">All files are already in the database.</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">The specified folder does not exist.</div>';
        }
    // } catch (PDOException $e) {
    //     echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    // }
    




    // try {
    //     // Open log file for writing
    //     $log_handle = fopen($log_file, 'w');
    //     if (!$log_handle) {
    //         throw new Exception("Unable to open log file for writing.");
    //     }

    //     // Fetch file names from the database
    //     $stmt = $conn->query("SELECT file_name FROM ".$table_1. " WHERE flag = 'active'");
    //     $file_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

    //     // Fetch entries from the 2nd-table
    //     $stmt = $conn->query("SELECT file_name FROM " . $table_2); // Replace 'your_table' with your table name
    //     $db_files_2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
    //     // Add record to 2nd table
    //     $sql1 = "DELETE FROM  " . $table_2 . " WHERE 1 ";
    //     $conn->query($sql1);

    //     // Loop through the files and copy them
    //     foreach ($file_list as $file) {
    //         $source_path = $source_folder . $file;
    //         $destination_path = $destination_folder . $file;

    //         if (file_exists($source_path)) {
    //             $log_entry = "Source path: '$source_path' \n";
    //             $log_entry = "Destination path: '$destination_path' \n";
    //             $log_entry = " '' \n";
    //             // Add record to 2nd table
    //             $sql1 = "INSERT INTO " . $table_2 . " (file_name)
    //             VALUES ('$file')";
    //             $conn->query($sql1);
    //         }

    //         if (file_exists($source_path)) {
    //             if (copy($source_path, $destination_path)) {
    //                 $log_entry = "File '$file' copied successfully.\n";
    //             } else {
    //                 $log_entry = "Failed to copy '$file'.\n";
    //             }
    //         } else {
    //             $log_entry = "File '$file' does not exist.\n";
    //         }

    //         // Write log entry to the log file
    //         fwrite($log_handle, $log_entry);
    //     }

    //     // Close the log file
    //     fclose($log_handle);

    //     // Read and display the log file content
    //     // $log_content = file_get_contents($log_file);
    //     // echo nl2br($log_content); // Convert newlines to <br> for display
    //     // header("Location: ./batch_copy_files_src_to_destination_folder.php");
    //     header("Location: ./login.php");
    //     // exit;

    // } catch (PDOException $e) {
    //     error_log("Database error: " . $e->getMessage());
    //     echo "Database error occurred. Check the logs for more details.";
    // } catch (Exception $e) {
    //     error_log("Error: " . $e->getMessage());
    //     echo "An error occurred. Check the logs for more details.";
    // }
}
// header("Location: ./maintain_src_file_list.php?tablesrc=" .$table_1. "&table_2= .$table_2. &description=Description: batch_compair_table_and_folder_list_difference\n Table:" );
// //   $parm_str = "maintain_src_file_list.php?table_1=src_file_list&description=Helo how are you";
// //   header("Location: " . $parm_str);
// exit;

// <?php
// Properly encode the description parameter to handle special characters and newlines
$description = "Description: batch_compair_table_and_folder_list_difference|Table: ". $table_2;
$encodedDescription = urlencode($description);
$pgm_description = "List of files in folder differnt from tabe";

$curr_pgm_name = basename($_SERVER['PHP_SELF']);
$parm_string = "./maintain_src_file_list.php"
    ."?calling_pgm_name=".$curr_pgm_name
    ."|description=".$pgm_description
    ."|source_folder=".$source_folder
    ."|destination_folder=".$destination_folder
    ."|tablesrc=" . $table_2
    ;
header("Location: " .$parm_string);
exit;
?>

