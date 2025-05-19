<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if order ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$order_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$user_id = $_SESSION['r_login_id'];

try {
    // Get order details
    $order_sql = "SELECT * FROM orders_tb WHERE order_id = ? AND user_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("ii", $order_id, $user_id);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    
    if($order_result->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    $order = $order_result->fetch_assoc();
    
    // Get order items
    $items_sql = "SELECT oi.*, a.pname 
                 FROM order_items_tb oi 
                 JOIN assets_tb a ON oi.product_id = a.pid 
                 WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $items = $items_result->fetch_all(MYSQLI_ASSOC);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 