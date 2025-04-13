<?php
define('TITLE', 'Add New Product');
define('PAGE', 'assets');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$msg = '';

if(isset($_REQUEST['psubmit'])) {
    // Validate input
    $pname = trim($_REQUEST['pname']);
    $pdop = trim($_REQUEST['pdop']);
    $pava = trim($_REQUEST['pava']);
    $ptotal = trim($_REQUEST['ptotal']);
    $poriginalcost = trim($_REQUEST['poriginalcost']);
    $psellingcost = trim($_REQUEST['psellingcost']);
    $description = trim($_REQUEST['description']);

    // Handle image upload
    $image_url = null;
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $upload_dir = '../assets/images/products/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                $image_url = 'assets/images/products/' . $new_filename;
            } else {
                $msg = '<div class="alert alert-warning">Failed to upload image</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Invalid file type. Allowed types: ' . implode(', ', $allowed) . '</div>';
        }
    }

    // Insert into database
    $sql = "INSERT INTO assets_tb (pname, pdop, pava, ptotal, poriginalcost, psellingcost, description, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiddss", $pname, $pdop, $pava, $ptotal, $poriginalcost, $psellingcost, $description, $image_url);
    
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success">Product Added Successfully</div>';
    } else {
        $msg = '<div class="alert alert-danger">Unable to Add Product</div>';
    }
    $stmt->close();
}

// Get categories for dropdown
$categories_sql = "SELECT * FROM product_categories_tb ORDER BY category_name";
$categories_result = $conn->query($categories_sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Add New Product</h2>
                        <p class="text-muted">Add a new product to your inventory</p>
                    </div>
                    <a href="assets.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>

        <?php if(isset($msg)) echo $msg; ?>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pname" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="pname" name="pname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php while($row = $categories_result->fetch_assoc()): ?>
                                    <option value="<?php echo $row['category_id']; ?>">
                                        <?php echo htmlspecialchars($row['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pdop" class="form-label">Date of Purchase</label>
                            <input type="date" class="form-control" id="pdop" name="pdop" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="product_image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pava" class="form-label">Available Quantity</label>
                            <input type="number" class="form-control" id="pava" name="pava" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ptotal" class="form-label">Total Quantity</label>
                            <input type="number" class="form-control" id="ptotal" name="ptotal" required min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="poriginalcost" class="form-label">Original Cost</label>
                            <input type="number" class="form-control" id="poriginalcost" name="poriginalcost" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="psellingcost" class="form-label">Selling Cost</label>
                            <input type="number" class="form-control" id="psellingcost" name="psellingcost" required min="0" step="0.01">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" name="psubmit">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
    
    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }
    
    .form-floating > label {
        padding: 1rem 0.75rem;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(243, 150, 28, 0.25);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #e08a1a;
        border-color: #e08a1a;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
    
    .alert {
        border-radius: 8px;
        border: none;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<?php include('includes/footer.php'); ?>