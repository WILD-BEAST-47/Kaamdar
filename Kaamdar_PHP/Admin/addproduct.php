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
    // Sanitize and validate input
    $pname = filter_input(INPUT_POST, 'pname', FILTER_SANITIZE_STRING);
    $pdop = filter_input(INPUT_POST, 'pdop', FILTER_SANITIZE_STRING);
    $pava = filter_input(INPUT_POST, 'pava', FILTER_SANITIZE_NUMBER_INT);
    $ptotal = filter_input(INPUT_POST, 'ptotal', FILTER_SANITIZE_NUMBER_INT);
    $poriginalcost = filter_input(INPUT_POST, 'poriginalcost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $psellingcost = filter_input(INPUT_POST, 'psellingcost', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Validate required fields
    if(empty($pname) || empty($pdop) || empty($pava) || empty($ptotal) || 
       empty($poriginalcost) || empty($psellingcost)) {
        $msg = '<div class="alert alert-warning">All fields are required</div>';
    } elseif($pava > $ptotal) {
        $msg = '<div class="alert alert-warning">Available quantity cannot be greater than total quantity</div>';
    } elseif($psellingcost <= $poriginalcost) {
        $msg = '<div class="alert alert-warning">Selling cost must be greater than original cost</div>';
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "INSERT INTO assets_tb (pname, pdop, pava, ptotal, poriginalcost, psellingcost, category_id, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiddis", $pname, $pdop, $pava, $ptotal, $poriginalcost, $psellingcost, $category_id, $description);
        
        if($stmt->execute()) {
            $msg = '<div class="alert alert-success">Product Added Successfully</div>';
        } else {
            $msg = '<div class="alert alert-danger">Unable to Add Product</div>';
        }
        $stmt->close();
    }
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
                        <p class="text-muted">Fill in the details to add a new product</p>
                    </div>
                    <a href="assets.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <?php echo $msg; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="pname" name="pname" 
                                               placeholder="Product Name" required
                                               value="<?php echo isset($_POST['pname']) ? htmlspecialchars($_POST['pname']) : ''; ?>">
                                        <label for="pname">Product Name</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            <?php 
                                            if($categories_result && $categories_result->num_rows > 0):
                                                while($category = $categories_result->fetch_assoc()): 
                                            ?>
                                            <option value="<?php echo $category['category_id']; ?>"
                                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                            <?php 
                                                endwhile;
                                            endif; 
                                            ?>
                                        </select>
                                        <label for="category_id">Category</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="date" class="form-control" id="pdop" name="pdop" required
                                               value="<?php echo isset($_POST['pdop']) ? htmlspecialchars($_POST['pdop']) : ''; ?>">
                                        <label for="pdop">Date of Purchase</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="pava" name="pava" 
                                               placeholder="Available" required min="0"
                                               value="<?php echo isset($_POST['pava']) ? htmlspecialchars($_POST['pava']) : ''; ?>">
                                        <label for="pava">Available Quantity</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" class="form-control" id="ptotal" name="ptotal" 
                                               placeholder="Total" required min="0"
                                               value="<?php echo isset($_POST['ptotal']) ? htmlspecialchars($_POST['ptotal']) : ''; ?>">
                                        <label for="ptotal">Total Quantity</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" step="0.01" class="form-control" id="poriginalcost" 
                                               name="poriginalcost" placeholder="Original Cost" required min="0"
                                               value="<?php echo isset($_POST['poriginalcost']) ? htmlspecialchars($_POST['poriginalcost']) : ''; ?>">
                                        <label for="poriginalcost">Original Cost (₹)</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="number" step="0.01" class="form-control" id="psellingcost" 
                                               name="psellingcost" placeholder="Selling Cost" required min="0"
                                               value="<?php echo isset($_POST['psellingcost']) ? htmlspecialchars($_POST['psellingcost']) : ''; ?>">
                                        <label for="psellingcost">Selling Cost (₹)</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-floating mb-3">
                                        <textarea class="form-control" id="description" name="description" 
                                                  placeholder="Description" style="height: 100px"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        <label for="description">Description</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100" name="psubmit">
                                        <i class="fas fa-plus me-2"></i>Add Product
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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