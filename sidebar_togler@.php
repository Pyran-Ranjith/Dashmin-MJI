<?php
session_start();
$sidebarHidden = isset($_SESSION['sidebarHidden']) ? $_SESSION['sidebarHidden'] : "false";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Toggle</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include Bootstrap if needed -->
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar <?php echo $sidebarHidden === "true" ? 'hidden' : ''; ?>">
    <!-- Sidebar content here -->
</div>

<!-- Main Content -->
<div id="main-content" class="<?php echo $sidebarHidden === "true" ? 'full-width' : ''; ?>">
    <a href="#" id="extra-sidebar-toggler" class="extra-toggler">
        <i class="fa fa-bars"></i>
    </a>
    <h1>Main Content Here</h1>
</div>

<!-- Add CSS for Styling -->
<style>
    /* Sidebar */
    .sidebar {
        width: 250px;
        background: #333;
        color: white;
        padding: 15px;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        transition: all 0.3s;
    }
    .sidebar.hidden {
        display: none; /* Completely hide sidebar */
    }

    /* Main Content */
    #main-content {
        margin-left: 250px; /* Adjust for sidebar */
        transition: margin-left 0.3s;
        padding: 20px;
    }
    #main-content.full-width {
        margin-left: 0; /* Full width when sidebar is hidden */
    }

    /* Toggle Button */
    .extra-toggler {
        position: fixed;
        top: 10px;
        left: 10px;
        background: #007bff;
        color: white;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        z-index: 1000;
    }
    .extra-toggler:hover {
        background: #0056b3;
    }
</style>

<!-- JavaScript to Toggle Sidebar and Send AJAX Request -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const extraToggler = document.getElementById("extra-sidebar-toggler");

        extraToggler.addEventListener("click", function() {
            // Toggle sidebar visibility
            sidebar.classList.toggle("hidden");
            mainContent.classList.toggle("full-width");

            // Determine new sidebar state
            let isHidden = sidebar.classList.contains("hidden") ? "true" : "false";

            // Send AJAX request to update PHP session
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "toggle_sidebar.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("sidebarHidden=" + isHidden);
        });
    });
</script>

</body>
</html>
