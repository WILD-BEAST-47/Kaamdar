<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug information
error_log("Payment Success - GET Parameters: " . print_r($_GET, true));
error_log("Payment Success - Session: " . print_r($_SESSION, true));

// Include database connection
include('../dbConnection.php');

// Initialize variables
$success_msg = '';
$error_msg = '';

// Check if we have payment parameters
if (isset($_GET['pidx'])) {
    $pidx = $_GET['pidx'];
    $status = $_GET['status'] ?? '';

    // If user canceled
    if ($status === 'User canceled') {
        header("Location: products.php?error=" . urlencode("Payment was canceled"));
        exit;
    }

    // Check if user is logged in
    if(!isset($_SESSION['is_login'])) {
        header("Location: login.php");
        exit;
    }

    // Get user's login ID from session
    $user_id = $_SESSION['r_login_id'];

    // Khalti API configuration
    $khalti_secret_key = "7b9bb2d3a2aa4de08af26c3653733895"; // Test Secret Key

    // Debug information
    error_log("Payment Verification - Pidx: " . $pidx);
    error_log("Payment Verification - User ID: " . $user_id);
    error_log("Payment Verification - Session Data: " . print_r($_SESSION, true));

    // Initialize cURL for payment verification
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Key ' . $khalti_secret_key,
            'Content-Type: application/json'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ));

    // Execute cURL request
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);

    // Log the complete request details for debugging
    error_log("Khalti API Request Details:");
    error_log("URL: https://a.khalti.com/api/v2/epayment/lookup/");
    error_log("Headers: " . print_r(curl_getinfo($curl, CURLINFO_HEADER_OUT), true));
    error_log("Request Body: " . json_encode(['pidx' => $pidx]));
    error_log("Response Code: " . $http_code);
    error_log("Response Body: " . $response);
    error_log("Error: " . $err);

    // Close cURL
    curl_close($curl);

    if ($err) {
        error_log("Khalti Payment Verification Error: " . $err);
        header("Location: cart.php?error=" . urlencode("Payment verification failed: " . $err));
        exit;
    }

    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        header("Location: cart.php?error=" . urlencode("Invalid payment response format"));
        exit;
    }

    error_log("Decoded Payment Response: " . print_r($result, true));

    // Check if payment is successful
    if (isset($result['status'])) {
        error_log("Payment Status: " . $result['status']);
        
        if ($result['status'] === 'Completed') {
            if (isset($_SESSION['payment_details'])) {
                $total_amount = $_SESSION['payment_details']['amount'];
                $items = $_SESSION['payment_details']['items'];

                // Start transaction
                $conn->begin_transaction();

                try {
                    // Insert order
                    $order_sql = "INSERT INTO orders_tb (user_id, total_amount, payment_status, payment_method, order_status, created_at) 
                                VALUES (?, ?, 'Paid', 'Khalti', 'Processing', NOW())";
                    $stmt = $conn->prepare($order_sql);
                    if (!$stmt) {
                        throw new Exception("Failed to prepare order statement: " . $conn->error);
                    }
                    
                    $stmt->bind_param("id", $user_id, $total_amount);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create order: " . $stmt->error);
                    }
                    
                    $order_id = $conn->insert_id;
                    error_log("Order created successfully with ID: " . $order_id);

                    // Insert order items
                    $item_sql = "INSERT INTO order_items_tb (order_id, product_id, quantity, price) 
                               VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($item_sql);
                    if (!$stmt) {
                        throw new Exception("Failed to prepare order items statement: " . $conn->error);
                    }

                    foreach ($items as $item) {
                        $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['psellingcost']);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to insert order item: " . $stmt->error);
                        }

                        // Update product stock
                        $update_stock = "UPDATE assets_tb SET pava = pava - ? WHERE pid = ?";
                        $stmt_stock = $conn->prepare($update_stock);
                        if (!$stmt_stock) {
                            throw new Exception("Failed to prepare stock update statement: " . $conn->error);
                        }
                        
                        $stmt_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                        if (!$stmt_stock->execute()) {
                            throw new Exception("Failed to update product stock: " . $stmt_stock->error);
                        }
                    }

                    // Clear cart
                    $clear_cart_sql = "DELETE FROM shopping_cart_tb WHERE user_id = ?";
                    $stmt = $conn->prepare($clear_cart_sql);
                    if (!$stmt) {
                        throw new Exception("Failed to prepare cart clear statement: " . $conn->error);
                    }
                    
                    $stmt->bind_param("i", $user_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to clear cart: " . $stmt->error);
                    }
                    error_log("Cart cleared successfully for user: " . $user_id);

                    // Get user details for email
                    $user_sql = "SELECT r_name, r_email FROM requesterlogin_tb WHERE r_login_id = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    if (!$user_stmt) {
                        throw new Exception("Failed to prepare user details statement: " . $conn->error);
                    }
                    
                    $user_stmt->bind_param("i", $user_id);
                    if (!$user_stmt->execute()) {
                        throw new Exception("Failed to get user details: " . $user_stmt->error);
                    }
                    
                    $user_result = $user_stmt->get_result();
                    $user_data = $user_result->fetch_assoc();
                    
                    if (!$user_data) {
                        throw new Exception("User data not found for ID: " . $user_id);
                    }

                    // Prepare order details for email
                    $orderDetails = [
                        'order_id' => $order_id,
                        'total_amount' => $total_amount,
                        'payment_method' => 'Khalti',
                        'order_status' => 'Processing',
                        'items' => $items
                    ];

                    // Send order confirmation email
                    require_once('mail_config.php');
                    if (function_exists('sendOrderConfirmation')) {
                        if (sendOrderConfirmation($user_data['r_email'], $order_id, $orderDetails)) {
                            error_log("Order confirmation email sent successfully to: " . $user_data['r_email']);
                        } else {
                            error_log("Failed to send order confirmation email to: " . $user_data['r_email']);
                        }
                    } else {
                        error_log("sendOrderConfirmation function not found in mail_config.php");
                    }

                    // Commit transaction
                    if (!$conn->commit()) {
                        throw new Exception("Failed to commit transaction: " . $conn->error);
                    }
                    error_log("Transaction committed successfully");

                    // Clear payment details from session
                    unset($_SESSION['payment_details']);

                    // Set success message in session
                    $_SESSION['payment_success'] = true;
                    $_SESSION['order_id'] = $order_id;

                    // Redirect to my orders page
                    header("Location: my-orders.php");
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    error_log("Order Processing Error: " . $e->getMessage());
                    header("Location: cart.php?error=" . urlencode("Order processing failed: " . $e->getMessage()));
                    exit;
                }
            } else {
                error_log("Payment details not found in session for user: " . $user_id);
                header("Location: cart.php?error=" . urlencode("Payment details not found. Please contact support."));
                exit;
            }
        } else {
            error_log("Payment not completed. Status: " . $result['status']);
            error_log("Full payment response: " . print_r($result, true));
            header("Location: cart.php?error=" . urlencode("Payment not completed. Status: " . $result['status']));
            exit;
        }
    } else {
        error_log("Payment status not found in response");
        error_log("Full payment response: " . print_r($result, true));
        header("Location: cart.php?error=" . urlencode("Invalid payment response. Please try again."));
        exit;
    }
} else {
    header("Location: cart.php?error=" . urlencode("Invalid payment response"));
    exit;
}
?> 