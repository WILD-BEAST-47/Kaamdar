<?php
session_start();
define('TITLE', 'Work Assignment');
define('PAGE', 'work');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Handle work assignment
if(isset($_POST['assign'])) {
    $request_id = $_POST['request_id'];
    $technician_id = $_POST['technician_id'];
    $work_priority = $_POST['work_priority'];
    $work_assign_date = $_POST['work_assign_date'];
    
    $sql = "INSERT INTO assignwork_tb (request_id, technician_id, work_priority, work_assign_date) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $request_id, $technician_id, $work_priority, $work_assign_date);
    
    if($stmt->execute()) {
        // Update request status
        $update_sql = "UPDATE submitrequest_tb SET request_status = 'Assigned' WHERE request_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();
        
        $success_msg = '<div class="alert alert-success">Work assigned successfully!</div>';
    } else {
        $error_msg = '<div class="alert alert-danger">Error assigning work. Please try again.</div>';
    }
}

// Get all work requests
$sql = "SELECT r.*, 
               r.requester_name as cust_name, 
               r.requester_email as cust_email, 
               r.requester_mobile as cust_mobile, 
               CONCAT(r.requester_add1, ' ', r.requester_add2, ', ', r.requester_city, ', ', r.requester_state, ' - ', r.requester_zip) as cust_address
        FROM submitrequest_tb r
        ORDER BY r.request_date DESC";
$result = $conn->query($sql);

// Get all technicians
$tech_sql = "SELECT * FROM technician_tb";
$tech_result = $conn->query($tech_sql);
?>

<style>
.modal {
    overflow: hidden;
}
.modal-dialog {
    margin: 0 auto;
    max-width: 500px;
}
.modal-content {
    border-radius: 0;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
}
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 20px;
}
.modal-body {
    padding: 20px;
}
.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 15px 20px;
}
.btn-close {
    margin: 0;
    padding: 0.5rem;
}
</style>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Work Assignment</h2>
                <p class="text-muted">Assign work to technicians and manage requests</p>
            </div>
        </div>

        <?php 
        if(isset($success_msg)) echo $success_msg;
        if(isset($error_msg)) echo $error_msg;
        ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Customer</th>
                                        <th>Request Info</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['request_id']; ?></td>
                                        <td>
                                            <strong><?php echo $row['cust_name']; ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo $row['cust_email']; ?><br>
                                                <?php echo $row['cust_mobile']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong>Description:</strong> <?php echo $row['request_desc']; ?><br>
                                            <strong>Address:</strong> <?php echo $row['cust_address']; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-warning">Pending</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#assignModal<?php echo $row['request_id']; ?>">
                                                Assign Work
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Assign Work Modal -->
                                    <div class="modal fade" id="assignModal<?php echo $row['request_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Assign Work</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Technician</label>
                                                            <select class="form-select" name="technician_id" required>
                                                                <option value="">Choose technician...</option>
                                                                <?php while($tech = $tech_result->fetch_assoc()): ?>
                                                                <option value="<?php echo $tech['tech_id']; ?>">
                                                                    <?php echo $tech['tech_name']; ?> 
                                                                    (<?php echo $tech['tech_city']; ?>)
                                                                </option>
                                                                <?php endwhile; ?>
                                                                <?php $tech_result->data_seek(0); ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Work Priority</label>
                                                            <select class="form-select" name="work_priority" required>
                                                                <option value="High">High</option>
                                                                <option value="Medium">Medium</option>
                                                                <option value="Low">Low</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Assign Date</label>
                                                            <input type="date" class="form-control" name="work_assign_date" 
                                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="assign" class="btn btn-primary">Assign Work</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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