<?php
session_start();
define('TITLE', 'Assign Work');
define('PAGE', 'work');
include('includes/header.php'); 
include('includes/sidebar.php');
include('../dbConnection.php');

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

$msg = '';

// Get request ID from URL
if(isset($_GET['id'])) {
    $request_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    
    // Get request details
    $req_sql = "SELECT r.*, u.r_name as requester_name 
                FROM submitrequest_tb r 
                LEFT JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
                WHERE r.request_id = ?";
    $req_stmt = $conn->prepare($req_sql);
    $req_stmt->bind_param("i", $request_id);
    $req_stmt->execute();
    $req_result = $req_stmt->get_result();
    
    if($req_result->num_rows > 0) {
        $request = $req_result->fetch_assoc();
    } else {
        $msg = '<div class="alert alert-danger">Request not found</div>';
    }
} else {
    $msg = '<div class="alert alert-warning">No request selected. Please select a request to assign work.</div>';
}

// Handle work assignment
if(isset($_POST['assign'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $technician_id = filter_input(INPUT_POST, 'technician_id', FILTER_SANITIZE_NUMBER_INT);
    $assigned_date = filter_input(INPUT_POST, 'assigned_date', FILTER_SANITIZE_STRING);

    // Validate inputs
    if(empty($request_id) || empty($technician_id) || empty($assigned_date)) {
        $msg = '<div class="alert alert-warning">All fields are required</div>';
    } else {
        // Get request details
        $req_sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
        $req_stmt = $conn->prepare($req_sql);
        $req_stmt->bind_param("i", $request_id);
        $req_stmt->execute();
        $req_result = $req_stmt->get_result();
        
        if($req_result->num_rows > 0) {
            $request = $req_result->fetch_assoc();
            
            // Get technician details
            $tech_sql = "SELECT * FROM technician_tb WHERE empid = ?";
            $tech_stmt = $conn->prepare($tech_sql);
            $tech_stmt->bind_param("i", $technician_id);
            $tech_stmt->execute();
            $tech_result = $tech_stmt->get_result();
            
            if($tech_result->num_rows > 0) {
                $technician = $tech_result->fetch_assoc();
                
                // Insert into assignwork_tb
                $insert_sql = "INSERT INTO assignwork_tb (request_id, request_info, request_desc, requester_name, 
                            requester_add1, requester_add2, requester_city, requester_state, requester_zip, 
                            requester_email, requester_mobile, assign_tech, assign_date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("issssssssssss", 
                    $request['request_id'],
                    $request['request_info'],
                    $request['request_desc'],
                    $request['requester_name'],
                    $request['requester_add1'],
                    $request['requester_add2'],
                    $request['requester_city'],
                    $request['requester_state'],
                    $request['requester_zip'],
                    $request['requester_email'],
                    $request['requester_mobile'],
                    $technician_id,
                    $assigned_date
                );
                
                if($insert_stmt->execute()) {
                    // Send email to requester
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'kaamdarservices@gmail.com';
                        $mail->Password = 'tzij wmxt rdja jauu';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        
                        // Recipients
                        $mail->setFrom('kaamdarservices@gmail.com', 'KaamDar Support');
                        $mail->addAddress($request['requester_email'], $request['requester_name']);
                        
                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Service Request Has Been Accepted';
                        $mail->Body = "
                            <h2>Your Service Request Has Been Accepted</h2>
                            <p>Dear " . $request['requester_name'] . ",</p>
                            <p>Your service request has been accepted and assigned to a technician.</p>
                            <h3>Request Details:</h3>
                            <p><strong>Request ID:</strong> " . $request['request_id'] . "</p>
                            <p><strong>Service Type:</strong> " . $request['request_info'] . "</p>
                            <p><strong>Description:</strong> " . $request['request_desc'] . "</p>
                            <h3>Assigned Technician:</h3>
                            <p><strong>Name:</strong> " . $technician['empName'] . "</p>
                            <p><strong>Mobile:</strong> " . $technician['empMobile'] . "</p>
                            <p><strong>Assignment Date:</strong> " . $assigned_date . "</p>
                            <p>Thank you for choosing KaamDar!</p>
                        ";
                        
                        $mail->send();
                        
                        // Send email to technician
                        $tech_mail = new PHPMailer(true);
                        $tech_mail->isSMTP();
                        $tech_mail->Host = 'smtp.gmail.com';
                        $tech_mail->SMTPAuth = true;
                        $tech_mail->Username = 'kaamdarservices@gmail.com';
                        $tech_mail->Password = 'tzij wmxt rdja jauu';
                        $tech_mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $tech_mail->Port = 587;
                        
                        $tech_mail->setFrom('kaamdarservices@gmail.com', 'KaamDar Support');
                        $tech_mail->addAddress($technician['empEmail'], $technician['empName']);
                        
                        $tech_mail->isHTML(true);
                        $tech_mail->Subject = 'New Work Assignment';
                        $tech_mail->Body = "
                            <h2>New Work Assignment</h2>
                            <p>Dear " . $technician['empName'] . ",</p>
                            <p>A new work has been assigned to you.</p>
                            <h3>Work Details:</h3>
                            <p><strong>Request ID:</strong> " . $request['request_id'] . "</p>
                            <p><strong>Service Type:</strong> " . $request['request_info'] . "</p>
                            <p><strong>Description:</strong> " . $request['request_desc'] . "</p>
                            <h3>Requester Details:</h3>
                            <p><strong>Name:</strong> " . $request['requester_name'] . "</p>
                            <p><strong>Address:</strong> " . $request['requester_add1'] . ", " . $request['requester_city'] . ", " . $request['requester_state'] . " - " . $request['requester_zip'] . "</p>
                            <p><strong>Mobile:</strong> " . $request['requester_mobile'] . "</p>
                            <p><strong>Assignment Date:</strong> " . $assigned_date . "</p>
                            <p>Please contact the requester to schedule the service.</p>
                        ";
                        
                        $tech_mail->send();
                        
                        $msg = '<div class="alert alert-success">Work assigned successfully!</div>';
                        echo "<script> location.href='work.php'; </script>";
                    } catch (Exception $e) {
                        $msg = '<div class="alert alert-warning">Work assigned but email not sent: ' . $mail->ErrorInfo . '</div>';
                    }
                } else {
                    $msg = '<div class="alert alert-danger">Request not found</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">Technician not found</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">Request not found</div>';
        }
    }
}

// Get all technicians
$tech_sql = "SELECT * FROM technician_tb";
$tech_result = $conn->query($tech_sql);
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Assign Work</h2>
                        <p class="text-muted">Assign work to technicians</p>
                    </div>
                    <a href="work.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Work Orders
                    </a>
                </div>
            </div>
        </div>

        <?php if($msg): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <?php echo $msg; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(isset($request)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Request ID:</strong> #<?php echo str_pad($request['request_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                <p><strong>Service Type:</strong> <?php echo htmlspecialchars($request['request_info']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($request['request_desc']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Requester:</strong> <?php echo htmlspecialchars($request['requester_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($request['requester_email']); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($request['requester_mobile']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Assign Technician</h5>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="technician_id" class="form-label">Select Technician</label>
                                    <select class="form-select" id="technician_id" name="technician_id" required>
                                        <option value="">Select a technician</option>
                                        <?php while($tech = $tech_result->fetch_assoc()): ?>
                                        <option value="<?php echo $tech['empid']; ?>">
                                            <?php echo htmlspecialchars($tech['empName']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="assigned_date" class="form-label">Assignment Date</label>
                                    <input type="date" class="form-control" id="assigned_date" name="assigned_date" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" name="assign" class="btn btn-primary">
                                    <i class="fas fa-user-cog me-2"></i>Assign Work
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus, .form-select:focus {
    border-color: #f3961c;
    box-shadow: 0 0 0 0.25rem rgba(243, 150, 28, 0.25);
}

.btn-primary {
    background-color: #f3961c;
    border-color: #f3961c;
}

.btn-primary:hover {
    background-color: #e08a19;
    border-color: #e08a19;
}

.alert {
    border-radius: 8px;
    border: none;
}
</style>

<?php include('includes/footer.php'); ?> 