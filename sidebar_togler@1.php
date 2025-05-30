<?php
session_start();

// Set default session variable (hidden initially)
if (!isset($_SESSION['sidebarTogglerHidden'])) {
    $_SESSION['sidebarTogglerHidden'] = "true"; // Initially hidden
}

$sidebarTogglerHidden = $_SESSION['sidebarTogglerHidden'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Toggler Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <style>
        /* Extra Toggle Button */
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

<!-- Sidebar Toggler -->
<a href="#" id="sidebar-toggler" class="sidebar-toggler flex-shrink-0 <?php echo $sidebarTogglerHidden === "true" ? 'd-none' : ''; ?>">
    <i class="fa fa-bars"></i>
</a>

<!-- Button to Toggle the Sidebar Toggler -->
<a href="#" id="toggle-toggler" class="extra-toggler">
    <i class="fa fa-bars"></i> Show/Hide Toggler
</a>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const sidebarToggler = document.getElementById("sidebar-toggler");
    const toggleToggler = document.getElementById("toggle-toggler");

    toggleToggler.addEventListener("click", function() {
        // Toggle Bootstrap's `d-none` class
        sidebarToggler.classList.toggle("d-none");

        // Determine new state for session
        let isHidden = sidebarToggler.classList.contains("d-none") ? "true" : "false";

        // Send AJAX request to update PHP session
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "toggle_toggler@1.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("sidebarTogglerHidden=" + isHidden);
    });
});
</script>

</body>
</html>
