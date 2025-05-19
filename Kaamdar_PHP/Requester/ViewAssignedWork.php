<?php
define('TITLE', 'View Assigned Work');
define('PAGE', 'ViewAssignedWork');
include('includes/header.php'); 
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];
$msg = '';

// Get request ID from URL
if(isset($_GET['id'])) {
    $request_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get assigned work details
    $sql = "SELECT a.*, t.empName as technician_name, t.empMobile as technician_mobile, 
            t.empEmail as technician_email, t.empCity as technician_city
            FROM assignwork_tb a 
            JOIN technician_tb t ON a.assign_tech = t.empEmail 
            WHERE a.request_id = ? AND a.requester_email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $request_id, $rEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        ?>
        <div class="col-sm-9 col-md-10">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <div class="card shadow-lg border-0 rounded-lg mt-5">
                        <div class="card-header bg-white">
                            <h3 class="text-center font-weight-light my-2">
                                <i class="fas fa-tasks me-2 text-primary"></i>Assigned Work Details
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="text-primary mb-0">
                                                <i class="fas fa-info-circle me-2"></i>Request Details
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th class="text-muted">Request ID</th>
                                                    <td class="fw-bold">#<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Service Type</th>
                                                    <td class="fw-bold"><?php echo $row['request_info']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Description</th>
                                                    <td class="fw-bold"><?php echo $row['request_desc']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Request Date</th>
                                                    <td class="fw-bold"><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="text-primary mb-0">
                                                <i class="fas fa-user-tie me-2"></i>Assigned Technician
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th class="text-muted">Name</th>
                                                    <td class="fw-bold"><?php echo $row['technician_name']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Mobile</th>
                                                    <td class="fw-bold"><?php echo $row['technician_mobile']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Email</th>
                                                    <td class="fw-bold"><?php echo $row['technician_email']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">City</th>
                                                    <td class="fw-bold"><?php echo $row['technician_city']; ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-light">
                                            <h5 class="text-primary mb-0">
                                                <i class="fas fa-clock me-2"></i>Work Status
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <th class="text-muted">Assigned Date</th>
                                                    <td class="fw-bold"><?php echo date('d M Y', strtotime($row['assign_date'])); ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Status</th>
                                                    <td>
                                                        <?php 
                                                        if($row['work_status'] == 'In Progress') {
                                                            echo '<span class="badge bg-warning p-2"><i class="fas fa-spinner me-1"></i>In Progress</span>';
                                                        } elseif($row['work_status'] == 'Completed') {
                                                            echo '<span class="badge bg-success p-2"><i class="fas fa-check-circle me-1"></i>Completed</span>';
                                                        } else {
                                                            echo '<span class="badge bg-info p-2"><i class="fas fa-clock me-1"></i>Pending</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php if(!empty($row['work_completion_date'])): ?>
                                                <tr>
                                                    <th class="text-muted">Completion Date</th>
                                                    <td class="fw-bold"><?php echo date('d M Y', strtotime($row['work_completion_date'])); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="ServiceStatus.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Status
                                </a>
                                <button type="button" class="btn btn-primary" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Print Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        // Get request details even if not assigned
        $request_sql = "SELECT * FROM submitrequest_tb WHERE request_id = ? AND requester_email = ?";
        $request_stmt = $conn->prepare($request_sql);
        $request_stmt->bind_param("is", $request_id, $rEmail);
        $request_stmt->execute();
        $request_result = $request_stmt->get_result();
        
        if($request_result->num_rows > 0) {
            $request_row = $request_result->fetch_assoc();
            ?>
            <div class="col-sm-9 col-md-10">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow-lg border-0 rounded-lg mt-5">
                            <div class="card-header bg-white">
                                <h3 class="text-center font-weight-light my-2">
                                    <i class="fas fa-tasks me-2 text-primary"></i>Request Details
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>This request is pending assignment to a technician.
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h5 class="text-primary mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>Request Details
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th class="text-muted">Request ID</th>
                                                        <td class="fw-bold">#<?php echo str_pad($request_row['request_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted">Service Type</th>
                                                        <td class="fw-bold"><?php echo $request_row['request_info']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted">Description</th>
                                                        <td class="fw-bold"><?php echo $request_row['request_desc']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted">Request Date</th>
                                                        <td class="fw-bold"><?php echo date('d M Y', strtotime($request_row['request_date'])); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-muted">Status</th>
                                                        <td><span class="badge bg-info p-2"><i class="fas fa-clock me-1"></i>Pending Assignment</span></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="ServiceStatus.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Status
                                    </a>
                                    <button type="button" class="btn btn-primary" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            $msg = '<div class="alert alert-warning" role="alert">No request found with this ID.</div>';
            echo "<script> location.href='ServiceStatus.php'; </script>";
        }
    }
} else {
    echo "<script> location.href='ServiceStatus.php'; </script>";
}

include('includes/footer.php'); 
?> 