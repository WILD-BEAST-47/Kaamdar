<?php
session_start();
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?> - KaamDar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                margin: 0;
                size: A4;
            }
            body {
                margin: 1.6cm;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            background: #fff;
        }
        
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        
        .invoice-box table tr.total td {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="text-end mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print Invoice
            </button>
        </div>
        
        <div class="invoice-box">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                        <h2>KaamDar</h2>
                        <p class="text-muted mb-0">Invoice #<?php echo $order_id; ?></p>
                        <p class="text-muted">Date: <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <td style="width: 50%;">
                        <strong>Billed To:</strong><br>
                        <?php echo $order['r_name']; ?><br>
                        <?php echo $order['r_email']; ?><br>
                        <?php echo $order['r_mobile']; ?>
                    </td>
                    <td style="width: 50%;" class="text-end">
                        <strong>Payment Details:</strong><br>
                        Status: <?php echo $order['payment_status']; ?><br>
                        Method: <?php echo $order['payment_method']; ?>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2">
                        <table class="table mt-4">
                            <thead>
                                <tr class="heading">
                                    <td>Item</td>
                                    <td class="text-end">Price</td>
                                    <td class="text-end">Quantity</td>
                                    <td class="text-end">Total</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while($item = $items->fetch_assoc()) {
                                    $total = $item['price'] * $item['quantity'];
                                ?>
                                <tr class="item">
                                    <td><?php echo $item['pname']; ?></td>
                                    <td class="text-end">Rs. <?php echo number_format($item['price'], 2); ?></td>
                                    <td class="text-end"><?php echo $item['quantity']; ?></td>
                                    <td class="text-end">Rs. <?php echo number_format($total, 2); ?></td>
                                </tr>
                                <?php } ?>
                                <tr class="total">
                                    <td colspan="3" class="text-end">Total Amount:</td>
                                    <td class="text-end">Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2" class="text-center mt-4">
                        <p class="text-muted">Thank you for your business!</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html> 