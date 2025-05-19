<?php
define('TITLE', 'View Request');
define('PAGE', 'request');
include('includes/header.php');
include('../dbConnection.php');

if(isset($_POST['view'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    $sql = "SELECT r.*, u.r_name as requester_name 
            FROM submitrequest_tb r 
            LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
            WHERE r.request_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}
?>

<div class="container-fluid mt-5 pt-3">
    <div class="row">
        <div class="col-12">
            <!-- Back button -->
            <div class="mb-4">
                <a href="dashboard.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <?php if(isset($row)): ?>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clipboard-list text-warning me-2"></i>
                            <h5 class="mb-0">Request #<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?></h5>
                        </div>
                        <span class="badge bg-warning text-dark">
                            <?php echo date('F j, Y', strtotime($row['request_date'])); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Request Details -->
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-3">
                                <h6 class="fw-bold mb-3">Request Details</h6>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Request Info</label>
                                    <p class="mb-0 fw-medium"><?php echo htmlspecialchars($row['request_info']); ?></p>
                                </div>
                                <div class="mb-0">
                                    <label class="text-muted small mb-1">Description</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($row['request_desc']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Requester Details -->
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-3">
                                <h6 class="fw-bold mb-3">Requester Information</h6>
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-circle me-3">
                                        <?php echo strtoupper(substr($row['requester_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($row['requester_name']); ?></h6>
                                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($row['requester_email']); ?></p>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Contact Number</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($row['requester_mobile']); ?></p>
                                </div>
                                <div>
                                    <label class="text-muted small mb-1">Address</label>
                                    <address class="mb-0">
                                        <?php echo htmlspecialchars($row['requester_add1']); ?><br>
                                        <?php echo htmlspecialchars($row['requester_add2']); ?><br>
                                        <?php echo htmlspecialchars($row['requester_city']); ?>, 
                                        <?php echo htmlspecialchars($row['requester_state']); ?> - 
                                        <?php echo htmlspecialchars($row['requester_zip']); ?>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-end">
                        <a href="dashboard.php" class="btn btn-light rounded-pill px-4 me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <a href="assignwork.php?id=<?php echo $row['request_id']; ?>" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-user-cog me-1"></i> Assign Work
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                    <h5>Request Not Found</h5>
                    <p class="text-muted">The requested service request could not be found.</p>
                    <a href="dashboard.php" class="btn btn-light rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 48px;
    height: 48px;
    background-color: #f3961c;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 20px;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.btn-primary {
    background-color: #f3961c;
    border-color: #f3961c;
}

.btn-primary:hover {
    background-color: #e08a19;
    border-color: #e08a19;
}

.text-warning {
    color: #f3961c !important;
}

.bg-warning {
    background-color: #f3961c !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

address {
    font-style: normal;
}
</style>

<?php include('includes/footer.php'); ?> 