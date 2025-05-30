<?php
session_start();
if (isset($_POST['sidebarTogglerHidden'])) {
    $_SESSION['sidebarTogglerHidden'] = $_POST['sidebarTogglerHidden'];
}
?>
