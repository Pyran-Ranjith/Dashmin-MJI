<?php
// Simulate a list of menu items for testing
$menu_items = [
    ['menu_name' => 'Dashboard', 'menu_link' => 'dashboard.php'],
    ['menu_name' => 'Manage Users', 'menu_link' => 'manage_users.php'],
    ['menu_name' => 'Manage Inventory', 'menu_link' => 'manage_inventory.php'],
    ['menu_name' => 'Maintain Equipment', 'menu_link' => 'maintain_equipment.php'],
    ['menu_name' => 'Maintain Vehicles', 'menu_link' => 'maintain_vehicles.php'],
    ['menu_name' => 'Reports', 'menu_link' => 'reports.php']
];

// Include the header file to test it
include '0104_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Header</title>
    <!-- Bootstrap CSS for proper styling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<!-- Simulate a Bootstrap Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="#">My App</a>
        
        <!-- Menu Items from header.php -->
        <?php include 'header.php'; ?>
    </div>
</nav>

<!-- Bootstrap JavaScript for Dropdown to Work -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

<!-- Bootstrap CSS (for styling) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap JS (for dropdown functionality) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
