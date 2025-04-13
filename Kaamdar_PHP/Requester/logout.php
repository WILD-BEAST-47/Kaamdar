<?php
/**
 * Requester Logout Handler
 * 
 * This file handles the logout functionality for requester users.
 * It clears the session and redirects to the login page.
 */

// Start session if not already started
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect to the requester login page
header("Location: ../RequesterLogin.php");
exit;
?> 