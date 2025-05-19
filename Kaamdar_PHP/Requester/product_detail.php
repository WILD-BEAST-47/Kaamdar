<?php
session_start();
define('TITLE', 'Product Details');
define('PAGE', 'products');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Check if product ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script> location.href='products.php'; </script>";
    exit;
}

$product_id = (int)$_GET['id'];

// Get product details
$sql = "SELECT p.*, c.category_name 
        FROM assets_tb p 
        LEFT JOIN product_categories_tb c ON p.category_id = c.category_id 
        WHERE p.pid = ? AND p.pava > 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    echo "<script> location.href='products.php'; </script>";
    exit;
}

$product = $result->fetch_assoc();

// Handle add to cart
$message = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if($quantity > 0 && $quantity <= $product['pava']) {
        // Check if product already in cart
        $check_sql = "SELECT * FROM shopping_cart_tb WHERE user_id = ? AND product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $_SESSION['myid'], $product_id);
        $check_stmt->execute();
        $cart_result = $check_stmt->get_result();
        
        if($cart_result->num_rows > 0) {
            // Update quantity
            $cart_item = $cart_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            if($new_quantity <= $product['pava']) {
                $update_sql = "UPDATE shopping_cart_tb SET quantity = ? WHERE cart_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $new_quantity, $cart_item['cart_id']);
                $update_stmt->execute();
                $message = '<div class="alert alert-success">Cart updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Cannot add more items than available in stock!</div>';
            }
        } else {
            // Add new item to cart
            $insert_sql = "INSERT INTO shopping_cart_tb (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $_SESSION['myid'], $product_id, $quantity);
            $insert_stmt->execute();
            $message = '<div class="alert alert-success">Product added to cart successfully!</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Invalid quantity!</div>';
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['pname']); ?></li>
                            </ol>
                        </nav>
                        
                        <?php echo $message; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?php if(!empty($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo htmlspecialchars($product['pname']); ?>">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 400px;">
                                        <i class="fas fa-box fa-6x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h1 class="h2 mb-3"><?php echo htmlspecialchars($product['pname']); ?></h1>
                                
                                <p class="text-muted mb-3">
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </p>
                                
                                <div class="mb-4">
                                    <h3 class="text-primary mb-0">₹<?php echo number_format($product['psellingcost'], 2); ?></h3>
                                    <small class="text-muted">Price per unit</small>
                                </div>
                                
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available')); ?></p>
                                </div>
                                
                                <div class="mb-4">
                                    <h5>Product Details</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Product ID:</strong> <?php echo $product['pid']; ?></li>
                                        <li><strong>Available Stock:</strong> <?php echo $product['pava']; ?> units</li>
                                        <li><strong>Added Date:</strong> <?php echo date('F j, Y', strtotime($product['pdate'])); ?></li>
                                    </ul>
                                </div>
                                
                                <form method="POST" class="mb-4">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <label for="quantity" class="form-label">Quantity:</label>
                                        </div>
                                        <div class="col-auto">
                                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                                   value="1" min="1" max="<?php echo $product['pava']; ?>" 
                                                   style="width: 100px;">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary">
                                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="d-grid gap-2">
                                    <a href="cart.php" class="btn btn-outline-primary">
                                        <i class="fas fa-shopping-cart me-2"></i>View Cart
                                    </a>
                                </div>
                            </div>
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