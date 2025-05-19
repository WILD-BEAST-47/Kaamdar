<?php
define('TITLE', 'Requester Profile');
define('PAGE', 'RequesterProfile');
include('includes/header.php'); 
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];
$msg = '';

if(isset($_REQUEST['nameupdate'])) {
    $rName = filter_input(INPUT_POST, 'rName', FILTER_SANITIZE_STRING);
    $sql = "UPDATE requesterlogin_tb SET r_name = ? WHERE r_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $rName, $rEmail);
    
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success" role="alert">Profile Updated Successfully</div>';
        $_SESSION['rName'] = $rName;
    } else {
        $msg = '<div class="alert alert-danger" role="alert">Unable to Update Profile</div>';
    }
    $stmt->close();
}

// Fetch assigned work
$sql = "SELECT a.*, t.empName, t.empMobile, s.request_info, s.request_desc, s.requester_name, s.requester_add1, s.requester_city, s.requester_state, s.requester_zip, s.requester_mobile
        FROM assignwork_tb a 
        JOIN submitrequest_tb s ON a.request_id = s.request_id 
        JOIN technician_tb t ON a.assign_tech = t.empid 
        WHERE s.requester_email = ? 
        ORDER BY a.assign_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rEmail);
$stmt->execute();
$assigned_work = $stmt->get_result();
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <p class="text-muted">Welcome, <?php echo $_SESSION['rName']; ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Total Requests</h6>
                                <h1 class="display-4">
                                    <?php
                                    $sql = "SELECT COUNT(*) FROM submitrequest_tb WHERE requester_email = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $rEmail);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = mysqli_fetch_row($result);
                                    echo $row[0];
                                    ?>
                                </h1>
                            </div>
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="ServiceStatus.php">View Details</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">New Request</h6>
                                <h1 class="display-4">+</h1>
                            </div>
                            <i class="fas fa-plus-circle fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="SubmitRequest.php">Submit Request</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($msg)) { echo $msg; } ?>
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $_SESSION['rName']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $rEmail; ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mobile" class="form-label">Mobile Number</label>
                                    <input type="text" class="form-control" id="mobile" name="mobile" 
                                           value="<?php echo isset($row['r_mobile']) ? htmlspecialchars($row['r_mobile']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter new password (leave blank to keep current)">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="nameupdate" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    position: relative;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
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
    overflow-y: auto;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
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
    const modal = document.getElementById('workDetailsModal');
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Get data from button attributes
        const requestId = button.getAttribute('data-request-id');
        const requestInfo = button.getAttribute('data-request-info');
        const requestDesc = button.getAttribute('data-request-desc');
        const requestDate = button.getAttribute('data-request-date');
        const techName = button.getAttribute('data-tech-name');
        const techContact = button.getAttribute('data-tech-contact');
        const assignDate = button.getAttribute('data-assign-date');
        const requesterName = button.getAttribute('data-requester-name');
        const requesterAddress = button.getAttribute('data-requester-address');
        const requesterMobile = button.getAttribute('data-requester-mobile');
        
        // Update modal content
        document.getElementById('modal-request-id').textContent = '#' + requestId;
        document.getElementById('modal-request-info').textContent = requestInfo;
        document.getElementById('modal-request-desc').textContent = requestDesc;
        document.getElementById('modal-request-date').textContent = requestDate;
        document.getElementById('modal-tech-name').textContent = techName;
        document.getElementById('modal-tech-contact').textContent = techContact;
        document.getElementById('modal-assign-date').textContent = assignDate;
        document.getElementById('modal-requester-name').textContent = requesterName;
        document.getElementById('modal-requester-address').textContent = requesterAddress;
        document.getElementById('modal-requester-mobile').textContent = requesterMobile;
    });
});
</script>

<?php
include('includes/footer.php'); 
$conn->close();
?>