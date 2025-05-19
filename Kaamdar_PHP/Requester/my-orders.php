<?php
session_start();
define('TITLE', 'My Orders');
define('PAGE', 'MyOrders');
include('includes/header.php');
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$user_id = $_SESSION['r_login_id'];

// Handle order cancellation
if(isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $sql = "UPDATE orders_tb SET order_status = 'Cancelled' WHERE order_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch orders
$sql = "SELECT o.*, GROUP_CONCAT(oi.product_id) as product_ids, GROUP_CONCAT(oi.quantity) as quantities, GROUP_CONCAT(oi.price) as prices 
        FROM orders_tb o 
        JOIN order_items_tb oi ON o.order_id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.order_id 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>My Orders</h2>
                <p class="text-muted">View your order history</p>
            </div>
        </div>

        <?php if(isset($_SESSION['payment_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Payment successful! Your order #<?php echo $_SESSION['order_id']; ?> has been placed.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
            unset($_SESSION['payment_success']);
            unset($_SESSION['order_id']);
        endif; ?>

        <div class="row">
            <?php while($order = $result->fetch_assoc()): 
                $product_ids = explode(',', $order['product_ids']);
                $quantities = explode(',', $order['quantities']);
                $prices = explode(',', $order['prices']);
            ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order #<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></h5>
                            <span class="badge bg-<?php 
                                echo $order['order_status'] == 'Processing' ? 'warning' : 
                                    ($order['order_status'] == 'Completed' ? 'success' : 
                                    ($order['order_status'] == 'Cancelled' ? 'danger' : 'info')); 
                            ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1"><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                                <p class="mb-1"><strong>Payment Status:</strong> 
                                    <span class="badge bg-<?php echo $order['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $order['payment_status']; ?>
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Total Amount:</strong> NPR <?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                            
                            <h6 class="mb-3">Order Items:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i = 0; $i < count($product_ids); $i++): 
                                            $product_sql = "SELECT pname FROM assets_tb WHERE pid = ?";
                                            $product_stmt = $conn->prepare($product_sql);
                                            $product_stmt->bind_param("i", $product_ids[$i]);
                                            $product_stmt->execute();
                                            $product_result = $product_stmt->get_result();
                                            $product = $product_result->fetch_assoc();
                                            $subtotal = $quantities[$i] * $prices[$i];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['pname']); ?></td>
                                                <td><?php echo $quantities[$i]; ?></td>
                                                <td>NPR <?php echo number_format($prices[$i], 2); ?></td>
                                                <td>NPR <?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if($order['order_status'] == 'Processing'): ?>
                                <form action="" method="POST" class="mt-3">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="cancel_order" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')">
                                        <i class="fas fa-times me-1"></i>Cancel Order
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.table {
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    color: #666;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

<?php include('includes/footer.php'); ?> 