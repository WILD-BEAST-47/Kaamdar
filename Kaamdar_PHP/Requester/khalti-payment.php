<?php
session_start();
include('../dbConnection.php');

// Debug information
error_log("Khalti Payment - Session: " . print_r($_SESSION, true));

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

try {
    // Get cart items and calculate total
    $user_id = $_SESSION['r_login_id'];
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
    $product_names = [];
    
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
        $total_amount += ($row['psellingcost'] * $row['quantity']);
        $product_names[] = $row['pname'] . ' (x' . $row['quantity'] . ')';
    }
    
    if(empty($items)) {
        throw new Exception('Cart is empty');
    }
    
    // Khalti API Configuration
    $khalti_secret_key = '7b9bb2d3a2aa4de08af26c3653733895'; // Test Secret Key
    $khalti_public_key = '903f72a413ff418684fd4f9be1e77cd0'; // Test Public Key
    
    // Generate a unique purchase order ID
    $purchase_order_id = 'ORDER_' . time() . '_' . $user_id;
    
    // Get the current domain
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'];
    
    // Prepare the payload for Khalti
    $payload = [
        'return_url' => $protocol . $domain . '/Kaamdar_PHP/Requester/payment-success.php',
        'website_url' => $protocol . $domain . '/Kaamdar_PHP/Requester/RequesterDashboard.php',
        'amount' => $total_amount * 100, // Convert to paisa
        'purchase_order_id' => $purchase_order_id,
        'purchase_order_name' => 'Order #' . $purchase_order_id,
        'customer_info' => [
            'name' => $_SESSION['r_name'] ?? 'Customer',
            'email' => $_SESSION['r_email'] ?? '',
            'phone' => $_SESSION['r_mobile'] ?? ''
        ],
        'amount_breakdown' => [
            [
                'label' => 'Total Amount',
                'amount' => $total_amount * 100
            ]
        ],
        'product_details' => [
            [
                'identity' => $purchase_order_id,
                'name' => implode(', ', $product_names),
                'total_price' => $total_amount * 100,
                'quantity' => 1,
                'unit_price' => $total_amount * 100
            ]
        ]
    ];
    
    // Initialize cURL
    $ch = curl_init('https://a.khalti.com/api/v2/epayment/initiate/');
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $khalti_secret_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // Disable SSL verification for testing
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL
    curl_close($ch);
    
    // Decode response
    $result = json_decode($response, true);
    
    // Log the response for debugging
    error_log('Khalti Payment Response: ' . print_r($result, true));
    error_log('HTTP Code: ' . $http_code);
    error_log('Request Payload: ' . json_encode($payload));
    
    if ($http_code === 200 && isset($result['payment_url'])) {
        // Store payment details in session
        $_SESSION['payment_details'] = [
            'pidx' => $result['pidx'],
            'purchase_order_id' => $purchase_order_id,
            'amount' => $total_amount,
            'items' => $items
        ];
        
        echo json_encode([
            'success' => true,
            'payment_url' => $result['payment_url']
        ]);
    } else {
        $error_message = isset($result['detail']) ? $result['detail'] : 'Payment initiation failed';
        error_log('Khalti Payment Error: ' . $error_message);
        error_log('Full Response: ' . print_r($result, true));
        throw new Exception($error_message);
    }
    
} catch (Exception $e) {
    error_log('Khalti Payment Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 