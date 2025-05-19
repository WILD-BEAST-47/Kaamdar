<?php
session_start();
define('TITLE', 'Work Orders');
define('PAGE', 'work');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get all work orders
$sql = "SELECT a.*, t.empName as tech_name 
        FROM assignwork_tb a 
        LEFT JOIN technician_tb t ON a.assign_tech = t.empEmail 
        ORDER BY a.assign_date DESC";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-0">Work Orders</h2>
                <p class="text-muted">Manage and track assigned work orders</p>
            </div>
        </div>

        <?php if($result->num_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Request ID</th>
                                    <th>Request Info</th>
                                    <th>Customer</th>
                                    <th>Location</th>
                                    <th>Technician</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['request_id']; ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold"><?php echo htmlspecialchars($row['request_info']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['requester_name']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?php echo htmlspecialchars($row['requester_name']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['requester_mobile']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?php echo htmlspecialchars($row['requester_city']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['requester_add2']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($row['tech_name']): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($row['tech_name']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['assign_date'])); ?></td>
                                    <td>
                                        <?php 
                                        $status = isset($row['assign_status']) ? $row['assign_status'] : 'Pending';
                                        $status_class = '';
                                        switch($status) {
                                            case 'In Progress':
                                                $status_class = 'bg-warning';
                                                break;
                                            case 'Completed':
                                                $status_class = 'bg-success';
                                                break;
                                            default:
                                                $status_class = 'bg-info';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form action="viewassignwork.php" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $row['request_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" name="view" value="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </form>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $row['request_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" name="delete" value="Delete" 
                                                        onclick="return confirm('Are you sure you want to delete this work order?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No work orders found.
            </div>
        <?php endif; ?>

        <?php
        // Handle delete request
        if(isset($_REQUEST['delete'])) {
            $sql = "DELETE FROM assignwork_tb WHERE request_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_REQUEST['id']);
            
            if($stmt->execute()) {
                echo '<div class="alert alert-success">Work order deleted successfully!</div>';
                echo '<meta http-equiv="refresh" content="2;URL=?deleted" />';
            } else {
                echo '<div class="alert alert-danger">Unable to delete work order.</div>';
            }
            $stmt->close();
        }
        ?>
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
        font-size: 0.9rem;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .table tbody tr:hover {
        background-color: rgba(243, 150, 28, 0.05);
    }
    
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
        font-size: 0.8rem;
    }
    
    .btn-group {
        gap: 0.5rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }

    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
        
        .badge {
            padding: 0.25em 0.5em;
        }
    }
</style>

<?php include('includes/footer.php'); ?>