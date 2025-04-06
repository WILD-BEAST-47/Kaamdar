<?php
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "kaamdar_db"; 
$db_port = 3306;  

// Create Connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

// Check Connection
if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>
