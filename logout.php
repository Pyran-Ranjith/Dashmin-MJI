<?php 
ob_start();
// session_destroy() destroys the session. Then the header() function redirects to the home page. 
session_unset();
session_destroy();
header ("Location: ./index.php");
