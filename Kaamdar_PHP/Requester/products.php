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
if(isset($_POST['add_to_cart'])) {
    $pid = filter_input(INPUT_POST, 'pid', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    
    if($pid && $quantity > 0) {
        // Check if product exists and is available
        $check_sql = "SELECT pava FROM assets_tb WHERE pid = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $pid);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if($check_result->num_rows > 0) {
            $product = $check_result->fetch_assoc();
            if($product['pava'] >= $quantity) {
                // Check if product already in cart
                $cart_sql = "SELECT * FROM shopping_cart_tb WHERE user_id = ? AND product_id = ?";
                $cart_stmt = $conn->prepare($cart_sql);
                $cart_stmt->bind_param("ii", $user_id, $pid);
                $cart_stmt->execute();
                $cart_result = $cart_stmt->get_result();
                
                if($cart_result->num_rows > 0) {
                    // Update quantity if already in cart
                    $update_sql = "UPDATE shopping_cart_tb SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("iii", $quantity, $user_id, $pid);
                    $update_stmt->execute();
                } else {
                    // Add new item to cart
                    $insert_sql = "INSERT INTO shopping_cart_tb (user_id, product_id, quantity) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("iii", $user_id, $pid, $quantity);
                    $insert_stmt->execute();
                }
                
                echo "<script>alert('Product added to cart successfully!'); window.location.href='cart.php';</script>";
            } else {
                echo "<script>alert('Not enough stock available!');</script>";
            }
        } else {
            echo "<script>alert('Product not found!');</script>";
        }
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : PHP_FLOAT_MAX;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build the SQL query
$sql = "SELECT a.*, c.category_name 
        FROM assets_tb a 
        LEFT JOIN product_categories_tb c ON a.category_id = c.category_id 
        WHERE 1=1";

$params = [];
$types = "";

// Add search condition
if (!empty($search)) {
    $sql .= " AND (a.pname LIKE ? OR a.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Add category filter
if (!empty($category)) {
    $sql .= " AND a.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

// Add price range filter
$sql .= " AND a.psellingcost >= ? AND a.psellingcost <= ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= "dd";

// Add sorting
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY a.psellingcost ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY a.psellingcost DESC";
        break;
    case 'name':
        $sql .= " ORDER BY a.pname ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY a.pid DESC";
        break;
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter dropdown
$category_sql = "SELECT * FROM product_categories_tb ORDER BY category_name";
$category_result = $conn->query($category_sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Products</h2>
                        <p class="text-muted">Browse our products</p>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filters</h5>
                        <form method="GET" id="filterForm">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php while($cat = $category_result->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category_name']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Price Range</label>
                                <div class="input-group">
                                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $min_price; ?>">
                                    <span class="input-group-text">to</span>
                                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $max_price; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="row">
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <?php 
                                $image_path = $row['image_url'] ? '../' . $row['image_url'] : 'assets/images/default-product.png';
                                if (!file_exists($image_path)) {
                                    $image_path = 'assets/images/default-product.png';
                                }
                                ?>
                                <img src="<?php echo $image_path; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($row['pname']); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['pname']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary">NPR <?php echo number_format($row['psellingcost'], 2); ?></span>
                                        <span class="badge bg-<?php echo $row['pava'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $row['pava'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <form method="POST" class="d-flex justify-content-between">
                                        <input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
                                        <input type="number" name="quantity" class="form-control me-2" value="1" min="1" max="<?php echo $row['pava']; ?>" style="width: 80px;">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary" <?php echo $row['pava'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No products found matching your criteria.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Keep search parameter when applying filters
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && searchInput.value) {
        const searchParam = document.createElement('input');
        searchParam.type = 'hidden';
        searchParam.name = 'search';
        searchParam.value = searchInput.value;
        this.appendChild(searchParam);
    }
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