<?php
session_start();
include('../dbConnection.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug logging function
function debug_log($message) {
    error_log("[Cart Debug] " . $message);
}

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    header("Location: ../RequesterLogin.php");
    exit;
}

// Get user's login ID from session
$user_id = $_SESSION['r_login_id'];

// Debug POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST data received: " . print_r($_POST, true));
}

// Handle remove item from cart
if(isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = filter_input(INPUT_GET, 'remove', FILTER_SANITIZE_NUMBER_INT);
    $sql = "DELETE FROM shopping_cart_tb WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: cart.php");
    exit;
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
    header("Location: cart.php");
    exit;
}

// Handle cash payment
if(isset($_POST['cash_payment'])) {
    debug_log("Processing cash payment for user: " . $user_id);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        debug_log("Transaction started");
        
        // Get cart items for order creation
        $cart_sql = "SELECT c.*, a.pname, a.psellingcost 
                    FROM shopping_cart_tb c 
                    JOIN assets_tb a ON c.product_id = a.pid 
                    WHERE c.user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);
        
        debug_log("Cart items found: " . count($cart_items));
        
        if (empty($cart_items)) {
            throw new Exception("Your cart is empty");
        }
        
        // Calculate total amount
        $total_amount = array_sum(array_map(function($item) {
            return $item['quantity'] * $item['psellingcost'];
        }, $cart_items));
        
        debug_log("Total amount calculated: " . $total_amount);
        
        // Create order
        $order_sql = "INSERT INTO orders_tb (user_id, total_amount, payment_method, payment_status, order_status) 
                     VALUES (?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $payment_method = 'Cash';
        $payment_status = 'Pending';
        $order_status = 'Pending';
        
        $order_stmt->bind_param("idsss", $user_id, $total_amount, $payment_method, $payment_status, $order_status);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        debug_log("Order created with ID: " . $order_id);
        
        // Create order items
        $item_sql = "INSERT INTO order_items_tb (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        foreach($cart_items as $item) {
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['psellingcost']);
            $item_stmt->execute();
            
            // Update product availability
            $update_sql = "UPDATE assets_tb SET pava = pava - ? WHERE pid = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $update_stmt->execute();
            
            debug_log("Order item created for product: " . $item['product_id']);
        }
        
        // Clear cart
        $clear_sql = "DELETE FROM shopping_cart_tb WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_sql);
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        
        debug_log("Cart cleared for user: " . $user_id);
        
        // Get user email
        $user_sql = "SELECT r_email FROM requesterlogin_tb WHERE r_login_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        
        // Prepare order details for email
        $orderDetails = [
            'total_amount' => $total_amount,
            'payment_method' => $payment_method,
            'order_status' => $order_status,
            'items' => $cart_items
        ];
        
        // Send order confirmation email
        require_once('mail_config.php');
        if (sendOrderConfirmation($user_data['r_email'], $order_id, $orderDetails)) {
            debug_log("Order confirmation email sent successfully");
        } else {
            debug_log("Failed to send order confirmation email");
        }
        
        // Set success message
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        
        // Commit transaction
        $conn->commit();
        debug_log("Transaction committed successfully");
        
        // Redirect to orders page
        header("Location: my-orders.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        debug_log("Error occurred: " . $e->getMessage());
        $_SESSION['payment_error'] = "Error processing order: " . $e->getMessage();
        header("Location: cart.php");
        exit;
    }
}

// Get cart items with product details
$sql = "SELECT c.*, a.pname, a.psellingcost, a.pava, a.image_url 
        FROM shopping_cart_tb c 
        JOIN assets_tb a ON c.product_id = a.pid 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total
$total = 0;
foreach($cart_items as &$item) {
    $item['subtotal'] = $item['quantity'] * $item['psellingcost'];
    $item['available_quantity'] = $item['pava'];
    $total += $item['subtotal'];
}

