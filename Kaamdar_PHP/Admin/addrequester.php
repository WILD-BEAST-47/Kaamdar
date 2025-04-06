<?php
session_start();
define('TITLE', 'Add Requester');
define('PAGE', 'requester');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if r_mobile and r_status columns exist
$check_mobile_column = $conn->query("SHOW COLUMNS FROM requesterlogin_tb LIKE 'r_mobile'");
$mobile_column_exists = $check_mobile_column->num_rows > 0;

$check_status_column = $conn->query("SHOW COLUMNS FROM requesterlogin_tb LIKE 'r_status'");
$status_column_exists = $check_status_column->num_rows > 0;

// Add missing columns if they don't exist
if(!$mobile_column_exists) {
    $sql = "ALTER TABLE requesterlogin_tb ADD COLUMN r_mobile VARCHAR(20) DEFAULT NULL";
    $conn->query($sql);
    $mobile_column_exists = true;
}

if(!$status_column_exists) {
    $sql = "ALTER TABLE requesterlogin_tb ADD COLUMN r_status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active'";
    $conn->query($sql);
    $status_column_exists = true;
}

// Handle form submission
if(isset($_POST['add'])) {
    $r_name = $_POST['r_name'];
    $r_email = $_POST['r_email'];
    $r_password = $_POST['r_password'];
    $r_mobile = $_POST['r_mobile'] ?? null;
    $r_status = $_POST['r_status'] ?? 'Active';
    
    // Check if email already exists
    $check_sql = "SELECT r_email FROM requesterlogin_tb WHERE r_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $r_email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        echo '<div class="alert alert-danger">Email already exists!</div>';
    } else {
        // Build the SQL query based on available columns
        $sql = "INSERT INTO requesterlogin_tb (r_name, r_email, r_password";
        $values = "VALUES (?, ?, ?";
        $types = "sss";
        $params = array($r_name, $r_email, $r_password);
        
        if($mobile_column_exists) {
            $sql .= ", r_mobile";
            $values .= ", ?";
            $types .= "s";
            $params[] = $r_mobile;
        }
        
        if($status_column_exists) {
            $sql .= ", r_status";
            $values .= ", ?";
            $types .= "s";
            $params[] = $r_status;
        }
        
        $sql .= ") " . $values . ")";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if($stmt->execute()) {
            echo '<div class="alert alert-success">Requester added successfully!</div>';
            echo '<meta http-equiv="refresh" content="2;URL=requester.php" />';
        } else {
            echo '<div class="alert alert-danger">Unable to add requester.</div>';
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Add Requester</h2>
                        <p class="text-muted">Create a new requester account</p>
                    </div>
                    <a href="requester.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="r_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="r_name" name="r_name" required>
                            <div class="invalid-feedback">
                                Please enter the requester's name.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="r_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="r_email" name="r_email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="r_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="r_password" name="r_password" required>
                            <div class="invalid-feedback">
                                Please enter a password.
                            </div>
                        </div>
                        
                        <?php if($mobile_column_exists): ?>
                        <div class="col-md-6">
                            <label for="r_mobile" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="r_mobile" name="r_mobile">
                        </div>
                        <?php endif; ?>
                        
                        <?php if($status_column_exists): ?>
                        <div class="col-md-6">
                            <label for="r_status" class="form-label">Status</label>
                            <select class="form-select" id="r_status" name="r_status" required>
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" name="add">
                            <i class="fas fa-plus me-2"></i>Add Requester
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
    
    .form-label {
        font-weight: 500;
        color: #555;
    }
    
    .form-control:focus, .form-select:focus {
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
    
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }
</style>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()
</script>

<?php include('includes/footer.php'); ?> 