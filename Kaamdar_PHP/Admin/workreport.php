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

// Check if workreport_tb exists, if not create it
$check_table = $conn->query("SHOW TABLES LIKE 'workreport_tb'");
if($check_table->num_rows == 0) {
    $create_table = "CREATE TABLE workreport_tb (
        work_id INT(11) NOT NULL AUTO_INCREMENT,
        r_login_id INT(11) NOT NULL,
        emp_id INT(11) NOT NULL,
        request_info TEXT NOT NULL,
        request_date DATE NOT NULL,
        work_details TEXT,
        work_date DATE,
        work_status ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
        PRIMARY KEY (work_id),
        FOREIGN KEY (r_login_id) REFERENCES requesterlogin_tb(r_login_id),
        FOREIGN KEY (emp_id) REFERENCES technician_tb(empid)
    )";
    
    if($conn->query($create_table)) {
        echo '<div class="alert alert-success">Work report table created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Unable to create work report table: ' . $conn->error . '</div>';
    }
}

// Handle date filter
$where_clause = "";
if(isset($_POST['searchsubmit'])) {
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    
    if(!empty($startdate) && !empty($enddate)) {
        $where_clause = "WHERE wr.request_date BETWEEN '$startdate' AND '$enddate'";
    }
}

// Get all work reports with requester and technician details
$sql = "SELECT wr.*, r.r_name as requester_name, t.empName as technician_name 
        FROM workreport_tb wr 
        LEFT JOIN requesterlogin_tb r ON wr.r_login_id = r.r_login_id 
        LEFT JOIN technician_tb t ON wr.emp_id = t.empid 
        $where_clause
        ORDER BY wr.work_id DESC";

// Debug: Print the SQL query
// echo '<div class="alert alert-info">SQL Query: ' . $sql . '</div>';

$result = $conn->query($sql);

// Debug: Check if there are any rows
if($result) {
    $row_count = $result->num_rows;
    if($row_count == 0) {
        echo '<div class="alert alert-info">No work reports found in the database.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Error executing query: ' . $conn->error . '</div>';
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
                    <a href="addworkreport.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Work Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Date Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="POST" class="row g-3">
                    <div class="col-md-4">
                        <label for="startdate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="startdate" name="startdate" 
                               value="<?php echo isset($_POST['startdate']) ? $_POST['startdate'] : ''; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="enddate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="enddate" name="enddate" 
                               value="<?php echo isset($_POST['enddate']) ? $_POST['enddate'] : ''; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="searchsubmit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                        <a href="workreport.php" class="btn btn-secondary">
                            <i class="fas fa-sync me-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
                                <th>Technician</th>
                                <th>Request Info</th>
                                <th>Work Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $statusClass = $row['work_status'] == 'Completed' ? 'success' : 
                                                 ($row['work_status'] == 'In Progress' ? 'warning' : 'danger');
                                    $statusIcon = $row['work_status'] == 'Completed' ? 'check-circle' : 
                                                ($row['work_status'] == 'In Progress' ? 'clock' : 'times-circle');
                            ?>
                            <tr>
                                <td>#<?php echo $row['work_id']; ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['requester_name']); ?></span>
                                        <small class="text-muted">ID: <?php echo $row['r_login_id']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['technician_name']); ?></span>
                                        <small class="text-muted">ID: <?php echo $row['emp_id']; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['request_info']); ?></span>
                                        <small class="text-muted">Date: <?php echo date('d M Y', strtotime($row['request_date'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['work_details']); ?></span>
                                        <small class="text-muted">Date: <?php echo date('d M Y', strtotime($row['work_date'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo $row['work_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editworkreport.php?work_id=<?php echo $row['work_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $row['work_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this work report?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="7" class="text-center">No work reports found</td></tr>';
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
    
    .table tbody tr:hover {
        background-color: rgba(243, 150, 28, 0.05);
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
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .badge.bg-danger {
        background-color: #dc3545 !important;
    }
    
    .text-muted {
        font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            margin: 0 -1rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
        }
    }
</style>

<?php include('includes/footer.php'); ?>