<?php
define('TITLE', 'Work Order');
define('PAGE', 'work');
include('../dbConnection.php');
include('includes/header.php');
include('includes/sidebar.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total number of records
$total_sql = "SELECT COUNT(*) as total FROM submitrequest_tb";
$total_result = $conn->query($total_sql);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Ensure page number is valid
if($page < 1) $page = 1;
if($page > $total_pages) $page = $total_pages;

// Get requests for current page
$sql = "SELECT r.*, u.r_name as requester_name, 
        r.status as request_status,
        t.empName as tech_name,
        a.assign_date
        FROM submitrequest_tb r 
        LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
        LEFT JOIN assignwork_tb a ON r.request_id = a.request_id
        LEFT JOIN technician_tb t ON a.assign_tech = t.empName
        ORDER BY r.request_date DESC, r.request_id DESC 
        LIMIT $offset, $records_per_page";
$result = $conn->query($sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Work Orders</h2>
                <p class="text-muted">View all work orders</p>
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
                                            <strong><?php echo $row['requester_name']; ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo $row['requester_email']; ?><br>
                                                <?php echo $row['requester_mobile']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong>Description:</strong> <?php echo $row['request_desc']; ?><br>
                                            <strong>Address:</strong> <?php echo $row['requester_add1'] . ', ' . $row['requester_add2'] . ', ' . $row['requester_city'] . ', ' . $row['requester_state'] . ' - ' . $row['requester_zip']; ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                                        <td>
                                            <?php if($row['request_status'] == 'Assigned'): ?>
                                            <span class="badge bg-success">Assigned</span><br>
                                            <small class="text-muted">To: <?php echo $row['tech_name']; ?></small>
                                            <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['request_status'] == 'Pending'): ?>
                                            <a href="request.php?request_id=<?php echo $row['request_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                Assign Work
                                            </a>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-info btn-sm view-details-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#workDetailsModal"
                                                    data-request-id="<?php echo $row['request_id']; ?>"
                                                    data-requester-name="<?php echo htmlspecialchars($row['requester_name']); ?>"
                                                    data-requester-email="<?php echo htmlspecialchars($row['requester_email']); ?>"
                                                    data-requester-mobile="<?php echo htmlspecialchars($row['requester_mobile']); ?>"
                                                    data-requester-address="<?php echo htmlspecialchars($row['requester_add1'] . ', ' . $row['requester_add2'] . ', ' . $row['requester_city'] . ', ' . $row['requester_state'] . ' - ' . $row['requester_zip']); ?>"
                                                    data-request-info="<?php echo htmlspecialchars($row['request_info']); ?>"
                                                    data-request-desc="<?php echo htmlspecialchars($row['request_desc']); ?>"
                                                    data-request-date="<?php echo date('Y-m-d', strtotime($row['request_date'])); ?>"
                                                    data-tech-name="<?php echo htmlspecialchars($row['tech_name'] ?? 'Not Assigned'); ?>"
                                                    data-assign-date="<?php echo isset($row['assign_date']) ? date('Y-m-d', strtotime($row['assign_date'])) : 'Not Assigned'; ?>">
                                                View Details
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
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
</div>

<!-- Single Work Details Modal -->
<div class="modal fade" id="workDetailsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="width: 800px; max-width: 800px; margin-top: 0;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Work Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Request Information</h6>
                        <p><strong>Request ID:</strong> <span id="modal-request-id"></span></p>
                        <p><strong>Service Type:</strong> <span id="modal-request-info"></span></p>
                        <p><strong>Description:</strong> <span id="modal-request-desc"></span></p>
                        <p><strong>Request Date:</strong> <span id="modal-request-date"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Requester Information</h6>
                        <p><strong>Name:</strong> <span id="modal-requester-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-requester-email"></span></p>
                        <p><strong>Mobile:</strong> <span id="modal-requester-mobile"></span></p>
                        <p><strong>Address:</strong> <span id="modal-requester-address"></span></p>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="text-primary mb-3">Assignment Details</h6>
                        <p><strong>Status:</strong> <span class="badge bg-success">Assigned</span></p>
                        <p><strong>Assigned To:</strong> <span id="modal-tech-name"></span></p>
                        <p><strong>Assigned Date:</strong> <span id="modal-assign-date"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
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
    const modal = document.getElementById('workDetailsModal');
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Get data from button attributes
        const requestId = button.getAttribute('data-request-id');
        const requesterName = button.getAttribute('data-requester-name');
        const requesterEmail = button.getAttribute('data-requester-email');
        const requesterMobile = button.getAttribute('data-requester-mobile');
        const requesterAddress = button.getAttribute('data-requester-address');
        const requestInfo = button.getAttribute('data-request-info');
        const requestDesc = button.getAttribute('data-request-desc');
        const requestDate = button.getAttribute('data-request-date');
        const techName = button.getAttribute('data-tech-name');
        const assignDate = button.getAttribute('data-assign-date');
        
        // Update modal content
        document.getElementById('modal-request-id').textContent = '#' + requestId;
        document.getElementById('modal-requester-name').textContent = requesterName;
        document.getElementById('modal-requester-email').textContent = requesterEmail;
        document.getElementById('modal-requester-mobile').textContent = requesterMobile;
        document.getElementById('modal-requester-address').textContent = requesterAddress;
        document.getElementById('modal-request-info').textContent = requestInfo || 'Not specified';
        document.getElementById('modal-request-desc').textContent = requestDesc || 'No description provided';
        document.getElementById('modal-request-date').textContent = requestDate;
        document.getElementById('modal-tech-name').textContent = techName;
        document.getElementById('modal-assign-date').textContent = assignDate;
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