<?php
session_start();
include('../dbConnection.php');

// Set headers for JSON response
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['amount']) || !isset($data['purchase_order_id']) || !isset($data['return_url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    // Khalti API endpoint
    $url = "https://a.khalti.com/api/v2/epayment/initiate/";
    
    // Khalti API credentials
    $public_key = "9ab466ca8db74aefb5f670a73c2d89f1";
    $secret_key = "57034cd32760445d81f87c5ac493c0d8";
    
    // Prepare payment data
    $payment_data = [
        'return_url' => $data['return_url'],
        'website_url' => $data['website_url'],
        'amount' => $data['amount'],
        'purchase_order_id' => $data['purchase_order_id'],
        'purchase_order_name' => $data['purchase_order_name'],
        'product_details' => $data['product_details']
    ];

    // Initialize cURL
    $ch = curl_init($url);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Key ' . $secret_key,
        'Content-Type: application/json'
    ]);

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
    
    // Check if payment URL is present in response
    if (isset($result['payment_url'])) {
        // Store payment details in session for verification
        $_SESSION['payment_details'] = [
            'purchase_order_id' => $data['purchase_order_id'],
            'amount' => $data['amount'],
            'status' => 'Pending'
        ];
        
        // Return success response
        echo json_encode([
            'success' => true,
            'payment_url' => $result['payment_url']
        ]);
    } else {
        throw new Exception('Payment URL not found in response');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Payment initiation failed',
        'detail' => $e->getMessage()
    ]);
}
?> 