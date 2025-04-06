<?php
session_start();
define('TITLE', 'Edit Requester');
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

// Get requester details
if(isset($_REQUEST['r_login_id'])) {
    $r_login_id = $_REQUEST['r_login_id'];
    $sql = "SELECT * FROM requesterlogin_tb WHERE r_login_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $r_login_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submission
if(isset($_POST['update'])) {
    $r_login_id = $_POST['r_login_id'];
    $r_name = $_POST['r_name'];
    $r_email = $_POST['r_email'];
    $r_mobile = $_POST['r_mobile'] ?? null;
    $r_status = $_POST['r_status'] ?? 'Active';
    
    // Build the SQL query based on available columns
    $sql = "UPDATE requesterlogin_tb SET r_name = ?, r_email = ?";
    $types = "ss";
    $params = array($r_name, $r_email);
    
    if($mobile_column_exists) {
        $sql .= ", r_mobile = ?";
        $types .= "s";
        $params[] = $r_mobile;
    }
    
    if($status_column_exists) {
        $sql .= ", r_status = ?";
        $types .= "s";
        $params[] = $r_status;
    }
    
    $sql .= " WHERE r_login_id = ?";
    $types .= "i";
    $params[] = $r_login_id;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if($stmt->execute()) {
        echo '<div class="alert alert-success">Requester updated successfully!</div>';
        echo '<meta http-equiv="refresh" content="2;URL=requester.php" />';
    } else {
        echo '<div class="alert alert-danger">Unable to update requester.</div>';
    }
    $stmt->close();
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Edit Requester</h2>
                        <p class="text-muted">Update requester details</p>
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
                    <input type="hidden" name="r_login_id" value="<?php echo $row['r_login_id']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="r_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="r_name" name="r_name" 
                                   value="<?php echo htmlspecialchars($row['r_name']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter the requester's name.
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="r_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="r_email" name="r_email" 
                                   value="<?php echo htmlspecialchars($row['r_email']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        
                        <?php if($mobile_column_exists): ?>
                        <div class="col-md-6">
                            <label for="r_mobile" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="r_mobile" name="r_mobile" 
                                   value="<?php echo htmlspecialchars($row['r_mobile'] ?? ''); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if($status_column_exists): ?>
                        <div class="col-md-6">
                            <label for="r_status" class="form-label">Status</label>
                            <select class="form-select" id="r_status" name="r_status" required>
                                <option value="Active" <?php echo ($row['r_status'] ?? 'Active') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($row['r_status'] ?? 'Active') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" name="update">
                            <i class="fas fa-save me-2"></i>Update Requester
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