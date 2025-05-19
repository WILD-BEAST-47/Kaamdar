<?php
session_start();
define('TITLE', 'Edit Technician');
define('PAGE', 'technician');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if empPhoto column exists
$check_column = $conn->query("SHOW COLUMNS FROM technician_tb LIKE 'empPhoto'");
$photo_column_exists = $check_column->num_rows > 0;

// Initialize variables
$row = null;
$error = null;

// Fetch technician details if empid is provided
if(isset($_REQUEST['empid'])) {
    $empid = $_REQUEST['empid'];
    $sql = "SELECT * FROM technician_tb WHERE empid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $empid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $error = "Technician not found";
    }
    $stmt->close();
} else {
    $error = "No technician ID provided";
}

// Handle form submission
if(isset($_REQUEST['update']) && $row) {
    $empid = $_REQUEST['empid'];
    $empName = $_REQUEST['empName'];
    $empCity = $_REQUEST['empCity'];
    $empMobile = $_REQUEST['empMobile'];
    $empEmail = $_REQUEST['empEmail'];
    $empStatus = $_REQUEST['empStatus'];

    // Handle photo upload if column exists
    $empPhoto = $row['empPhoto'] ?? null;
    if($photo_column_exists && isset($_FILES['empPhoto']) && $_FILES['empPhoto']['error'] == 0) {
        $target_dir = "../uploads/technicians/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["empPhoto"]["name"], PATHINFO_EXTENSION));
        $new_filename = "tech_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["empPhoto"]["tmp_name"]);
        if($check !== false) {
            if(move_uploaded_file($_FILES["empPhoto"]["tmp_name"], $target_file)) {
                $empPhoto = $new_filename;
            }
        }
    }

    // Build SQL query based on whether photo column exists
    if($photo_column_exists) {
        $sql = "UPDATE technician_tb SET empName=?, empCity=?, empMobile=?, empEmail=?, empStatus=?, empPhoto=? WHERE empid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $empName, $empCity, $empMobile, $empEmail, $empStatus, $empPhoto, $empid);
    } else {
        $sql = "UPDATE technician_tb SET empName=?, empCity=?, empMobile=?, empEmail=?, empStatus=? WHERE empid=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $empName, $empCity, $empMobile, $empEmail, $empStatus, $empid);
    }
    
    if($stmt->execute()) {
        echo '<div class="alert alert-success">Technician updated successfully!</div>';
        echo '<meta http-equiv="refresh" content="2;URL=technician.php" />';
    } else {
        echo '<div class="alert alert-danger">Unable to update technician.</div>';
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
                        <h2 class="mb-0">Edit Technician</h2>
                        <p class="text-muted">Update technician details</p>
                    </div>
                    <a href="technician.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <div class="text-center mt-4">
                <a href="technician.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Return to Technicians
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="empid" value="<?php echo $row['empid']; ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="empName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="empName" name="empName" 
                                           value="<?php echo htmlspecialchars($row['empName']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="empEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="empEmail" name="empEmail" 
                                           value="<?php echo htmlspecialchars($row['empEmail']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="empMobile" class="form-label">Mobile</label>
                                    <input type="tel" class="form-control" id="empMobile" name="empMobile" 
                                           value="<?php echo htmlspecialchars($row['empMobile']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="empCity" class="form-label">City</label>
                                    <input type="text" class="form-control" id="empCity" name="empCity" 
                                           value="<?php echo htmlspecialchars($row['empCity']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="empStatus" class="form-label">Status</label>
                                    <select class="form-select" id="empStatus" name="empStatus" required>
                                        <option value="Active" <?php echo ($row['empStatus'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo ($row['empStatus'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                
                                <?php if($photo_column_exists): ?>
                                <div class="mb-3">
                                    <label for="empPhoto" class="form-label">Photo</label>
                                    <?php if(!empty($row['empPhoto'])): ?>
                                        <div class="mb-2">
                                            <img src="../uploads/technicians/<?php echo htmlspecialchars($row['empPhoto']); ?>" 
                                                 alt="Technician Photo" class="img-thumbnail" style="max-width: 150px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="empPhoto" name="empPhoto" accept="image/*">
                                    <small class="text-muted">Upload a new photo to replace the existing one</small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" name="update">
                                <i class="fas fa-save me-2"></i>Update Technician
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
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
    
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 0.75rem 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(243, 150, 28, 0.25);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #e08a19;
        border-color: #e08a19;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }
    
    .img-thumbnail {
        border-radius: 6px;
        border: 1px solid #ddd;
        padding: 4px;
    }
</style>

<?php include('includes/footer.php'); ?> 