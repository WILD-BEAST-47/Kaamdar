<?php
session_start();
define('TITLE', 'Submit Request');
define('PAGE', 'SubmitRequest');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$msg = '';
$success = false;

if(isset($_REQUEST['submit'])) {
    // Sanitize and validate input
    $request_info = filter_input(INPUT_POST, 'service_type', FILTER_SANITIZE_STRING);
    $request_desc = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $requester_name = $_SESSION['rName'];
    $requester_add1 = filter_input(INPUT_POST, 'address1', FILTER_SANITIZE_STRING);
    $requester_add2 = filter_input(INPUT_POST, 'address2', FILTER_SANITIZE_STRING);
    $requester_city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $requester_state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $requester_zip = filter_input(INPUT_POST, 'zip', FILTER_SANITIZE_NUMBER_INT);
    $requester_email = $_SESSION['rEmail'];
    $requester_mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_NUMBER_INT);
    $request_date = date('Y-m-d');

    // Validate mobile number
    if(strlen($requester_mobile) < 10 || strlen($requester_mobile) > 15) {
        $msg = '<div class="alert alert-warning" role="alert">Please enter a valid mobile number</div>';
    }
    // Validate ZIP code
    else if(strlen($requester_zip) < 5 || strlen($requester_zip) > 10) {
        $msg = '<div class="alert alert-warning" role="alert">Please enter a valid ZIP code</div>';
    }
    // Check if all required fields are filled
    else if(empty($request_info) || empty($request_desc) || empty($requester_add1) || 
            empty($requester_city) || empty($requester_state) || empty($requester_zip) || 
            empty($requester_mobile)) {
        $msg = '<div class="alert alert-warning" role="alert">All fields are required</div>';
    } else {
        try {
            $sql = "INSERT INTO submitrequest_tb (request_info, request_desc, requester_name, 
                    requester_add1, requester_add2, requester_city, requester_state, 
                    requester_zip, requester_email, requester_mobile, request_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if($stmt === false) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            $stmt->bind_param("sssssssssss", $request_info, $request_desc, $requester_name, 
                            $requester_add1, $requester_add2, $requester_city, $requester_state, 
                            $requester_zip, $requester_email, $requester_mobile, $request_date);
            
            if($stmt->execute()) {
                $request_id = $conn->insert_id;
                $success = true;
                $msg = '<div class="alert alert-success" role="alert">Request submitted successfully!</div>';
                
                // Generate receipt
                $receipt = generateReceipt($request_id, $request_info, $request_desc, $request_date);
                $_SESSION['receipt'] = $receipt;
            } else {
                throw new Exception("Error executing statement: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            $msg = '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

function generateReceipt($request_id, $request_info, $request_desc, $request_date) {
    $receipt = "
    <div class='receipt p-4 border rounded shadow-sm'>
        <h2 class='text-center mb-4'>KaamDar Service Request Receipt</h2>
        <div class='receipt-details'>
            <p class='mb-2'><strong>Request ID:</strong> #" . str_pad($request_id, 6, '0', STR_PAD_LEFT) . "</p>
            <p class='mb-2'><strong>Service Type:</strong> $request_info</p>
            <p class='mb-2'><strong>Description:</strong> $request_desc</p>
            <p class='mb-2'><strong>Date:</strong> $request_date</p>
            <p class='mb-2'><strong>Status:</strong> Pending</p>
        </div>
    </div>";
    return $receipt;
}
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
                                    $stmt->bind_param("s", $_SESSION['rEmail']);
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
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Assigned Work</h6>
                                <h1 class="display-4">
                                    <?php
                                    $sql = "SELECT COUNT(*) FROM assignwork_tb WHERE requester_email = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $_SESSION['rEmail']);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $row = mysqli_fetch_row($result);
                                    echo $row[0];
                                    ?>
                                </h1>
                            </div>
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="ViewAssignedWork.php">View Details</a>
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
                        <h5 class="mb-0">Submit Service Request</h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($msg)) { echo $msg; } ?>
                        
                        <?php if($success && isset($_SESSION['receipt'])): ?>
                            <div class="receipt-container">
                                <?php echo $_SESSION['receipt']; ?>
                                <div class="text-center mt-4">
                                    <button class="btn btn-primary" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print Receipt
                                    </button>
                                    <a href="RequesterDashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form action="" method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="service_type" class="form-label">Service Type</label>
                                        <select class="form-select" id="service_type" name="service_type" required>
                                            <option value="">Select a service</option>
                                            <option value="Home Repairs">Home Repairs</option>
                                            <option value="Construction">Construction</option>
                                            <option value="Cleaning">Cleaning</option>
                                            <option value="Painting">Painting</option>
                                            <option value="Handyman">Handyman</option>
                                            <option value="Moving">Moving & Packing</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="description" class="form-label">Service Description</label>
                                        <textarea class="form-control" id="description" name="description" 
                                                  rows="3" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="address1" class="form-label">Address Line 1</label>
                                        <input type="text" class="form-control" id="address1" name="address1" 
                                               value="<?php echo isset($_POST['address1']) ? htmlspecialchars($_POST['address1']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address2" class="form-label">Address Line 2</label>
                                        <input type="text" class="form-control" id="address2" name="address2" 
                                               value="<?php echo isset($_POST['address2']) ? htmlspecialchars($_POST['address2']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" 
                                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" 
                                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="zip" class="form-label">ZIP Code</label>
                                        <input type="number" class="form-control" id="zip" name="zip" 
                                               value="<?php echo isset($_POST['zip']) ? htmlspecialchars($_POST['zip']) : ''; ?>" 
                                               required onkeypress="return isInputNumber(event)">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="mobile" class="form-label">Mobile Number</label>
                                        <input type="number" class="form-control" id="mobile" name="mobile" 
                                               value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>" 
                                               required onkeypress="return isInputNumber(event)">
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Only Number for input fields
function isInputNumber(evt) {
    var ch = String.fromCharCode(evt.which);
    if (!(/[0-9]/.test(ch))) {
        evt.preventDefault();
    }
}
</script>

<?php
include('includes/footer.php'); 
$conn->close();
?>