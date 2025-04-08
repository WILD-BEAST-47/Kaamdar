<?php
define('TITLE', 'Sold Products Report');
define('PAGE', 'soldproductreport');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get all orders with product details
$sql = "SELECT o.order_id, o.user_id, o.total_amount, o.payment_status, o.payment_method, 
               o.created_at, r.r_name, r.r_email, r.r_mobile,
               oi.product_id, oi.quantity, oi.price, a.pname
        FROM orders_tb o
        JOIN requesterlogin_tb r ON o.user_id = r.r_login_id
        JOIN order_items_tb oi ON o.order_id = oi.order_id
        JOIN assets_tb a ON oi.product_id = a.pid
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Sold Products Report</h2>
                        <p class="text-muted">View all sold products and orders</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row['order_id']; ?></td>
                                <td>
                                    <?php echo $row['r_name']; ?>
                                    <small class="d-block text-muted"><?php echo $row['r_email']; ?></small>
                                </td>
                                <td><?php echo $row['r_mobile']; ?></td>
                                <td><?php echo $row['pname']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td>Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                        <?php echo $row['payment_status']; ?>
                                    </span>
                                    <small class="d-block"><?php echo $row['payment_method']; ?></small>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="viewOrder(<?php echo $row['order_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            onclick="printInvoice(<?php echo $row['order_id']; ?>)">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="10" class="text-center">No orders found</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrder(orderId) {
    window.location.href = 'view-order.php?id=' + orderId;
}

function printInvoice(orderId) {
    window.open('print-invoice.php?id=' + orderId, '_blank');
}
</script>

<style>
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .badge {
        padding: 0.5em 0.75em;
        border-radius: 4px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-primary {
        background-color: #f3961c;
        border-color: #f3961c;
    }
    
    .btn-primary:hover {
        background-color: #e08a1a;
        border-color: #e08a1a;
    }
    
    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }
    
    .btn-info:hover {
        background-color: #138496;
        border-color: #138496;
        color: white;
    }
</style>

<?php include('includes/footer.php'); ?>