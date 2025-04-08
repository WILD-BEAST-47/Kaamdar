<?php
define('TITLE', 'View Order');
define('PAGE', 'soldproductreport');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if order ID is provided
if(!isset($_GET['id'])) {
    echo "<script> location.href='soldproductreport.php'; </script>";
    exit;
}

$order_id = $_GET['id'];

// Get order details
$sql = "SELECT o.*, r.r_name, r.r_email, r.r_mobile
        FROM orders_tb o
        JOIN requesterlogin_tb r ON o.user_id = r.r_login_id
        WHERE o.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$sql = "SELECT oi.*, a.pname, a.psellingcost
        FROM order_items_tb oi
        JOIN assets_tb a ON oi.product_id = a.pid
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Order Details #<?php echo $order_id; ?></h2>
                        <p class="text-muted">View order information</p>
                    </div>
                    <a href="soldproductreport.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Reports
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Customer Information</h5>
                        <hr>
                        <p><strong>Name:</strong> <?php echo $order['r_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $order['r_email']; ?></p>
                        <p><strong>Mobile:</strong> <?php echo $order['r_mobile']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title">Order Information</h5>
                        <hr>
                        <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge bg-<?php echo $order['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                <?php echo $order['payment_status']; ?>
                            </span>
                        </p>
                        <p><strong>Payment Method:</strong> <?php echo $order['payment_method']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title">Order Items</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            while($item = $items->fetch_assoc()) {
                                $total = $item['price'] * $item['quantity'];
                            ?>
                            <tr>
                                <td><?php echo $item['pname']; ?></td>
                                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td class="text-end">Rs. <?php echo number_format($total, 2); ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                                <td class="text-end"><strong>Rs. <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    .card-title {
        color: #333;
        font-size: 1.1rem;
        margin-bottom: 0;
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
    
    hr {
        margin: 1rem 0;
        opacity: 0.1;
    }
</style>

<?php include('includes/footer.php'); ?> 