<?php
session_start();
define('TITLE', 'Work Report');
define('PAGE', 'workreport');
include('includes/header.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get work reports with requester and technician details
$sql = "SELECT a.*, r.r_name as requester_name, t.empName as technician_name 
        FROM assignwork_tb a 
        LEFT JOIN submitrequest_tb sr ON a.request_id = sr.request_id 
        LEFT JOIN requesterlogin_tb r ON sr.requester_email = r.r_email 
        LEFT JOIN technician_tb t ON a.assign_tech = t.empid 
        ORDER BY a.assign_date DESC, a.rno DESC";
$result = $conn->query($sql);

// Handle report deletion
if(isset($_POST['delete'])) {
    $rno = filter_input(INPUT_POST, 'rno', FILTER_SANITIZE_NUMBER_INT);
    $delete_sql = "DELETE FROM assignwork_tb WHERE rno = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $rno);
    if($stmt->execute()) {
        echo "<script>alert('Report deleted successfully');</script>";
        echo "<script> location.href='workreport.php'; </script>";
    } else {
        echo "<script>alert('Error deleting report');</script>";
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Work Reports</h2>
                        <p class="text-muted">View and manage work reports</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Request ID</th>
                                        <th>Requester</th>
                                        <th>Technician</th>
                                        <th>Assign Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>
                                                <td>#".str_pad($row['rno'], 6, '0', STR_PAD_LEFT)."</td>
                                                <td>#".str_pad($row['request_id'], 6, '0', STR_PAD_LEFT)."</td>
                                                <td>".htmlspecialchars($row['requester_name'])."</td>
                                                <td>".htmlspecialchars($row['technician_name'])."</td>
                                                <td>".date('d M Y', strtotime($row['assign_date']))."</td>
                                                <td><span class='badge bg-success'>Assigned</span></td>
                                                <td>
                                                    <form method='POST' class='d-inline'>
                                                        <input type='hidden' name='rno' value='".$row['rno']."'>
                                                        <button type='submit' name='delete' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this report?\")'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No reports found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}
</style>

<?php include('includes/footer.php'); ?>