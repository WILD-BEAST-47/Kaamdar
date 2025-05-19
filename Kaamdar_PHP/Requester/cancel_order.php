<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$order_id = $data['order_id'] ?? null;

if(!$order_id || !is_numeric($order_id)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$user_id = $_SESSION['r_login_id'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if order exists and belongs to user
    $check_sql = "SELECT * FROM orders_tb WHERE order_id = ? AND user_id = ? AND order_status != 'Cancelled'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows === 0) {
        throw new Exception('Order not found or already cancelled');
    }
    
    // Get order items to restore product quantities
    $items_sql = "SELECT product_id, quantity FROM order_items_tb WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    $items = $items_result->fetch_all(MYSQLI_ASSOC);
    
    // Restore product quantities
    foreach($items as $item) {
        $update_sql = "UPDATE assets_tb SET pava = pava + ? WHERE pid = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stmt->execute();
    }
    
    // Update order status
    $update_sql = "UPDATE orders_tb SET order_status = 'Cancelled' WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 