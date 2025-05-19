<?php ob_start(); ?>
define('TITLE', 'Work Order');
define('PAGE', 'WorkOrder');

// Handle form submissions and redirects here
if ($should_redirect) {
    header("Location: ...");
    exit;
}

include('includes/header.php'); 
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];
$msg = '';

if(isset($_POST['submit'])) {
    // Sanitize and validate input
    $request_info = filter_input(INPUT_POST, 'request_info', FILTER_SANITIZE_STRING);
    $request_desc = filter_input(INPUT_POST, 'request_desc', FILTER_SANITIZE_STRING);
    $requester_name = filter_input(INPUT_POST, 'requester_name', FILTER_SANITIZE_STRING);
    $requester_add1 = filter_input(INPUT_POST, 'requester_add1', FILTER_SANITIZE_STRING);
    $requester_add2 = filter_input(INPUT_POST, 'requester_add2', FILTER_SANITIZE_STRING);
    $requester_city = filter_input(INPUT_POST, 'requester_city', FILTER_SANITIZE_STRING);
    $requester_state = filter_input(INPUT_POST, 'requester_state', FILTER_SANITIZE_STRING);
    $requester_zip = filter_input(INPUT_POST, 'requester_zip', FILTER_SANITIZE_STRING);
    $requester_mobile = filter_input(INPUT_POST, 'requester_mobile', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if(empty($request_info) || empty($request_desc) || empty($requester_name) || 
       empty($requester_add1) || empty($requester_city) || empty($requester_state) || 
       empty($requester_zip) || empty($requester_mobile)) {
        $msg = '<div class="alert alert-warning" role="alert">All fields marked with * are required</div>';
    } else {
        // Insert into database
        $sql = "INSERT INTO submitrequest_tb (request_info, request_desc, requester_name, 
                requester_add1, requester_add2, requester_city, requester_state, 
                requester_zip, requester_mobile, requester_email, request_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $request_info, $request_desc, $requester_name, 
                         $requester_add1, $requester_add2, $requester_city, $requester_state, 
                         $requester_zip, $requester_mobile, $rEmail);
        
        if($stmt->execute()) {
            $msg = '<div class="alert alert-success" role="alert">Work Order Submitted Successfully</div>';
            // Clear form
            $_POST = array();
        } else {
            $msg = '<div class="alert alert-danger" role="alert">Unable to Submit Work Order</div>';
        }
        $stmt->close();
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-2">
                        <i class="fas fa-clipboard-list me-2"></i>Submit Work Order
                    </h3>
                </div>
                <div class="card-body">
                    <?php if(isset($msg)) { echo $msg; } ?>
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="form-group mb-4">
                            <label for="request_info" class="form-label">Service Type *</label>
                            <select class="form-select" id="request_info" name="request_info" required>
                                <option value="">Select Service Type</option>
                                <option value="Plumbing">Plumbing</option>
                                <option value="Electrical">Electrical</option>
                                <option value="Carpentry">Carpentry</option>
                                <option value="Cleaning">Cleaning</option>
                                <option value="Painting">Painting</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="request_desc" class="form-label">Service Description *</label>
                            <textarea class="form-control" id="request_desc" name="request_desc" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="requester_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="requester_name" name="requester_name" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="requester_add1" class="form-label">Address Line 1 *</label>
                            <input type="text" class="form-control" id="requester_add1" name="requester_add1" required>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="requester_add2" class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" id="requester_add2" name="requester_add2">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="requester_city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="requester_city" name="requester_city" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="requester_state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="requester_state" name="requester_state" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="requester_zip" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="requester_zip" name="requester_zip" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="requester_mobile" class="form-label">Mobile Number *</label>
                                    <input type="text" class="form-control" id="requester_mobile" name="requester_mobile" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" name="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Request
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
<?php ob_end_flush(); ?> 