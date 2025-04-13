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

// Get all orders for the user
$sql = "SELECT o.*, 
               COUNT(oi.order_id) as total_items
        FROM orders_tb o
        LEFT JOIN order_items_tb oi ON o.order_id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
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

        <?php if($orders->num_rows > 0): ?>
            <div class="row">
                <?php while($order = $orders->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                    <span class="badge bg-<?php echo $order['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $order['payment_status']; ?>
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">Ordered on: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></small>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Total Items:</span>
                                    <span><?php echo $order['total_items']; ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Total Amount:</span>
                                    <span class="fw-bold">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                    
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Payment via <?php echo $order['payment_method']; ?></small>
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Order Details Modal -->
            <div class="modal fade" id="orderDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="orderDetailsContent">
                            Loading...
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-info">
                You haven't placed any orders yet. <a href="products.php" class="alert-link">Start shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const contentDiv = document.getElementById('orderDetailsContent');
    
    // Show modal with loading state
    modal.show();
    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    // Fetch order details
    fetch(`get-order-details.php?id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading order details. Please try again.</div>';
        });
}
</script>

<?php include('includes/footer.php'); ?> 