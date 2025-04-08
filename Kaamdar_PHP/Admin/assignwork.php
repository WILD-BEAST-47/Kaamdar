<?php
session_start();
define('TITLE', 'Assign Work');
define('PAGE', 'work');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$msg = '';

// Handle work assignment
if(isset($_POST['assign'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $technician_id = filter_input(INPUT_POST, 'technician_id', FILTER_SANITIZE_NUMBER_INT);
    $assigned_date = filter_input(INPUT_POST, 'assigned_date', FILTER_SANITIZE_STRING);

    // Validate inputs
    if(empty($request_id) || empty($technician_id) || empty($assigned_date)) {
        $msg = '<div class="alert alert-warning">All fields are required</div>';
    } else {
        // Get request details
        $req_sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
        $req_stmt = $conn->prepare($req_sql);
        $req_stmt->bind_param("i", $request_id);
        $req_stmt->execute();
        $req_result = $req_stmt->get_result();
        
        if($req_result->num_rows > 0) {
            $request = $req_result->fetch_assoc();
            
            // Get technician details
            $tech_sql = "SELECT * FROM technician_tb WHERE empid = ?";
            $tech_stmt = $conn->prepare($tech_sql);
            $tech_stmt->bind_param("i", $technician_id);
            $tech_stmt->execute();
            $tech_result = $tech_stmt->get_result();
            
            if($tech_result->num_rows > 0) {
                $technician = $tech_result->fetch_assoc();
                
                // Insert into assignwork_tb
                $insert_sql = "INSERT INTO assignwork_tb (request_id, request_info, request_desc, requester_name, 
                            requester_add1, requester_add2, requester_city, requester_state, requester_zip, 
                            requester_email, requester_mobile, assign_tech, assign_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("isssssssissss", 
                    $request['request_id'],
                    $request['request_info'],
                    $request['request_desc'],
                    $request['requester_name'],
                    $request['requester_add1'],
                    $request['requester_add2'],
                    $request['requester_city'],
                    $request['requester_state'],
                    $request['requester_zip'],
                    $request['requester_email'],
                    $request['requester_mobile'],
                    $technician['empName'],
                    $assigned_date
                );
                
                if($insert_stmt->execute()) {
                    // Delete from submitrequest_tb
                    $delete_sql = "DELETE FROM submitrequest_tb WHERE request_id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("i", $request_id);
                    $delete_stmt->execute();
                    
                    $msg = '<div class="alert alert-success">Work assigned successfully!</div>';
                    echo "<script>setTimeout(function(){ location.href='work.php'; }, 2000);</script>";
                } else {
                    $msg = '<div class="alert alert-danger">Unable to assign work</div>';
                }
            } else {
                $msg = '<div class="alert alert-warning">Technician not found</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Request not found</div>';
        }
    }
}

// Get request details if ID is provided
$request_details = null;
if(isset($_GET['id'])) {
    $request_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $request_details = $result->fetch_assoc();
    } else {
        $msg = '<div class="alert alert-warning">Request not found!</div>';
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Assign Work</h2>
                        <p class="text-muted">Assign work to technicians</p>
                    </div>
                    <a href="work.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Work Orders
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

        <?php if($request_details): ?>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="request_id" value="<?php echo $request_details['request_id']; ?>">
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Request Information</h6>
                                    <p><strong>Request ID:</strong> #<?php echo str_pad($request_details['request_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($request_details['request_info']); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($request_details['request_desc']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Requester Information</h6>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($request_details['requester_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($request_details['requester_email']); ?></p>
                                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($request_details['requester_mobile']); ?></p>
                                    <p><strong>City:</strong> <?php echo htmlspecialchars($request_details['requester_city']); ?></p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Technician</label>
                                <select name="technician_id" class="form-select" required>
                                    <option value="">Select a technician...</option>
                                    <?php
                                    $sql = "SELECT * FROM technician_tb WHERE empStatus = 'Active' ORDER BY empName ASC";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['empid'] . "'>" . 
                                             htmlspecialchars($row['empName']) . " - " . 
                                             htmlspecialchars($row['empCity']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Assigned Date</label>
                                <input type="date" class="form-control" name="assigned_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <button type="submit" name="assign" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Assign Work
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No request selected. Please select a request to assign work.
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus, .form-select:focus {
    border-color: #f3961c;
    box-shadow: 0 0 0 0.25rem rgba(243, 150, 28, 0.25);
}

.btn-primary {
    background-color: #f3961c;
    border-color: #f3961c;
}

.btn-primary:hover {
    background-color: #e08a19;
    border-color: #e08a19;
}

.alert {
    border-radius: 8px;
    border: none;
}
</style>

<?php include('includes/footer.php'); ?> 