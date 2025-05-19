<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    header("Location: ../RequesterLogin.php");
    exit;
}

// Get user's login ID from session
$user_id = $_SESSION['r_login_id'];

// Get all orders for the user
$sql = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', a.pname) SEPARATOR ', ') as products
        FROM orders_tb o 
        LEFT JOIN order_items_tb oi ON o.order_id = oi.order_id
        LEFT JOIN assets_tb a ON oi.product_id = a.pid
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Now include the header and start HTML output
define('TITLE', 'My Orders');
define('PAGE', 'Orders');
include('includes/header.php');
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">My Orders</h2>
                        <p class="text-muted">View and manage your orders</p>
                    </div>
                    <a href="RequesterDashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <?php if(empty($orders)): ?>
            <div class="alert alert-info">
                You haven't placed any orders yet. <a href="products.php" class="alert-link">Start shopping</a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Products</th>
                                    <th>Total Amount</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['products']); ?></td>
                                    <td>NPR <?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($order['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($order['order_status']) {
                                            case 'Paid':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'Pending':
                                                $status_class = 'bg-warning';
                                                break;
                                            case 'Cancelled':
                                                $status_class = 'bg-danger';
                                                break;
                                            default:
                                                $status_class = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if($order['order_status'] !== 'Cancelled'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="cancelOrder(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    // Show loading state
    document.getElementById('orderDetailsContent').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Fetch order details
    fetch('get_order_details.php?id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                let html = `
                    <div class="order-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p><strong>Order ID:</strong> #${data.order.order_id}</p>
                                <p><strong>Date:</strong> ${new Date(data.order.created_at).toLocaleDateString()}</p>
                                <p><strong>Status:</strong> <span class="badge bg-${getStatusClass(data.order.order_status)}">${data.order.order_status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Information</h6>
                                <p><strong>Payment Method:</strong> ${data.order.payment_method}</p>
                                <p><strong>Payment Status:</strong> <span class="badge bg-${getStatusClass(data.order.payment_status)}">${data.order.payment_status}</span></p>
                                <p><strong>Total Amount:</strong> NPR ${parseFloat(data.order.total_amount).toFixed(2)}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.items.map(item => `
                                        <tr>
                                            <td>${item.pname}</td>
                                            <td>${item.quantity}</td>
                                            <td>NPR ${parseFloat(item.price).toFixed(2)}</td>
                                            <td>NPR ${(item.quantity * item.price).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                document.getElementById('orderDetailsContent').innerHTML = html;
            } else {
                document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading order details</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('orderDetailsContent').innerHTML = '<div class="alert alert-danger">Error loading order details</div>';
        });
}

function cancelOrder(orderId) {
    if(confirm('Are you sure you want to cancel this order?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Order cancelled successfully');
                location.reload();
            } else {
                alert(data.error || 'Error cancelling order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling order');
        });
    }
}

function getStatusClass(status) {
    switch(status) {
        case 'Paid':
            return 'success';
        case 'Pending':
            return 'warning';
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
</script>

<style>
.table th {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.85em;
    padding: 0.5em 0.75em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.order-details {
    padding: 1rem;
}

.order-details h6 {
    color: #6c757d;
    margin-bottom: 1rem;
}

.order-details p {
    margin-bottom: 0.5rem;
}

.table-responsive {
    margin-top: 1rem;
}
</style> 