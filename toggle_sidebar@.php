<?php
session_start();
if (isset($_POST['sidebarHidden'])) {
    $_SESSION['sidebarHidden'] = $_POST['sidebarHidden'];
}
?>
