<?php
session_start();
define('TITLE', 'Requesters');
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

// Handle delete request
if(isset($_REQUEST['delete'])) {
    $r_login_id = $_REQUEST['delete'];
    $sql = "DELETE FROM requesterlogin_tb WHERE r_login_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $r_login_id);
    
    if($stmt->execute()) {
        echo '<div class="alert alert-success">Requester deleted successfully!</div>';
        echo '<meta http-equiv="refresh" content="2;URL=requester.php" />';
    } else {
        echo '<div class="alert alert-danger">Unable to delete requester.</div>';
    }
    $stmt->close();
}

// Get all requesters
$sql = "SELECT * FROM requesterlogin_tb ORDER BY r_login_id DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Requesters</h2>
                        <p class="text-muted">Manage and track your requesters</p>
                    </div>
                    <a href="addrequester.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Requester
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <?php if($mobile_column_exists): ?>
                                <th>Mobile</th>
                                <?php endif; ?>
                                <?php if($status_column_exists): ?>
                                <th>Status</th>
                                <?php endif; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    if($status_column_exists) {
                                        $statusClass = $row['r_status'] == 'Active' ? 'success' : 'danger';
                                        $statusIcon = $row['r_status'] == 'Active' ? 'check-circle' : 'times-circle';
                                    }
                            ?>
                            <tr>
                                <td>#<?php echo $row['r_login_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['r_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['r_email']); ?></td>
                                <?php if($mobile_column_exists): ?>
                                <td><?php echo htmlspecialchars($row['r_mobile'] ?? ''); ?></td>
                                <?php endif; ?>
                                <?php if($status_column_exists): ?>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo $row['r_status']; ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="editrequester.php?r_login_id=<?php echo $row['r_login_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['r_login_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this requester?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="' . ($mobile_column_exists && $status_column_exists ? '6' : '5') . '" class="text-center">No requesters found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group {
        gap: 4px;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
    .badge.bg-success {
        background-color: #28a745 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
</style>

<?php include('includes/footer.php'); ?>