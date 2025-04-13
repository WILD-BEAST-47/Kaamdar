<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    die('Please log in to view order details.');
}

// Get order ID from request
$order_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if(!$order_id) {
    die('Invalid order ID.');
}

// Get user's ID from session
$user_id = $_SESSION['r_login_id'];

// Get order details and items
$sql = "SELECT o.*, oi.*, a.pname, a.image_url
        FROM orders_tb o
        JOIN order_items_tb oi ON o.order_id = oi.order_id
        JOIN assets_tb a ON oi.product_id = a.pid
        WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    die('Order not found or access denied.');
}

// Get the first row for order details
$first_item = $result->fetch_assoc();
$order_date = date('F j, Y, g:i a', strtotime($first_item['created_at']));

// Reset result pointer
$result->data_seek(0);
?>

<div class="order-details">
    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Order Information</h6>
            <p class="mb-1">Order #<?php echo $order_id; ?></p>
            <p class="mb-1">Placed on: <?php echo $order_date; ?></p>
            <p class="mb-1">
                Status: 
                <span class="badge bg-<?php echo $first_item['payment_status'] == 'Paid' ? 'success' : 'warning'; ?>">
                    <?php echo $first_item['payment_status']; ?>
                </span>
            </p>
            <p class="mb-1">Payment Method: <?php echo $first_item['payment_method']; ?></p>
        </div>
    </div>

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
                $total = 0;
                while($item = $result->fetch_assoc()):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'https://via.placeholder.com/50'); ?>" 
                                 alt="<?php echo htmlspecialchars($item['pname']); ?>"
                                 class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <?php echo htmlspecialchars($item['pname']); ?>
                            </div>
                        </div>
                    </td>
                    <td>NPR <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td class="text-end">NPR <?php echo number_format($item_total, 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                    <td class="text-end"><strong>NPR <?php echo number_format($total, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print Order
        </button>
    </div>
</div>

<style>
@media print {
    .modal-header, .btn, .modal-footer {
        display: none !important;
    }
    .modal {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        overflow: visible !important;
    }
    .modal-dialog {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
    }
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
}
</style> 