<?php
session_start();
define('TITLE', 'Order Confirmation');
define('PAGE', 'orders');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Check if order ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script> location.href='orders.php'; </script>";
    exit;
}

$order_id = (int)$_GET['id'];

// Get order details
$sql = "SELECT o.*, r.r_name, r.r_email 
        FROM orders_tb o 
        JOIN requesterlogin_tb r ON o.user_id = r.r_login_id 
        WHERE o.order_id = ? AND o.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['myid']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo "<script> location.href='orders.php'; </script>";
    exit;
}

$order = $result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.pname, p.image_url 
              FROM order_items_tb oi 
              JOIN assets_tb p ON oi.product_id = p.pid 
              WHERE oi.order_id = ?";

$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <h2 class="card-title">Order Confirmed!</h2>
                            <p class="text-muted">Thank you for your purchase. Your order has been received.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Order Details</h5>
                                        <ul class="list-unstyled">
                                            <li><strong>Order ID:</strong> #<?php echo $order_id; ?></li>
                                            <li><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></li>
                                            <li><strong>Status:</strong> 
                                                <span class="badge bg-<?php 
                                                    echo $order['status'] == 'pending' ? 'warning' : 
                                                        ($order['status'] == 'processing' ? 'info' : 
                                                        ($order['status'] == 'completed' ? 'success' : 'danger')); 
                                                ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </li>
                                            <li><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></li>
                                            <li><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Shipping Information</h5>
                                        <ul class="list-unstyled">
                                            <li><strong>Name:</strong> <?php echo htmlspecialchars($order['r_name']); ?></li>
                                            <li><strong>Email:</strong> <?php echo htmlspecialchars($order['r_email']); ?></li>
                                            <li><strong>Address:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Order Items</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($item = $items_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if(!empty($item['image_url'])): ?>
                                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                                     class="rounded me-3" 
                                                                     alt="<?php echo htmlspecialchars($item['pname']); ?>"
                                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                                     style="width: 50px; height: 50px;">
                                                                    <i class="fas fa-box text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div><?php echo htmlspecialchars($item['pname']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                                    <td><?php echo $item['quantity']; ?></td>
                                                    <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="orders.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>View All Orders
                            </a>
                            <a href="products.php" class="btn btn-outline-primary ms-2">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php'); 
$conn->close();
?> 