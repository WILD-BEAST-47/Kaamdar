<?php
session_start();
define('TITLE', 'Assets');
define('PAGE', 'assets');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get all products with category details
$sql = "SELECT a.*, c.category_name 
        FROM assets_tb a 
        LEFT JOIN product_categories_tb c ON a.category_id = c.category_id 
        ORDER BY a.pid DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Products</h2>
                        <p class="text-muted">Manage your products</p>
                    </div>
                    <a href="addproduct.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Product
                    </a>
                </div>
            </div>
        </div>

        <?php 
        // Display success/error messages
        if(isset($_SESSION['success_msg'])) {
            echo $_SESSION['success_msg'];
            unset($_SESSION['success_msg']);
        }
        if(isset($_SESSION['error_msg'])) {
            echo $_SESSION['error_msg'];
            unset($_SESSION['error_msg']);
        }
        ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $image_path = $row['image_url'] ? '../' . $row['image_url'] : '../assets/images/default-product.png';
                                    if (!file_exists($image_path)) {
                                        $image_path = '../assets/images/default-product.png';
                                    }
                                    ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($row['pname']); ?>" 
                                         class="product-image" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['pname']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>NPR <?php echo number_format($row['psellingcost'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $row['pava'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $row['pava']; ?> in stock
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['pava'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $row['pava'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editproduct.php?id=<?php echo $row['pid']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="deleteproduct.php?id=<?php echo $row['pid']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
function deleteProduct(pid) {
    if(confirm('Are you sure you want to delete this product?')) {
        window.location.href = 'deleteproduct.php?id=' + pid;
    }
}
</script>

<?php include('includes/footer.php'); ?>