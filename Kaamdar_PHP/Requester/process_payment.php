<?php
session_start();
include('../dbConnection.php');

// Debug information
error_log("Process Payment - GET Parameters: " . print_r($_GET, true));
error_log("Process Payment - Session: " . print_r($_SESSION, true));

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get payment details from callback
$pidx = $_GET['pidx'] ?? null;
$status = $_GET['status'] ?? null;
$amount = $_GET['amount'] ?? null;
$purchase_order_id = $_GET['purchase_order_id'] ?? null;

if(!$pidx || !$status) {
    $_SESSION['payment_error'] = 'Invalid payment callback';
    header('Location: cart.php');
    exit;
}

try {
    // Verify payment with Khalti
    $khalti_secret_key = '7b9bb2d3a2aa4de08af26c3653733895';
    
    // Khalti verification endpoint
    $url = "https://khalti.com/api/v2/epayment/lookup/";
    
    // Prepare verification data
    $verification_data = [
        'pidx' => $pidx
    ];
    
    // Initialize cURL
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verification_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $khalti_secret_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    // Close cURL
    curl_close($ch);
    
    // Decode response
    $result = json_decode($response, true);
    
    // Log the response for debugging
    error_log('Khalti Verification Response: ' . print_r($result, true));
    
    // Check payment status
    if (isset($result['status']) && $result['status'] === 'Completed') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            $user_id = $_SESSION['r_login_id'];
            
            // Get cart items
            $sql = "SELECT c.*, a.pname, a.psellingcost 
                    FROM shopping_cart_tb c 
                    JOIN assets_tb a ON c.product_id = a.pid 
                    WHERE c.user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $items = [];
            $total_amount = 0;
            while($row = $result->fetch_assoc()) {
                $items[] = $row;
                $total_amount += ($row['psellingcost'] * $row['quantity']);
            }
            $stmt->close();
            
            // Insert into orders table
            $order_sql = "INSERT INTO orders_tb (user_id, total_amount, payment_method, payment_status, order_status, created_at) 
                         VALUES (?, ?, 'Khalti', 'Paid', 'Paid', NOW())";
            $stmt = $conn->prepare($order_sql);
            $stmt->bind_param("id", $user_id, $total_amount);
            $stmt->execute();
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Insert order items
            $item_sql = "INSERT INTO order_items_tb (order_id, product_id, quantity, price) 
                         VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($item_sql);
            
            foreach ($items as $item) {
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['psellingcost']);
                $stmt->execute();
                
                // Update product availability
                $update_sql = "UPDATE assets_tb SET pava = pava - ? WHERE pid = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            $stmt->close();
            
            // Clear cart
            $clear_cart_sql = "DELETE FROM shopping_cart_tb WHERE user_id = ?";
            $stmt = $conn->prepare($clear_cart_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            $_SESSION['payment_success'] = true;
            $_SESSION['order_id'] = $order_id;
            
            // Clear payment details from session
            unset($_SESSION['payment_details']);
            
            // Redirect to my orders page
            header("Location: my-orders.php");
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['payment_error'] = "Error processing order: " . $e->getMessage();
            header("Location: cart.php");
            exit;
        }
    } else {
        $_SESSION['payment_error'] = "Payment verification failed. Status: " . ($result['status'] ?? 'Unknown');
        header("Location: cart.php");
        exit;
    }
    
} catch (Exception $e) {
    $_SESSION['payment_error'] = "Error verifying payment: " . $e->getMessage();
    header("Location: cart.php");
    exit;
}
?> 