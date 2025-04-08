<?php
session_start();
include('../dbConnection.php');

// Debug information
error_log("Khalti Payment - Session: " . print_r($_SESSION, true));

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get user's login ID from session
$user_id = $_SESSION['r_login_id'];

// Get cart items and calculate total
$sql = "SELECT c.*, a.pname, a.psellingcost 
        FROM shopping_cart_tb c 
        JOIN assets_tb a ON c.product_id = a.pid 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$items = [];
$product_details = [];

while($row = $result->fetch_assoc()) {
    $total += $row['psellingcost'] * $row['quantity'];
    $items[] = $row;
    
    // Prepare product details for Khalti
    $product_details[] = [
        'identity' => (string)$row['product_id'],
        'name' => $row['pname'],
        'total_price' => (int)($row['psellingcost'] * $row['quantity'] * 100), // Convert to paisa
        'quantity' => (int)$row['quantity'],
        'unit_price' => (int)($row['psellingcost'] * 100) // Convert to paisa
    ];
}
$stmt->close();

// Validate minimum amount (10 rupees = 1000 paisa)
if ($total < 10) {
    echo json_encode(['error' => 'Minimum payment amount is Rs. 10']);
    exit;
}

// Store payment details in session
$_SESSION['payment_details'] = [
    'total_amount' => $total,
    'items' => $items
];

// Khalti API configuration
$khalti_secret_key = "94749dd8be814afa8274c7dea9424af4"; // Live secret key

// Initialize cURL
$curl = curl_init();

// Generate unique purchase order ID
$purchase_order_id = 'ORDER_' . time() . '_' . $user_id;

// Get the current URL's directory
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . dirname($_SERVER['PHP_SELF']);

// Prepare the payload exactly as per documentation
$payload = [
    'return_url' => $base_url . '/payment-success.php',
    'website_url' => $base_url . '/',
    'amount' => (int)($total * 100), // Convert to paisa
    'purchase_order_id' => $purchase_order_id,
    'purchase_order_name' => "Order from KaamDar",
    'customer_info' => [
        'name' => $_SESSION['r_name'] ?? 'Customer',
        'email' => $_SESSION['r_email'] ?? '',
        'phone' => $_SESSION['r_mobile'] ?? ''
    ],
    'product_details' => $product_details
];

// Set cURL options
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Key ' . $khalti_secret_key,
        'Content-Type: application/json',
    ),
));

// Execute cURL request
$response = curl_exec($curl);
$err = curl_error($curl);

// Get HTTP status code
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Close cURL
curl_close($curl);

error_log("Khalti API Request Payload: " . json_encode($payload));
error_log("Khalti API Response: " . $response);
error_log("HTTP Status Code: " . $httpCode);

if ($err) {
    echo json_encode(['error' => 'cURL Error: ' . $err]);
} else {
    $result = json_decode($response, true);
    
    if(isset($result['payment_url'])) {
        // Store payment details in session
        $_SESSION['payment_details']['pidx'] = $result['pidx'];
        $_SESSION['payment_details']['purchase_order_id'] = $purchase_order_id;
        
        // Return payment URL for redirection
        echo json_encode([
            'success' => true,
            'payment_url' => $result['payment_url']
        ]);
    } else {
        // Enhanced error message
        $error_message = 'Payment initiation failed: ';
        if (isset($result['detail'])) {
            $error_message .= $result['detail'];
        } elseif (isset($result['error_key'])) {
            $error_message .= $result['error_key'];
        } else {
            $error_message .= 'Unknown error';
        }
        $error_message .= ' (HTTP Code: ' . $httpCode . ')';
        
        echo json_encode(['error' => $error_message]);
    }
}
?> 