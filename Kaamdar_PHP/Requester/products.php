<?php
session_start();
define('TITLE', 'Products');
define('PAGE', 'Products');
include('includes/header.php');
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get user's login ID from session or database
if(!isset($_SESSION['r_login_id'])) {
    // If r_login_id is not in session, get it from database
    $user_email = $_SESSION['rEmail'];
    $user_sql = "SELECT r_login_id FROM requesterlogin_tb WHERE r_email = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("s", $user_email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $_SESSION['r_login_id'] = $user['r_login_id'];
        $user_id = $user['r_login_id'];
    } else {
        echo "<script>alert('User not found!'); window.location.href='../RequesterLogin.php';</script>";
        exit;
    }
    $user_stmt->close();
} else {
    $user_id = $_SESSION['r_login_id'];
}

// Handle Add to Cart
if(isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Check if product exists and is available
    $check_sql = "SELECT pava FROM assets_tb WHERE pid = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        $product = $check_result->fetch_assoc();
        if($product['pava'] > 0) {
            // Check if product already in cart
            $cart_sql = "SELECT * FROM shopping_cart_tb WHERE user_id = ? AND product_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("ii", $user_id, $product_id);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            
            if($cart_result->num_rows > 0) {
                // Update quantity if already in cart
                $update_sql = "UPDATE shopping_cart_tb SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $user_id, $product_id);
                $update_stmt->execute();
            } else {
                // Add new item to cart
                $insert_sql = "INSERT INTO shopping_cart_tb (user_id, product_id, quantity) VALUES (?, ?, 1)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ii", $user_id, $product_id);
                $insert_stmt->execute();
            }
            
            echo "<script>alert('Product added to cart successfully!'); window.location.href='cart.php';</script>";
        } else {
            echo "<script>alert('Product is out of stock!');</script>";
        }
    } else {
        echo "<script>alert('Product not found!');</script>";
    }
}

// Get category filter if set
$category_id = isset($_GET['category']) ? filter_input(INPUT_GET, 'category', FILTER_SANITIZE_NUMBER_INT) : null;

// Build the query
$sql = "SELECT a.*, c.category_name 
        FROM assets_tb a 
        LEFT JOIN product_categories_tb c ON a.category_id = c.category_id 
        WHERE a.pava > 0";

$params = [];
$types = "";

if($category_id) {
    $sql .= " AND a.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories_sql = "SELECT * FROM product_categories_tb ORDER BY category_name";
$categories_result = $conn->query($categories_sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container py-4">
        <div class="page-header text-center">
            <h1 class="mb-0">KaamDar Marketplace</h1>
            <p class="mb-0">Find the tools and equipment you need</p>
        </div>

        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="search-bar">
                        <input type="text" placeholder="Search products..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <a href="products.php" class="btn btn-outline-secondary me-2">All Products</a>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Category
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                                <?php 
                                if($categories_result && $categories_result->num_rows > 0):
                                    while($category = $categories_result->fetch_assoc()): 
                                ?>
                                <li><a class="dropdown-item" href="products.php?category=<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </a></li>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <li><span class="dropdown-item text-muted">No categories found</span></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($product = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/300x200'); ?>" 
                             alt="<?php echo htmlspecialchars($product['pname']); ?>" 
                             class="product-image w-100">
                        <div class="card-body">
                            <span class="category-badge"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($product['pname']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                            <p class="price">₹<?php echo number_format($product['psellingcost'], 2); ?></p>
                            <p class="text-muted">Available: <?php echo $product['pava']; ?> units</p>
                            <a href="products.php?action=add&id=<?php echo $product['pid']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> No products found. Please check back later.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Simple search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const products = document.querySelectorAll('.product-card');
        
        products.forEach(product => {
            const title = product.querySelector('.card-title').textContent.toLowerCase();
            const description = product.querySelector('.card-text').textContent.toLowerCase();
            
            if (title.includes(searchText) || description.includes(searchText)) {
                product.closest('.col-md-6').style.display = '';
            } else {
                product.closest('.col-md-6').style.display = 'none';
            }
        });
    });
</script>

<style>
    :root {
        --primary-color: #f3961c;
        --secondary-color: #333;
        --accent-color: #f3961c;
        --text-color: #333;
        --light-bg: #f8f9fa;
        --dark-bg: #333;
    }
    
    .product-card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: white;
        margin-bottom: 1.5rem;
        height: 100%;
    }
    
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .product-image {
        height: 200px;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
    }
    
    .product-card .card-body {
        padding: 1.5rem;
    }
    
    .product-card .card-title {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .product-card .card-text {
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    
    .product-card .price {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .category-badge {
        background-color: var(--primary-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 500;
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

    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .search-bar {
        margin-bottom: 2rem;
    }

    .search-bar input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .page-header {
        background: var(--primary-color);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 8px;
    }
</style> 