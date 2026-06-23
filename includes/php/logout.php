<?php
// includes/php/logout.php
session_start();

// Remove all session variables
session_unset(); 

// Destroy the session completely
session_destroy(); 

// Send the user back to the login page
header("Location: ../../login.html"); 
exit();
?>