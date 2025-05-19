<?php
define('TITLE', 'Sold Product Report');
define('PAGE', 'soldproductreport');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if soldproduct_tb exists, if not create it
$check_table = $conn->query("SHOW TABLES LIKE 'soldproduct_tb'");
if($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE soldproduct_tb (
        sold_id INT(11) NOT NULL AUTO_INCREMENT,
        r_login_id INT(11) NOT NULL,
        emp_id INT(11) NOT NULL,
        product_name VARCHAR(100) NOT NULL,
        quantity INT(11) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        sold_date DATE NOT NULL,
        PRIMARY KEY (sold_id),
        FOREIGN KEY (r_login_id) REFERENCES requesterlogin_tb(r_login_id),
        FOREIGN KEY (emp_id) REFERENCES technician_tb(empid)
    )";
    
    if($conn->query($create_table)) {
        echo '<div class="alert alert-success">Sold product table created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Unable to create sold product table: ' . $conn->error . '</div>';
    }
}

// Handle date filter
$where_clause = "";
if(isset($_POST['searchsubmit'])) {
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    
    if(!empty($startdate) && !empty($enddate)) {
        $where_clause = "WHERE sp.sold_date BETWEEN '$startdate' AND '$enddate'";
    }
}

// Get all sold products with requester and technician details
$sql = "SELECT sp.*, r.r_name as requester_name, t.empName as technician_name 
        FROM soldproduct_tb sp 
        LEFT JOIN requesterlogin_tb r ON sp.r_login_id = r.r_login_id 
        LEFT JOIN technician_tb t ON sp.emp_id = t.empid 
        $where_clause
        ORDER BY sp.sold_id DESC";

$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Sold Products</h2>
                        <p class="text-muted">View and manage sold products</p>
                    </div>
                    <a href="addsoldproduct.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Sold Product
                    </a>
                </div>
            </div>
        </div>

        <!-- Date Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="startdate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startdate" name="startdate" 
                               value="<?php echo isset($_POST['startdate']) ? $_POST['startdate'] : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="enddate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="enddate" name="enddate" 
                               value="<?php echo isset($_POST['enddate']) ? $_POST['enddate'] : ''; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="searchsubmit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="soldproductreport.php" class="btn btn-secondary">
                            <i class="fas fa-sync me-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
                                <th>Technician</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td>#<?php echo $row['sold_id']; ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['requester_name']); ?></span>
                                        <small class="text-muted">ID: <?php echo $row['r_login_id']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['technician_name']); ?></span>
                                        <small class="text-muted">ID: <?php echo $row['emp_id']; ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                <td>Rs. <?php echo number_format($row['quantity'] * $row['price'], 2); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['sold_date'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editsoldproduct.php?sold_id=<?php echo $row['sold_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['sold_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this sold product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="9" class="text-center">No sold products found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
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
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: rgba(243, 150, 28, 0.05);
    }
    
    .btn-group {
        gap: 4px;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .text-muted {
        font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
    }
</style>

<?php include('includes/footer.php'); ?>