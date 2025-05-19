<?php
session_start();
define('TITLE', 'Shopping Cart');
define('PAGE', 'Cart');
include('includes/header.php');
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get user's login ID from session
$user_id = $_SESSION['r_login_id'];

// Handle remove item
if(isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = filter_input(INPUT_GET, 'remove', FILTER_SANITIZE_NUMBER_INT);
    $sql = "DELETE FROM shopping_cart_tb WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    $success_msg = '<div class="alert alert-success">Item removed from cart successfully!</div>';
}

// Handle update quantity
if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $cart_id => $quantity) {
        $cart_id = filter_var($cart_id, FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
        
        if($quantity > 0) {
            $sql = "UPDATE shopping_cart_tb SET quantity = ? WHERE cart_id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $success_msg = '<div class="alert alert-success">Cart updated successfully!</div>';
}

// Get cart items
$sql = "SELECT c.*, a.pname, a.psellingcost, a.image_url, a.pava as available_quantity 
        FROM shopping_cart_tb c 
        JOIN assets_tb a ON c.product_id = a.pid 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['psellingcost'] * $row['quantity'];
    $total += $row['subtotal'];
    $cart_items[] = $row;
}
$stmt->close();

// Debug information
error_log("User ID: " . $user_id);
error_log("Cart Items Count: " . count($cart_items));
?>

<div class="col-sm-9 col-md-10">
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Shopping Cart</h2>
                <p class="text-muted">Review and manage your cart items</p>
            </div>
        </div>

        <?php if(isset($success_msg)) echo $success_msg; ?>

        <?php if(empty($cart_items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="cart-card p-4 mb-4">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/100'); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['pname']); ?>" 
                                                         class="product-image me-3">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['pname']); ?></h6>
                                                        <small class="text-muted">Available: <?php echo $item['available_quantity']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>₹<?php echo number_format($item['psellingcost'], 2); ?></td>
                                            <td>
                                                <input type="number" name="quantity[<?php echo $item['cart_id']; ?>]" 
                                                       class="form-control quantity-input" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['available_quantity']; ?>">
                                            </td>
                                            <td>₹<?php echo number_format($item['subtotal'], 2); ?></td>
                                            <td>
                                                <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="products.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                </a>
                                <button type="submit" name="update_cart" class="btn btn-primary">
                                    <i class="fas fa-sync me-2"></i>Update Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="cart-card p-4">
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
                            <a href="checkout.php" class="btn btn-primary w-100">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
    :root {
        --primary-color: #f3961c;
        --secondary-color: #333;
        --accent-color: #f3961c;
        --text-color: #333;
        --light-bg: #f8f9fa;
        --dark-bg: #333;
    }
    
    .cart-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        background: white;
    }
    
    .product-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .quantity-input {
        width: 60px;
        text-align: center;
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
    
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 