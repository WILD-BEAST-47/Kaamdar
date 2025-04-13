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

// Check if status column exists in submitrequest_tb
$check_status_column = $conn->query("SHOW COLUMNS FROM submitrequest_tb LIKE 'status'");
if($check_status_column->num_rows == 0) {
    // Add status column
    $sql = "ALTER TABLE submitrequest_tb ADD COLUMN status ENUM('Pending','Assigned','Completed') NOT NULL DEFAULT 'Pending'";
    if($conn->query($sql)) {
        echo '<div class="alert alert-success">Status column added successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error adding status column: ' . $conn->error . '</div>';
    }
}

// Handle request status update and technician assignment
if(isset($_POST['update'])) {
    $request_id = $_POST['request_id'];
    $technician_id = $_POST['technician_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
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
            // Update status in submitrequest_tb
            $update_status_sql = "UPDATE submitrequest_tb SET status = 'Assigned' WHERE request_id = ?";
            $update_stmt = $conn->prepare($update_status_sql);
            $update_stmt->bind_param("i", $request_id);
            
            if($update_stmt->execute()) {
                $conn->commit();
                echo '<div class="alert alert-success">Request updated successfully!</div>';
            } else {
                throw new Exception("Error updating status");
            }
        } else {
            throw new Exception("Error updating assignment");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo '<div class="alert alert-danger">Unable to update request: ' . $e->getMessage() . '</div>';
    }
}

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$sql = "SELECT COUNT(*) as total FROM submitrequest_tb";
$result = $conn->query($sql);
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page number is valid
if($page < 1) $page = 1;
if($page > $total_pages) $page = $total_pages;

// Get requests for current page
$sql = "SELECT r.*, u.r_name as requester_name, 
        r.status as request_status,
        t.empName as tech_name
        FROM submitrequest_tb r 
        LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
        LEFT JOIN assignwork_tb a ON r.request_id = a.request_id
        LEFT JOIN technician_tb t ON a.assign_tech = t.empName
        ORDER BY r.request_date DESC 
        LIMIT $offset, $records_per_page";
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
                                    $statusClass = '';
                                    $statusIcon = '';
                                    switch($row['request_status']) {
                                        case 'Assigned':
                                            $statusClass = 'success';
                                            $statusIcon = 'check-circle';
                                            break;
                                        case 'Completed':
                                            $statusClass = 'info';
                                            $statusIcon = 'check-double';
                                            break;
                                        default:
                                            $statusClass = 'warning';
                                            $statusIcon = 'clock';
                                    }
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
                                        <?php echo $row['request_status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($row['tech_name']) { ?>
                                        <span class="fw-bold"><?php echo htmlspecialchars($row['tech_name']); ?></span>
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
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
                        </div>
                        
                        <nav aria-label="Page navigation">
                            <ul class="pagination mb-0">
                                <!-- First Page -->
                                <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <!-- Previous Page -->
                                <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                for($i = $start_page; $i <= $end_page; $i++) {
                                    $active = $i == $page ? 'active' : '';
                                    echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                                }
                                
                                if($end_page < $total_pages) {
                                    if($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo "<li class='page-item'><a class='page-link' href='?page=$total_pages'>$total_pages</a></li>";
                                }
                                ?>
                                
                                <!-- Next Page -->
                                <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <!-- Last Page -->
                                <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
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

    /* Enhanced Pagination styles */
    .pagination {
        margin-bottom: 0;
        gap: 0.25rem;
    }

    .page-link {
        color: #f3961c;
        border-color: #f3961c;
        padding: 0.5rem 0.75rem;
        min-width: 2.5rem;
        text-align: center;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .page-link:hover {
        color: #fff;
        background-color: #f3961c;
        border-color: #f3961c;
        transform: translateY(-1px);
    }

    .page-item.active .page-link {
        background-color: #f3961c;
        border-color: #f3961c;
        font-weight: 600;
    }

    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Responsive pagination */
    @media (max-width: 768px) {
        .pagination {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .page-link {
            padding: 0.375rem 0.5rem;
            min-width: 2rem;
        }
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

    // Add smooth scrolling to top when changing pages
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => {
                window.location.href = href;
            }, 300);
        });
    });
});
</script>

<?php include('includes/footer.php'); ?>