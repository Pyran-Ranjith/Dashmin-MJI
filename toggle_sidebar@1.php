<?php
session_start();

// Set sidebar state in session
if (!isset($_SESSION['sidebarHidden'])) {
    $_SESSION['sidebarHidden'] = "false"; // Sidebar visible by default
}

$sidebarHidden = $_SESSION['sidebarHidden'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Toggle Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #333;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s;
            padding: 15px;
        }

        .sidebar.hidden {
            display: none; /* Hide sidebar */
        }

        /* Main Content */
        #main-content {
            margin-left: 250px; /* Shifted when sidebar is visible */
            padding: 20px;
            transition: margin-left 0.3s;
        }

        #main-content.full-width {
            margin-left: 0; /* Full width when sidebar is hidden */
        }

        /* Toggle Button */
        .extra-toggler {
            position: fixed;
            top: 10px;
            right: 10px;
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
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar <?php echo $sidebarHidden === "true" ? 'hidden' : ''; ?>">
    <h3>Sidebar Menu</h3>
    <ul>
        <li><a href="#" class="text-white">Dashboard</a></li>
        <li><a href="#" class="text-white">Settings</a></li>
        <li><a href="#" class="text-white">Profile</a></li>
    </ul>
</div>

<!-- Main Content -->
<div id="main-content" class="<?php echo $sidebarHidden === "true" ? 'full-width' : ''; ?>">
    <!-- Sidebar Toggler -->
    <a href="#" id="sidebar-toggler" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>

    <!-- Toggle Sidebar Button -->
    <a href="#" id="toggle-toggler" class="extra-toggler">
        <i class="fa fa-bars"></i> Show/Hide Sidebar
    </a>

    <h1>Welcome to the Page</h1>
    <p>This is a test page for sidebar toggling with PHP sessions.</p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.getElementById("main-content");
    const toggleToggler = document.getElementById("toggle-toggler");

    toggleToggler.addEventListener("click", function() {
        // Toggle sidebar visibility
        sidebar.classList.toggle("hidden");
        mainContent.classList.toggle("full-width");

        // Determine new state for session
        let isHidden = sidebar.classList.contains("hidden") ? "true" : "false";

        // Send AJAX request to update PHP session
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "toggle_sidebar@1.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("sidebarHidden=" + isHidden);
    });
});
</script>

</body>
</html>
