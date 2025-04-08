<?php
define('TITLE', 'Work Report');
define('PAGE', 'workreport');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get work reports
$sql = "SELECT wr.*, r.r_name, r.r_email, r.r_mobile 
        FROM workreport_tb wr 
        JOIN requesterlogin_tb r ON wr.requester_id = r.r_login_id 
        ORDER BY wr.created_at DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Work Reports</h2>
                        <p class="text-muted">View all work reports</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Requester</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Work Type</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row['report_id']; ?></td>
                                <td><?php echo $row['r_name']; ?></td>
                                <td><?php echo $row['r_email']; ?></td>
                                <td><?php echo $row['r_mobile']; ?></td>
                                <td><?php echo $row['work_type']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $row['status'] == 'Pending' ? 'warning' : 
                                            ($row['status'] == 'Completed' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="viewReport(<?php echo $row['report_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($row['status'] == 'Pending'): ?>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            onclick="updateStatus(<?php echo $row['report_id']; ?>, 'Completed')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="updateStatus(<?php echo $row['report_id']; ?>, 'Rejected')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="9" class="text-center">No work reports found</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewReport(reportId) {
    window.location.href = 'view-report.php?id=' + reportId;
}

function updateStatus(reportId, status) {
    if(confirm('Are you sure you want to update the status to ' + status + '?')) {
        $.ajax({
            url: 'update-report-status.php',
            type: 'POST',
            data: {
                report_id: reportId,
                status: status
            },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + response.message);
                }
            },
            error: function() {
                alert('Error updating status. Please try again.');
            }
        });
    }
}
</script>

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
        background-color: var(--light-bg);
        border-bottom: 2px solid #dee2e6;
    }
    
    .badge {
        padding: 0.5em 0.75em;
        border-radius: 4px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-primary:hover {
        background-color: #e08a1a;
        border-color: #e08a1a;
    }
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>

<?php include('includes/footer.php'); ?>