<?php
define('TITLE', 'Requester Profile');
define('PAGE', 'RequesterProfile');
include('includes/header.php'); 
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];
$msg = '';

if(isset($_REQUEST['nameupdate'])) {
    $rName = filter_input(INPUT_POST, 'rName', FILTER_SANITIZE_STRING);
    $sql = "UPDATE requesterlogin_tb SET r_name = ? WHERE r_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $rName, $rEmail);
    
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success" role="alert">Profile Updated Successfully</div>';
        $_SESSION['rName'] = $rName;
    } else {
        $msg = '<div class="alert alert-danger" role="alert">Unable to Update Profile</div>';
    }
    $stmt->close();
}

// Fetch assigned work
$sql = "SELECT a.*, t.empName, t.empMobile, s.request_info, s.request_desc, s.requester_name, s.requester_add1, s.requester_city, s.requester_state, s.requester_zip, s.requester_mobile
        FROM assignwork_tb a 
        JOIN submitrequest_tb s ON a.request_id = s.request_id 
        JOIN technician_tb t ON a.assign_tech = t.empid 
        WHERE s.requester_email = ? 
        ORDER BY a.assign_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rEmail);
$stmt->execute();
$assigned_work = $stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <p class="text-muted">Welcome, <?php echo $_SESSION['rName']; ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total Requests</h6>
                                <h1 class="display-4">
                                    <?php
                                    $sql = "SELECT COUNT(*) FROM submitrequest_tb WHERE requester_email = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $rEmail);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = mysqli_fetch_row($result);
                                    echo $row[0];
                                    ?>
                                </h1>
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
                                <h1 class="display-4">
                                    <?php
                                    $sql = "SELECT COUNT(*) FROM assignwork_tb a 
                                            JOIN submitrequest_tb s ON a.request_id = s.request_id 
                                            WHERE s.requester_email = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $rEmail);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = mysqli_fetch_row($result);
                                    echo $row[0];
                                    ?>
                                </h1>
                            </div>
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="#assignedWorkSection">View Details</a>
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
                <div class="card shadow-sm" id="assignedWorkSection">
                    <div class="card-header">
                        <h5 class="mb-0">Assigned Work</h5>
                    </div>
                    <div class="card-body">
                        <?php if($assigned_work->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Service Type</th>
                                            <th>Description</th>
                                            <th>Assigned Technician</th>
                                            <th>Assignment Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $assigned_work->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                <td><?php echo htmlspecialchars($row['request_info']); ?></td>
                                                <td><?php echo htmlspecialchars($row['request_desc']); ?></td>
                                                <td><?php echo htmlspecialchars($row['empName']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($row['assign_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-success">Assigned</span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#workDetailsModal<?php echo $row['request_id']; ?>">
                                                        View Details
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Work Details Modal -->
                            <?php 
                            $assigned_work->data_seek(0); // Reset the result pointer
                            while($row = $assigned_work->fetch_assoc()): 
                            ?>
                            <div class="modal fade" id="workDetailsModal<?php echo $row['request_id']; ?>" tabindex="-1" aria-labelledby="workDetailsModalLabel<?php echo $row['request_id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="workDetailsModalLabel<?php echo $row['request_id']; ?>">
                                                Work Details - Request #<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Request Information</h6>
                                                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($row['request_info']); ?></p>
                                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($row['request_desc']); ?></p>
                                                    <p><strong>Request Date:</strong> <?php echo date('d M Y', strtotime($row['assign_date'])); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Technician Information</h6>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['empName']); ?></p>
                                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($row['empMobile']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <h6>Your Information</h6>
                                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['requester_name']); ?></p>
                                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($row['requester_add1'] . ', ' . $row['requester_city'] . ', ' . $row['requester_state'] . ' - ' . $row['requester_zip']); ?></p>
                                                    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($row['requester_mobile']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <div class="alert alert-info">
                                No work has been assigned to you yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($msg)) { echo $msg; } ?>
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $_SESSION['rName']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $rEmail; ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" 
                                           value="<?php echo isset($row['r_mobile']) ? htmlspecialchars($row['r_mobile']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter new password (leave blank to keep current)">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="nameupdate" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php'); 
$conn->close();
?>