<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$user_id = $_SESSION['r_login_id'];

// Get user details
$user_sql = "SELECT * FROM requesterlogin_tb WHERE r_login_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get cart items
$cart_sql = "SELECT c.*, a.pname, a.psellingcost, a.quantity as available_quantity 
             FROM shopping_cart_tb c 
             JOIN assets_tb a ON c.product_id = a.pid 
             WHERE c.user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while($row = $cart_result->fetch_assoc()) {
    $row['subtotal'] = $row['psellingcost'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}
$cart_stmt->close();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $shipping_address = filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING);
    $shipping_city = filter_input(INPUT_POST, 'shipping_city', FILTER_SANITIZE_STRING);
    $shipping_state = filter_input(INPUT_POST, 'shipping_state', FILTER_SANITIZE_STRING);
    $shipping_zip = filter_input(INPUT_POST, 'shipping_zip', FILTER_SANITIZE_STRING);
    $shipping_phone = filter_input(INPUT_POST, 'shipping_phone', FILTER_SANITIZE_STRING);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $order_sql = "INSERT INTO orders_tb (user_id, total_amount, shipping_address, shipping_city, 
                      shipping_state, shipping_zip, shipping_phone, payment_method) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("idssssss", $user_id, $total, $shipping_address, $shipping_city, 
                               $shipping_state, $shipping_zip, $shipping_phone, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        $order_stmt->close();
        
        // Add order items
        $item_sql = "INSERT INTO order_items_tb (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        foreach($cart_items as $item) {
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['psellingcost']);
            $item_stmt->execute();
            
            // Update product quantity
            $update_sql = "UPDATE assets_tb SET quantity = quantity - ? WHERE pid = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
        $item_stmt->close();
        
        // Clear cart
        $clear_cart_sql = "DELETE FROM shopping_cart_tb WHERE user_id = ?";
        $clear_cart_stmt = $conn->prepare($clear_cart_sql);
        $clear_cart_stmt->bind_param("i", $user_id);
        $clear_cart_stmt->execute();
        $clear_cart_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to success page
        header("Location: order-success.php?id=" . $order_id);
        exit;
        
    } catch(Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_msg = '<div class="alert alert-danger">An error occurred while processing your order. Please try again.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KaamDar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f3961c;
            --secondary-color: #333;
            --accent-color: #f3961c;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --dark-bg: #333;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        .checkout-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: white;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: rgba(243, 150, 28, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(243, 150, 28, 0.2);
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Checkout</h2>
                <p class="text-muted">Complete your order details</p>
            </div>
        </div>

        <?php if(isset($error_msg)) echo $error_msg; ?>

        <?php if(empty($cart_items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" class="row">
                <div class="col-md-8">
                    <div class="checkout-card p-4 mb-4">
                        <h5 class="mb-4">Shipping Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="shipping_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="shipping_address" name="shipping_address" 
                                       value="<?php echo htmlspecialchars($user['r_add1'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                       value="<?php echo htmlspecialchars($user['r_city'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" 
                                       value="<?php echo htmlspecialchars($user['r_state'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_zip" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" 
                                       value="<?php echo htmlspecialchars($user['r_zip'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" 
                                       value="<?php echo htmlspecialchars($user['r_mobile'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card p-4">
                        <h5 class="mb-4">Payment Method</h5>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                            <label class="form-check-label" for="cod">
                                Cash on Delivery
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online">
                            <label class="form-check-label" for="online">
                                Online Payment
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="checkout-card p-4">
                        <h5 class="mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">₹<?php echo number_format($total, 2); ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Place Order
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 