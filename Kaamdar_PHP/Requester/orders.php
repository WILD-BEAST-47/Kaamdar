<?php
session_start();
define('TITLE', 'My Orders');
define('PAGE', 'orders');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items_tb WHERE order_id = o.order_id) as total_items 
        FROM orders_tb o 
        WHERE o.user_id = ?";

$params = [$_SESSION['myid']];
$types = "i";

if(!empty($status)) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

if(!empty($date_from)) {
    $sql .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if(!empty($date_to)) {
    $sql .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">
                            <i class="fas fa-shopping-bag me-2"></i>My Orders
                        </h2>
                        
                        <!-- Filter Form -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </form>
                        
                        <?php if($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $row['order_id']; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($row['order_date'])); ?></td>
                                                <td><?php echo $row['total_items']; ?> items</td>
                                                <td>₹<?php echo number_format($row['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $row['status'] == 'pending' ? 'warning' : 
                                                            ($row['status'] == 'processing' ? 'info' : 
                                                            ($row['status'] == 'completed' ? 'success' : 'danger')); 
                                                    ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo ucfirst($row['payment_method']); ?></td>
                                                <td>
                                                    <a href="order_confirmation.php?id=<?php echo $row['order_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                                <h4>No orders found</h4>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                                </a>
                            </div>
                        <?php endif; ?>
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