// Now include the header and start HTML output
define('TITLE', 'Shopping Cart');
define('PAGE', 'Cart');
include('includes/header.php');
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <?php
        if (isset($_SESSION['order_success']) && $_SESSION['order_success']) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>
                            <strong>Success!</strong> Your order has been placed successfully.
                            <br>
                            <small>Order ID: ' . $_SESSION['order_id'] . '</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['order_success']);
            unset($_SESSION['order_id']);
        }

        if (isset($_SESSION['payment_error'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ' . $_SESSION['payment_error'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['payment_error']);
        }
        ?>

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Shopping Cart</h2>
                        <p class="text-muted">Review and manage your cart items</p>
                    </div>
                    <a href="RequesterDashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>

        <?php if(empty($cart_items)): ?>
            <div class="alert alert-info">
                Your cart is empty. <a href="products.php" class="alert-link">Continue shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" action="cart.php" id="cartForm">
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
                                                    <?php 
                                                    $image_path = isset($item['image_url']) && $item['image_url'] ? '../' . $item['image_url'] : 'assets/images/default-product.png';
                                                    if (!file_exists($image_path)) {
                                                        $image_path = 'assets/images/default-product.png';
                                                    }
                                                    ?>
                                                    <img src="<?php echo $image_path; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['pname']); ?>" 
                                                         class="product-image me-3" style="max-height: 100px; max-width: 100px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['pname']); ?></h6>
                                                        <small class="text-muted">Available: <?php echo $item['available_quantity']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>NPR <?php echo number_format($item['psellingcost'], 2); ?></td>
                                            <td>
                                                <div class="input-group">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">-</button>
                                                    <input type="number" class="form-control text-center" name="quantity[<?php echo $item['cart_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['available_quantity']; ?>">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">+</button>
                                                </div>
                                            </td>
                                            <td>NPR <?php echo number_format($item['subtotal'], 2); ?></td>
                                            <td>
                                                <a href="?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this item?')">
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
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>NPR. <?php echo number_format($total, 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Total:</span>
                                    <span class="fw-bold">NPR. <?php echo number_format($total, 2); ?></span>
                                </div>
                                
                                <div class="payment-options mb-3">
                                    <h6 class="mb-2">Payment Method:</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="khalti" value="khalti" checked>
                                        <label class="form-check-label" for="khalti">
                                            <i class="fas fa-credit-card me-2"></i>Khalti Payment
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash">
                                        <label class="form-check-label" for="cash">
                                            <i class="fas fa-money-bill-wave me-2"></i>Cash on Site
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-primary" id="proceedToPayment">
                                        <i class="fas fa-lock me-2"></i>Proceed to Payment
                                    </button>
                                    <button type="submit" name="cash_payment" value="1" class="btn btn-success d-none" id="confirmCashPayment">
                                        <i class="fas fa-check me-2"></i>Confirm Order
                                    </button>
                                </div>
                            </div>
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

    .alert {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .alert .fas {
        font-size: 1.25rem;
    }

    .alert strong {
        font-weight: 600;
    }

    .alert small {
        color: #666;
        font-size: 0.875rem;
    }

    .btn-close {
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }

    .btn-close:hover {
        opacity: 0.75;
    }
</style>

<script>
function updateQuantity(cartId, change) {
    const input = document.querySelector(`input[name="quantity[${cartId}]"]`);
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    const newValue = currentValue + change;
    
    if (newValue >= 1 && newValue <= maxValue) {
        input.value = newValue;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.querySelectorAll('input[name="payment_method"]');
    const proceedBtn = document.getElementById('proceedToPayment');
    const confirmCashBtn = document.getElementById('confirmCashPayment');
    const cartForm = document.getElementById('cartForm');
    
    // Handle payment method change
    paymentMethod.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'cash') {
                proceedBtn.classList.add('d-none');
                confirmCashBtn.classList.remove('d-none');
            } else {
                proceedBtn.classList.remove('d-none');
                confirmCashBtn.classList.add('d-none');
            }
        });
    });

    // Handle form submission
    cartForm.addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (selectedPayment === 'cash') {
            // Show loading state
            confirmCashBtn.disabled = true;
            confirmCashBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Add a hidden input for cash payment
            const cashPaymentInput = document.createElement('input');
            cashPaymentInput.type = 'hidden';
            cashPaymentInput.name = 'cash_payment';
            cashPaymentInput.value = '1';
            this.appendChild(cashPaymentInput);
            
            // Let the form submit
            return true;
        }
    });

    // Handle Khalti payment
    proceedBtn.addEventListener('click', function() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (selectedPayment === 'khalti') {
            // Show loading state
            proceedBtn.disabled = true;
            proceedBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Get the total amount from the page
            const totalAmount = <?php echo $total; ?>;
            
            // Make API call to initiate payment
            fetch('khalti-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: totalAmount,
                    return_url: window.location.origin + '/Kaamdar_PHP/Requester/payment-success.php'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Payment response:', data);
                if (data.success && data.payment_url) {
                    // Redirect to Khalti payment page
                    window.location.href = data.payment_url;
                } else {
                    throw new Error(data.error || 'Payment initiation failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment failed: ' + error.message);
                // Reset button state
                proceedBtn.disabled = false;
                proceedBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
            });
        }
    });
});
</script> 