<?php
//batch_copy_files_src_to_destination_folder.php
// list the files ina folder and write to text file and
// add records to table

ob_start();
session_start();
include 'header.php'; // Include header
include 'db.php'; // Include database connection


function copyFolder($source, $destination)
{
    // $source = dirname($source);
    // $destination = dirname($destination);
    // Ensure the destination folder exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    // Scan the source folder
    $files = scandir($source);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
        $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

        if (is_dir($sourcePath)) {
            copyFolder($sourcePath, $destinationPath); // Recursive call for subfolders
        } else {
            copy($sourcePath, $destinationPath);
        }
    }
}

?>

<?php
// Ensure only authorized users (admin/staff) can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit;
} else {
    // Defaults
    // Get the current script name without the .php extension
    $curr_pgm_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
    $pgm_description = "List of files copied from source folder to destination folder";
    // $source_folder = 'C:\xampp\htdocs\CHRGPT\0103-CHTGPT-Vehicle-Spare-Parts-Management-System\/';
    // $source_folder = $prj_folder_mji_.'/';
    // $source_folder = $prj_folder_mji_;
    $source_folder = 'C:\xampp\htdocs\JYI_DEV_6_DashMin\Dashmin-MJI\/';

    // $destination_folder = 'C:\xampp\htdocs\NSPMS_DEV_2\NSPMS\/';
    // $destination_folder = 'C:\xampp\htdocs\JYI_DEV_6_DashMin\Nmc\/'; /* Namarathne */
    $destination_folder = 'C:\xampp\htdocs\JYI_DEV_6_DashMin\Demo\/'; /* Demo */
    $log_file = $curr_pgm_name . '_log.txt'; // Log file path
    $table_1 = 'sfl_batch_transfer_remote_nonroot'; // Source table
    // $table_1 = 'sfl_batch_transfer_remote_nonroot'; // Source table
    $table_2 = 'src_file_list_folder';

    // Check for parameters
    if (isset($_GET['parm'])) {
        if (isset($_GET['curr_pgm_name'])) {
            $curr_pgm_name = $_GET['curr_pgm_name'];
        }
        if (isset($_GET['pgm_description'])) {
            $pgm_description = $_GET['pgm_description'];
        }
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
            $table_1 = $_GET['table_1'];
        }
        if (isset($_GET['table_2'])) {
            $table_2 = $_GET['table_2'];
        }
    }
    
    try {
        // Open log file for writing
        $log_handle = fopen($log_file, 'w');
        if (!$log_handle) {
            throw new Exception("Unable to open log file for writing.");
        }

        // Fetch file names from the database
        $sql = "SELECT file_name FROM " . $table_1 . " WHERE flag = 'active'";
        $stmt = $conn->query($sql);
        $db_files_1 = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Fetch entries from the 2nd-table
        $stmt = $conn->query("SELECT file_name FROM " . $table_2); // Replace 'your_table' with your table name
        $db_files_2 = $stmt->fetchAll(PDO::FETCH_COLUMN);
        // Add record to 2nd table
        $sql1 = "DELETE FROM  " . $table_2 . " WHERE 1 ";
        $conn->query($sql1);

        // Loop through the files and copy them
        $i = 0;
        foreach ($db_files_1 as $file) {
            $source_path = $source_folder . $file;
            // $destination_path = $destination_folder . $file;
            $destination_path = $destination_folder . DIRECTORY_SEPARATOR . $file;

            if (file_exists($source_path)) {
                // $log_entry = "Source path: '$source_path' \n";
                // $log_entry = "Destination path: '$destination_path' \n";
                // $log_entry = " '' \n";
                // Add record to 2nd table
                // $sql1 = "INSERT INTO " . $table_2 . " (file_name)
                // VALUES ('$file')";
                // $conn->query($sql1);
            }

            // Ensure the parent folder of the destination file exists
            $destination_dir = dirname($destination_path);
            if (!is_dir($destination_dir)) {
                mkdir($destination_dir, 0777, true);
            }
            $basename = basename($file);
            $dirname = dirname($file);

            $found = true;
            $parts = explode("*.*", $file);
            if (count($parts) == 2) {
                $left = $parts[0];
                $right = $parts[1];
            } else {
                $found = false;
            }

            if (!$found) {
                if (file_exists($source_path)) {
                    if (copy($source_path, $destination_path)) {
                        $i++;
                        // $log_entry = "File '$file' copied successfully.\n";
                        // Add record to 2nd table
                        // $file_entry = $file . " Not Found";
                        $sql1 = "INSERT INTO " . $table_2 . " (file_name)
                VALUES ('$file')";
                        $conn->query($sql1);
                    } else {
                        // $log_entry = "Failed to copy '$file'.\n";
                    }
                } else {
                    // copyFolder($source_path, $destination_path); // Recursive call for subfolders
                    if (file_exists($source_path)) {
                        $dirname_source_path = dirname($source_path);
                        $dirname_destination_path = dirname($destination_path);
                        copyFolder(dirname($source_path), dirname($destination_path)); // Recursive call for subfolders
                        $file_entry = $file;
                    } else {
                        $file_entry = $file . " Not Found";
                    }
                    // Add record to 2nd table
                    $sql1 = "INSERT INTO " . $table_2 . " (file_name)
                VALUES ('$file_entry')";
                    $conn->query($sql1);
                }
            } else {
                // $log_entry = "File '$file' does not exist.\n";
                // if (file_exists($source_path)) {
                $sql1 = "INSERT INTO " . $table_2 . " (file_name)
                    VALUES ('$file')";
                $conn->query($sql1);
                $dirname_source_path = dirname($source_path);
                $dirname_destination_path = dirname($destination_path);
                copyFolder(dirname($source_path), dirname($destination_path)); // Recursive call for subfolders
                $file_entry = $file;
                // }
            }

            // Write log entry to the log file
            // fwrite($log_handle, $log_entry);
        }

        // Close the log file
        fclose($log_handle);

        // Read and display the log file content
        // $log_content = file_get_contents($log_file);
        // echo nl2br($log_content); // Convert newlines to <br> for display
        // header("Location: ./batch_copy_files_src_to_destination_folder.php");
        // header("Location: ./login.php");
        // exit;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Database error occurred. Check the logs for more details.";
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "An error occurred. Check the logs for more details.";
    }
}
// $curr_pgm_name = basename($_SERVER['PHP_SELF']);
// $pgm_description = "List of files copied from source folder to destination folder";

$status = $i . " files copied successfully.";
$parm_string = "./maintain_src_file_list.php"
    . "?calling_pgm_name=" . $curr_pgm_name
    . "|description=" . $pgm_description
    . "|source_folder=" . $source_folder
    . "|destination_folder=" . $destination_folder
    . "|tablesrc=" . $table_2
    . "|status=" . $status;
header("Location: " . $parm_string);
exit;
