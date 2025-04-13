<?php
session_start();
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get product ID from URL
if(isset($_GET['id'])) {
    $pid = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // First, get the image URL to delete the image file
    $sql = "SELECT image_url FROM assets_tb WHERE pid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Delete the image file if it exists
    if($row['image_url'] && file_exists('../' . $row['image_url'])) {
        unlink('../' . $row['image_url']);
    }
    
    // Delete from shopping cart first (to maintain referential integrity)
    $cart_sql = "DELETE FROM shopping_cart_tb WHERE product_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $pid);
    $cart_stmt->execute();
    $cart_stmt->close();
    
    // Then delete the product
    $sql = "DELETE FROM assets_tb WHERE pid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pid);
    
    if($stmt->execute()) {
        $_SESSION['success_msg'] = '<div class="alert alert-success">Product deleted successfully!</div>';
    } else {
        $_SESSION['error_msg'] = '<div class="alert alert-danger">Unable to delete product.</div>';
    }
    $stmt->close();
}

// Redirect back to assets page
header("Location: assets.php");
exit;
?> 