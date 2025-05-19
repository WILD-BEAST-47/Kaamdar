<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) : 0;

if($product_id > 0) {
    // Get product details
    $sql = "SELECT a.*, c.category_name 
            FROM assets_tb a 
            LEFT JOIN product_categories_tb c ON a.category_id = c.category_id 
            WHERE a.pid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $msg = '<div class="alert alert-warning">Product not found!</div>';
    }
    $stmt->close();
} else {
    $msg = '<div class="alert alert-warning">Invalid product ID!</div>';
}

// Handle add to cart
if(isset($_POST['add_to_cart']) && isset($product)) {
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['r_login_id'];
    
    // Check if product is already in cart
    $check_sql = "SELECT * FROM shopping_cart_tb WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        // Update quantity
        $update_sql = "UPDATE shopping_cart_tb SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Add new item
        $insert_sql = "INSERT INTO shopping_cart_tb (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
    
    $success_msg = '<div class="alert alert-success">Product added to cart successfully!</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($product) ? htmlspecialchars($product['pname']) : 'Product Details'; ?> - KaamDar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f3961c;
            --secondary-color: #333;
            --accent-color: #f3961c;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --dark-bg: #333;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        .product-detail-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: white;
        }
        
        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .product-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .product-price {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .category-badge {
            background: var(--light-bg);
            color: var(--text-color);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: rgba(243, 150, 28, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(243, 150, 28, 0.2);
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>
    
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <a href="products.php" class="btn btn-primary mb-3">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>

        <?php if(isset($msg)) echo $msg; ?>
        <?php if(isset($success_msg)) echo $success_msg; ?>

        <?php if(isset($product)): ?>
        <div class="row">
            <div class="col-md-6 mb-4">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/600x400'); ?>" 
                     alt="<?php echo htmlspecialchars($product['pname']); ?>" 
                     class="product-image">
            </div>
            <div class="col-md-6">
                <div class="product-detail-card p-4">
                    <span class="category-badge"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                    <h1 class="product-title mt-3"><?php echo htmlspecialchars($product['pname']); ?></h1>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                    
                    <div class="d-flex align-items-center mb-4">
                        <span class="product-price me-3">₹<?php echo number_format($product['psellingcost'], 2); ?></span>
                        <span class="badge bg-success">In Stock: <?php echo $product['quantity']; ?></span>
                    </div>
                    
                    <form method="POST" class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <label for="quantity" class="me-3">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" class="form-control quantity-input" 
                                   value="1" min="1" max="<?php echo $product['quantity']; ?>">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                    </form>
                    
                    <div class="product-details">
                        <h5 class="mb-3">Product Details</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></li>
                            <li class="mb-2"><strong>Available Quantity:</strong> <?php echo $product['quantity']; ?></li>
                            <li class="mb-2"><strong>Added Date:</strong> <?php echo date('M d, Y', strtotime($product['created_at'])); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 