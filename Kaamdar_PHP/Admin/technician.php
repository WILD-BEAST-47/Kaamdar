<?php
session_start();
define('TITLE', 'Technicians');
define('PAGE', 'technician');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if empStatus column exists
$check_status_column = $conn->query("SHOW COLUMNS FROM technician_tb LIKE 'empStatus'");
$status_column_exists = $check_status_column->num_rows > 0;

// Add empStatus column if it doesn't exist
if(!$status_column_exists) {
    $sql = "ALTER TABLE technician_tb ADD COLUMN empStatus ENUM('Active','Inactive') NOT NULL DEFAULT 'Active'";
    if($conn->query($sql)) {
        $status_column_exists = true;
        echo '<div class="alert alert-success">Status column added successfully!</div>';
        echo '<meta http-equiv="refresh" content="2;URL=technician.php" />';
    } else {
        echo '<div class="alert alert-danger">Unable to add status column: ' . $conn->error . '</div>';
    }
}

// Check if empPhoto column exists
$check_photo_column = $conn->query("SHOW COLUMNS FROM technician_tb LIKE 'empPhoto'");
$photo_column_exists = $check_photo_column->num_rows > 0;

// Handle delete request
if(isset($_REQUEST['delete'])) {
    $empid = $_REQUEST['delete'];
    $sql = "DELETE FROM technician_tb WHERE empid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $empid);
    
    if($stmt->execute()) {
        echo '<div class="alert alert-success">Technician deleted successfully!</div>';
        echo '<meta http-equiv="refresh" content="2;URL=technician.php" />';
    } else {
        echo '<div class="alert alert-danger">Unable to delete technician.</div>';
    }
    $stmt->close();
}

// Get all technicians
$sql = "SELECT * FROM technician_tb ORDER BY empid DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Technicians</h2>
                        <p class="text-muted">Manage your technicians</p>
                    </div>
                    <a href="addtechnician.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Technician
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
                                <th>Photo</th>
                                <th>Name</th>
                                <th>City</th>
                                <th>Mobile</th>
                                <th>Email</th>
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
                                        $statusClass = $row['empStatus'] == 'Active' ? 'success' : 'danger';
                                        $statusIcon = $row['empStatus'] == 'Active' ? 'check-circle' : 'times-circle';
                                    }
                            ?>
                            <tr>
                                <td><?php echo $row['empid']; ?></td>
                                <td>
                                    <?php if($photo_column_exists && !empty($row['empPhoto'])): ?>
                                        <img src="../uploads/technicians/<?php echo htmlspecialchars($row['empPhoto']); ?>" 
                                             alt="Technician Photo" class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['empName']); ?></td>
                                <td><?php echo htmlspecialchars($row['empCity']); ?></td>
                                <td><?php echo htmlspecialchars($row['empMobile']); ?></td>
                                <td><?php echo htmlspecialchars($row['empEmail']); ?></td>
                                <?php if($status_column_exists): ?>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo $row['empStatus']; ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="edittechnician.php?empid=<?php echo $row['empid']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['empid']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this technician?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="' . ($status_column_exists ? '8' : '7') . '" class="text-center">No technicians found</td></tr>';
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