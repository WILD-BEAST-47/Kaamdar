<?php
session_start();
define('TITLE', 'Sell Product');
define('PAGE', 'sellproduct');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get all products with category details
$sql = "SELECT a.*, c.cat_name 
        FROM assets_tb a 
        LEFT JOIN product_categories_tb c ON a.p_cat_id = c.cat_id 
        WHERE a.p_availability = 'In Stock' 
        ORDER BY a.p_name ASC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Sell Products</h2>
                <p class="text-muted">Manage product sales and inventory</p>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($row['p_img']): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($row['p_img']); ?>" 
                                                     alt="<?php echo htmlspecialchars($row['p_name']); ?>" 
                                                     class="rounded me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($row['p_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['p_desc']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($row['cat_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">NPR <?php echo number_format($row['p_price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo htmlspecialchars($row['p_quantity']); ?> in stock
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#sellProductModal<?php echo $row['p_id']; ?>">
                                                <i class="fas fa-shopping-cart"></i> Sell
                                            </button>
                                        </div>

                                        <!-- Sell Product Modal -->
                                        <div class="modal fade" id="sellProductModal<?php echo $row['p_id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Sell Product</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="p_id" value="<?php echo $row['p_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Product Name</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['p_name']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Price</label>
                                                                <input type="text" class="form-control" value="NPR <?php echo number_format($row['p_price'], 2); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Available Stock</label>
                                                                <input type="text" class="form-control" value="<?php echo $row['p_quantity']; ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Quantity to Sell</label>
                                                                <input type="number" class="form-control" name="quantity" 
                                                                       min="1" max="<?php echo $row['p_quantity']; ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Customer Name</label>
                                                                <input type="text" class="form-control" name="customer_name" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Customer Email</label>
                                                                <input type="email" class="form-control" name="customer_email" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary" name="sell">Sell Product</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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
                <i class="fas fa-info-circle"></i> No products available for sale.
            </div>
        <?php endif; ?>

        <?php
        // Handle product sale
        if(isset($_POST['sell'])) {
            $p_id = $_POST['p_id'];
            $quantity = $_POST['quantity'];
            $customer_name = $_POST['customer_name'];
            $customer_email = $_POST['customer_email'];
            
            // Get product details
            $product_sql = "SELECT * FROM assets_tb WHERE p_id = ?";
            $product_stmt = $conn->prepare($product_sql);
            $product_stmt->bind_param("i", $p_id);
            $product_stmt->execute();
            $product = $product_stmt->get_result()->fetch_assoc();
            
            if($product && $quantity <= $product['p_quantity']) {
                // Calculate total price
                $total_price = $product['p_price'] * $quantity;
                
                // Insert into sales table
                $insert_sql = "INSERT INTO sales_tb (p_id, quantity, total_price, customer_name, customer_email, sale_date) 
                              VALUES (?, ?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iidss", $p_id, $quantity, $total_price, $customer_name, $customer_email);
                
                if($insert_stmt->execute()) {
                    // Update product quantity
                    $update_sql = "UPDATE assets_tb SET p_quantity = p_quantity - ? WHERE p_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ii", $quantity, $p_id);
                    $update_stmt->execute();
                    
                    echo '<div class="alert alert-success">Product sold successfully!</div>';
                    echo '<meta http-equiv="refresh" content="2;URL=?sold" />';
                } else {
                    echo '<div class="alert alert-danger">Error processing sale. Please try again.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Invalid quantity or product not found.</div>';
            }
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
    
    .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }
    
    .modal-header {
        border-bottom: 1px solid rgba(0,0,0,0.1);
        background-color: white;
    }
    
    .modal-title {
        color: #333;
        font-weight: 600;
    }
    
    .form-label {
        font-weight: 500;
        color: #555;
    }
    
    .form-control {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(243, 150, 28, 0.25);
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