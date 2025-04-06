<?php
define('TITLE', 'Dashboard');
define('PAGE', 'dashboard');
include('includes/header.php');

include('../dbConnection.php');

// Get total requests count
$sql = "SELECT COUNT(*) as total FROM submitrequest_tb";
$result = $conn->query($sql);
if ($result) {
    $total_requests = $result->fetch_assoc()['total'];
} else {
    $total_requests = 0;
    error_log("Error getting total requests: " . $conn->error);
}

// Get assigned works count
$sql = "SELECT COUNT(*) as total FROM assignwork_tb";
$result = $conn->query($sql);
if ($result) {
    $total_assigned = $result->fetch_assoc()['total'];
} else {
    $total_assigned = 0;
    error_log("Error getting assigned works: " . $conn->error);
}

// Get technicians count
$sql = "SELECT COUNT(*) as total FROM technician_tb";
$result = $conn->query($sql);
if ($result) {
    $total_technicians = $result->fetch_assoc()['total'];
} else {
    $total_technicians = 0;
    error_log("Error getting technicians count: " . $conn->error);
}

// Get recent requests with proper error handling
$sql = "SELECT r.*, u.r_name as requester_name 
        FROM submitrequest_tb r 
        LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
        ORDER BY r.request_date DESC LIMIT 5";
$result = $conn->query($sql);
if (!$result) {
    error_log("Error getting recent requests: " . $conn->error);
}
?>

<!-- Main content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="welcome-text">
                    <span class="text-muted">Welcome back,</span>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 rounded-circle bg-warning bg-opacity-10 p-3">
                                    <i class="fas fa-clipboard-list fa-2x text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Total Requests</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo $total_requests; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 p-3">
                                    <i class="fas fa-tasks fa-2x text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Assigned Works</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo $total_assigned; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 rounded-circle bg-info bg-opacity-10 p-3">
                                    <i class="fas fa-user-cog fa-2x text-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Technicians</h6>
                                    <h3 class="mb-0 fw-bold"><?php echo $total_technicians; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-warning me-2"></i>
                        <h5 class="mb-0">Recent Service Requests</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Request ID</th>
                                    <th class="px-4 py-3">Info</th>
                                    <th class="px-4 py-3">Requester</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-light text-dark">
                                                #<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['request_info']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-circle">
                                                        <?php echo strtoupper(substr($row['requester_name'], 0, 1)); ?>
                                                    </div>
                                                </div>
                                                <div class="ms-2"><?php echo htmlspecialchars($row['requester_name']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="text-muted">
                                                <?php echo date('Y-m-d', strtotime($row['request_date'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form action="viewrequest.php" method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo $row['request_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" name="view">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                                            No recent requests found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 32px;
    height: 32px;
    background-color: #f3961c;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.rounded-circle {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* Add new styles for spacing */
.container-fluid {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.welcome-text {
    font-size: 1.25rem;
}

.card {
    margin-bottom: 1.5rem;
}

/* Update navbar spacing in header */
.navbar {
    margin-bottom: 2rem;
}

/* Ensure content doesn't get hidden under fixed navbar */
body {
    padding-top: 60px;
}
</style>

<?php include('includes/footer.php'); ?>