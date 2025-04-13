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

// Get requests for current page
$sql = "SELECT r.*, u.r_name as requester_name, 
        r.status as request_status,
        t.empName as tech_name
        FROM submitrequest_tb r 
        LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
        LEFT JOIN assignwork_tb a ON r.request_id = a.request_id
        LEFT JOIN technician_tb t ON a.assign_tech = t.empName
        ORDER BY r.request_date DESC 
        LIMIT 5";
$result = $conn->query($sql);
if (!$result) {
    error_log("Error getting recent requests: " . $conn->error);
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col-12">
                <h2 class="mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Welcome to your dashboard</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
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
                <div class="card shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
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
                <div class="card shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
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
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-warning me-2"></i>
                            <h5 class="mb-0">Recent Service Requests</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Info</th>
                                        <th>Requester</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($result && $result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($row['request_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($row['request_info']); ?></td>
                                            <td><?php echo htmlspecialchars($row['requester_name']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                                            <td>
                                                <?php 
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
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                    <?php echo $row['request_status']; ?>
                                                </span>
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
</div>

<style>
.avatar-circle {
    width: 28px;
    height: 28px;
    background-color: #f3961c;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 12px;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.rounded-circle {
    width: 40px;
    height: 40px;
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

/* Update spacing */
.container-fluid {
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}

.welcome-text {
    font-size: 1.1rem;
}

.card {
    margin-bottom: 1rem;
}

/* Update navbar spacing in header */
.navbar {
    margin-bottom: 1.5rem;
}

/* Ensure content doesn't get hidden under fixed navbar */
body {
    padding-top: 56px;
}
</style>

<?php include('includes/footer.php'); ?>