<?php
// PHPMailer namespace declarations
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
define('TITLE', 'Requests');
define('PAGE', 'request');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Check if assignwork_tb exists, if not create it
$check_table = "SHOW TABLES LIKE 'assignwork_tb'";
$result = $conn->query($check_table);

if($result->num_rows == 0) {
    // Create assignwork_tb table
    $create_table = "CREATE TABLE assignwork_tb (
        assign_id INT(11) NOT NULL AUTO_INCREMENT,
        request_id INT(11) NOT NULL,
        assign_tech VARCHAR(100) NOT NULL,
        assign_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (assign_id),
        FOREIGN KEY (request_id) REFERENCES submitrequest_tb(request_id)
    )";
    
    if($conn->query($create_table)) {
        echo '<div class="alert alert-success">Table assignwork_tb created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error creating table: ' . $conn->error . '</div>';
    }
}

// Handle request status update and technician assignment
if(isset($_POST['update'])) {
    $request_id = $_POST['request_id'];
    $technician_id = $_POST['technician_id'];
    
    // Check if assignment already exists
    $check_sql = "SELECT * FROM assignwork_tb WHERE request_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $request_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        // Update existing assignment
        $sql = "UPDATE assignwork_tb SET assign_tech = ? WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $technician_id, $request_id);
    } else {
        // Create new assignment
        $sql = "INSERT INTO assignwork_tb (request_id, assign_tech) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $request_id, $technician_id);
    }
    
    if($stmt->execute()) {
        echo '<div class="alert alert-success">Request updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Unable to update request: ' . $conn->error . '</div>';
    }
}

// Get all requests with requester and assignment details
$sql = "SELECT sr.*, r.r_name, r.r_email, r.r_mobile, 
               t.empName as technician_name, aw.assign_tech
        FROM submitrequest_tb sr 
        LEFT JOIN requesterlogin_tb r ON sr.requester_email = r.r_email
        LEFT JOIN assignwork_tb aw ON sr.request_id = aw.request_id
        LEFT JOIN technician_tb t ON aw.assign_tech = t.empEmail
        ORDER BY sr.request_id DESC";

$result = $conn->query($sql);

// Get all technicians for the dropdown
$technicians_sql = "SELECT empid, empName, empEmail FROM technician_tb WHERE empStatus = 'Active' ORDER BY empName";
$technicians_result = $conn->query($technicians_sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Service Requests</h2>
                        <p class="text-muted">View and manage service requests</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
                                <th>Request Info</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Technician</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $statusClass = $row['technician_name'] ? 'success' : 'danger';
                                    $statusIcon = $row['technician_name'] ? 'check-circle' : 'times-circle';
                            ?>
                            <tr>
                                <td>#<?php echo $row['request_id']; ?></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['requester_name']); ?></span>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['requester_email']); ?></small>
                                        <small class="text-muted"><?php echo htmlspecialchars($row['requester_mobile']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['request_info']); ?></td>
                                <td><?php echo htmlspecialchars($row['request_desc']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['request_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                        <?php echo $row['technician_name'] ? 'Assigned' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['technician_name']) { ?>
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['technician_name']); ?></span>
                                    <?php } else { ?>
                                        <span class="text-muted">Not Assigned</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#updateModal<?php echo $row['request_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?php echo $row['request_id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Request</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="" method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Assign Technician</label>
                                                    <select name="technician_id" class="form-select">
                                                        <option value="">Select Technician</option>
                                                        <?php 
                                                        if($technicians_result && $technicians_result->num_rows > 0) {
                                                            while($tech = $technicians_result->fetch_assoc()) {
                                                                $selected = $tech['empEmail'] == ($row['assign_tech'] ?? '') ? 'selected' : '';
                                                                echo "<option value='{$tech['empEmail']}' $selected>{$tech['empName']}</option>";
                                                            }
                                                            $technicians_result->data_seek(0);
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" name="update" class="btn btn-primary">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center">No requests found</td></tr>';
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
    
    .card-body {
        padding: 0;
    }
    
    .table {
        margin-bottom: 0;
        width: 100%;
    }
    
    .table th {
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
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
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
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

    /* Modal Fixes */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1050;
        display: none;
    }

    .modal.show {
        display: block;
    }

    .modal-dialog {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
        max-width: 500px;
        width: 90%;
        pointer-events: auto;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        pointer-events: auto;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .form-select {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #fff;
    }

    .form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Remove Bootstrap's default modal backdrop */
    .modal-backdrop {
        display: none;
    }

    body.modal-open {
        overflow: hidden;
        padding-right: 0 !important;
    }

    /* Ensure modal is clickable */
    .modal * {
        pointer-events: auto;
    }

    /* Prevent body scroll when modal is open */
    body.modal-open {
        overflow: hidden;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal show/hide
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            this.style.display = 'block';
            document.body.classList.add('modal-open');
            // Force modal to stay in place
            const dialog = this.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.position = 'fixed';
                dialog.style.top = '50%';
                dialog.style.left = '50%';
                dialog.style.transform = 'translate(-50%, -50%)';
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            this.style.display = 'none';
            document.body.classList.remove('modal-open');
        });
    });

    // Prevent modal from moving when mouse is near
    document.addEventListener('mousemove', function(e) {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const dialog = openModal.querySelector('.modal-dialog');
            if (dialog) {
                dialog.style.position = 'fixed';
                dialog.style.top = '50%';
                dialog.style.left = '50%';
                dialog.style.transform = 'translate(-50%, -50%)';
            }
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>