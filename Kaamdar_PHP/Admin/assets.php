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
                        <p class="text-muted">Manage your product inventory</p>
                    </div>
                    <a href="addproduct.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Product
                    </a>
                </div>
            </div>
        </div>

        <?php if($result->num_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
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
                                        <div class="d-flex align-items-center">
                                            <?php if($row['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($row['pname']); ?>" 
                                                     class="rounded me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($row['pname']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['description']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($row['category_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">₹<?php echo number_format($row['psellingcost'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo htmlspecialchars($row['pava']); ?> in stock
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_class = $row['pava'] > 0 ? 'bg-success' : 'bg-danger';
                                        $status_text = $row['pava'] > 0 ? 'In Stock' : 'Out of Stock';
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="editproduct.php?id=<?php echo $row['pid']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $row['pid']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete" 
                                                        onclick="return confirm('Are you sure you want to delete this product?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No products found.
            </div>
        <?php endif; ?>

        <?php
        // Handle delete request
        if(isset($_REQUEST['delete'])) {
            $sql = "DELETE FROM assets_tb WHERE pid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_REQUEST['id']);
            
            if($stmt->execute()) {
                echo '<div class="alert alert-success">Product deleted successfully!</div>';
                echo '<meta http-equiv="refresh" content="2;URL=?deleted" />';
            } else {
                echo '<div class="alert alert-danger">Unable to delete product.</div>';
            }
            $stmt->close();
        }
        ?>
    </div>
</div>

<style>
    :root {
        --primary-color: #f3961c;
        --secondary-color: #333;
        --accent-color: #f3961c;
        --text-color: #333;
        --light-bg: #f8f9fa;
        --dark-bg: #333;
    }
    
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .table {
        margin-bottom: 0;
        font-size: 0.9rem;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .table tbody tr:hover {
        background-color: rgba(243, 150, 28, 0.05);
    }
    
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
        font-size: 0.8rem;
    }
    
    .btn-group {
        gap: 0.5rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
        
        .badge {
            padding: 0.25em 0.5em;
        }
    }
</style>

<?php include('includes/footer.php'); ?>