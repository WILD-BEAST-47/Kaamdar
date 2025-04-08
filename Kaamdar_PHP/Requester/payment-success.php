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
    $khalti_secret_key = "94749dd8be814afa8274c7dea9424af4";

    // Initialize cURL for payment verification
    $curl = curl_init();

    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Key ' . $khalti_secret_key,
            'Content-Type: application/json',
        ),
    ));

    // Execute cURL request
    $response = curl_exec($curl);
    $err = curl_error($curl);

    // Close cURL
    curl_close($curl);

    if ($err) {
        error_log("Khalti Payment Verification Error: " . $err);
        header("Location: products.php?error=" . urlencode("Payment verification failed"));
        exit;
    }

    $result = json_decode($response, true);
    error_log("Khalti Payment Verification Response: " . print_r($result, true));

    if (isset($result['status']) && $result['status'] === 'Completed') {
        if (isset($_SESSION['payment_details'])) {
            $total_amount = $_SESSION['payment_details']['total_amount'];
            $items = $_SESSION['payment_details']['items'];

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert order
                $order_sql = "INSERT INTO orders_tb (user_id, total_amount, payment_status, payment_method, created_at) 
                            VALUES (?, ?, 'Paid', 'Khalti', NOW())";
                $stmt = $conn->prepare($order_sql);
                $stmt->bind_param("id", $user_id, $total_amount);
                $stmt->execute();
                $order_id = $conn->insert_id;

                // Insert order items
                $item_sql = "INSERT INTO order_items_tb (order_id, product_id, quantity, price) 
                           VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($item_sql);

                foreach ($items as $item) {
                    $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['psellingcost']);
                    $stmt->execute();

                    // Update product stock
                    $update_stock = "UPDATE assets_tb SET pava = pava - ? WHERE pid = ?";
                    $stmt_stock = $conn->prepare($update_stock);
                    $stmt_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                    $stmt_stock->execute();
                }

                // Clear cart
                $clear_cart_sql = "DELETE FROM shopping_cart_tb WHERE user_id = ?";
                $stmt = $conn->prepare($clear_cart_sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();

                // Commit transaction
                $conn->commit();

                // Clear payment details from session
                unset($_SESSION['payment_details']);

                // Set success message in session
                $_SESSION['payment_success'] = true;
                $_SESSION['order_id'] = $order_id;

                // Redirect to my orders page to show the order
                header("Location: my-orders.php");
                exit;
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                error_log("Order Processing Error: " . $e->getMessage());
                header("Location: products.php?error=" . urlencode("Order processing failed. Please contact support."));
                exit;
            }
        } else {
            error_log("Payment details not found in session");
            header("Location: products.php?error=" . urlencode("Payment details not found. Please contact support."));
            exit;
        }
    } elseif (isset($result['status']) && $result['status'] === 'Pending') {
        header("Location: products.php?warning=" . urlencode("Payment is pending. Please wait for confirmation."));
        exit;
    } else {
        error_log("Payment Verification Failed: " . json_encode($result));
        header("Location: products.php?error=" . urlencode("Payment verification failed"));
        exit;
    }
} else {
    header("Location: products.php?error=" . urlencode("Invalid payment response"));
    exit;
}
?> 