<?php
session_start();
define('TITLE', 'Requester Dashboard');
define('PAGE', 'dashboard');
include('includes/header.php');
include('../dbConnection.php');

// Check if requester is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];

// Get requester's details
$sql = "SELECT r_name, r_email, r_mobile FROM requesterlogin_tb WHERE r_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rEmail);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Get total requests
$request_sql = "SELECT COUNT(*) as total_requests FROM submitrequest_tb WHERE requester_email = ?";
$request_stmt = $conn->prepare($request_sql);
$request_stmt->bind_param("s", $rEmail);
$request_stmt->execute();
$request_result = $request_stmt->get_result();
$request_row = $request_result->fetch_assoc();

// Get assigned work
$assign_sql = "SELECT COUNT(*) as assigned_work FROM assignwork_tb WHERE requester_email = ?";
$assign_stmt = $conn->prepare($assign_sql);
$assign_stmt->bind_param("s", $rEmail);
$assign_stmt->execute();
$assign_result = $assign_stmt->get_result();
$assign_row = $assign_result->fetch_assoc();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <p class="text-muted">Welcome, <?php echo $row['r_name']; ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total Requests</h6>
                                <h1 class="display-4"><?php echo $request_row['total_requests']; ?></h1>
                            </div>
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="ServiceStatus.php">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Assigned Work</h6>
                                <h1 class="display-4"><?php echo $assign_row['assigned_work']; ?></h1>
                            </div>
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="ViewAssignedWork.php">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">New Request</h6>
                                <h1 class="display-4">+</h1>
                            </div>
                            <i class="fas fa-plus-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="SubmitRequest.php">Submit Request</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Requests</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Service Type</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $recent_sql = "SELECT r.*, 
                                                 CASE WHEN a.request_id IS NOT NULL THEN 'Assigned' ELSE 'Pending' END as request_status
                                          FROM submitrequest_tb r
                                          LEFT JOIN assignwork_tb a ON r.request_id = a.request_id
                                          WHERE r.requester_email = ?
                                          ORDER BY r.request_date DESC
                                          LIMIT 5";
                                    $recent_stmt = $conn->prepare($recent_sql);
                                    $recent_stmt->bind_param("s", $rEmail);
                                    $recent_stmt->execute();
                                    $recent_result = $recent_stmt->get_result();
                                    
                                    while($recent_row = $recent_result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td>#<?php echo $recent_row['request_id']; ?></td>
                                        <td><?php echo $recent_row['request_info']; ?></td>
                                        <td><?php echo substr($recent_row['request_desc'], 0, 50) . '...'; ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($recent_row['request_date'])); ?></td>
                                        <td>
                                            <?php if($recent_row['request_status'] == 'Assigned'): ?>
                                            <span class="badge bg-success">Assigned</span>
                                            <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="ServiceStatus.php?id=<?php echo $recent_row['request_id']; ?>" 
                                               class="btn btn-info btn-sm">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?> 