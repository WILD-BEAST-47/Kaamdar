<?php
/**
 * Global Logout Handler
 * 
 * This file handles the logout functionality for both admin and requester users.
 * It determines the user type and redirects to the appropriate login page.
 */

// Start session if not already started
session_start();

// Store the user type before clearing session
$user_type = isset($_SESSION['is_adminlogin']) ? 'admin' : 'requester';

// Clear all session variables
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect based on user type
if ($user_type === 'admin') {
    header("Location: Admin/login.php");
} else {
    header("Location: RequesterLogin.php");
}
exit;
?> 