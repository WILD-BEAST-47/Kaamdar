<?php
session_start();
define('TITLE', 'Assign Work');
define('PAGE', 'work');
include('includes/header.php'); 
include('includes/sidebar.php');
include('../dbConnection.php');

// Include mail configuration
require_once '../Requester/mail_config.php';

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
        try {
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
                        $technician['empName'],
                        $assigned_date
                    );
                    
                    if($insert_stmt->execute()) {
                        // Send email to requester
                        $req_subject = "Service Request Update - KaamDar";
                        $req_body = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='background: linear-gradient(135deg, #f3961c 0%, #e67e22 100%); padding: 20px; color: white; text-align: center; border-radius: 8px 8px 0 0;'>
                                    <h2 style='margin: 0;'>Service Request Update</h2>
                                </div>
                                <div style='background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 8px 8px;'>
                                    <p style='font-size: 16px;'>Dear " . htmlspecialchars($request['requester_name']) . ",</p>
                                    <p style='font-size: 16px;'>Your service request has been assigned to a technician.</p>
                                    
                                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                        <h3 style='color: #f3961c; margin-top: 0;'>Request Details:</h3>
                                        <p><strong>Request ID:</strong> #" . str_pad($request['request_id'], 6, '0', STR_PAD_LEFT) . "</p>
                                        <p><strong>Service Type:</strong> " . htmlspecialchars($request['request_info']) . "</p>
                                        <p><strong>Description:</strong> " . htmlspecialchars($request['request_desc']) . "</p>
                                        <p><strong>Assignment Date:</strong> " . htmlspecialchars($assigned_date) . "</p>
                                        <p><strong>Assigned Technician:</strong> " . htmlspecialchars($technician['empName']) . "</p>
                                    </div>
                                    
                                    <div style='text-align: center; margin-top: 20px;'>
                                        <a href='mailto:support@kaamdar.com' style='display: inline-block; padding: 10px 20px; background: #f3961c; color: white; text-decoration: none; border-radius: 5px;'>Contact Support</a>
                                    </div>
                                </div>
                            </div>";
                        
                        // Send email to technician
                        $tech_subject = "New Work Assignment - KaamDar";
                        $tech_body = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                                <div style='background: linear-gradient(135deg, #f3961c 0%, #e67e22 100%); padding: 20px; color: white; text-align: center; border-radius: 8px 8px 0 0;'>
                                    <h2 style='margin: 0;'>New Work Assignment</h2>
                                </div>
                                <div style='background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 8px 8px;'>
                                    <p style='font-size: 16px;'>Dear " . htmlspecialchars($technician['empName']) . ",</p>
                                    <p style='font-size: 16px;'>A new work has been assigned to you.</p>
                                    
                                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                        <h3 style='color: #f3961c; margin-top: 0;'>Work Details:</h3>
                                        <p><strong>Request ID:</strong> #" . str_pad($request['request_id'], 6, '0', STR_PAD_LEFT) . "</p>
                                        <p><strong>Service Type:</strong> " . htmlspecialchars($request['request_info']) . "</p>
                                        <p><strong>Description:</strong> " . htmlspecialchars($request['request_desc']) . "</p>
                                        <p><strong>Assignment Date:</strong> " . htmlspecialchars($assigned_date) . "</p>
                                        <p><strong>Customer Name:</strong> " . htmlspecialchars($request['requester_name']) . "</p>
                                        <p><strong>Customer Contact:</strong> " . htmlspecialchars($request['requester_mobile']) . "</p>
                                        <p><strong>Customer Email:</strong> " . htmlspecialchars($request['requester_email']) . "</p>
                                        <p><strong>Service Location:</strong> " . htmlspecialchars($request['requester_city']) . "</p>
                                    </div>
                                    
                                    <div style='text-align: center; margin-top: 20px;'>
                                        <a href='mailto:" . htmlspecialchars($request['requester_email']) . "' style='display: inline-block; padding: 10px 20px; background: #f3961c; color: white; text-decoration: none; border-radius: 5px;'>Contact Customer</a>
                                    </div>
                                </div>
                            </div>";

                        // Send emails using the sendMail function
                        $req_email_sent = sendMail($request['requester_email'], $req_subject, $req_body, $request['requester_name']);
                        $tech_email_sent = sendMail($technician['empEmail'], $tech_subject, $tech_body, $technician['empName']);

                        if($req_email_sent && $tech_email_sent) {
                            // Update status in submitrequest_tb
                            $update_sql = "UPDATE submitrequest_tb SET status = 'Assigned' WHERE request_id = ?";
                            $update_stmt = $conn->prepare($update_sql);
                            $update_stmt->bind_param("i", $request_id);
                            $update_stmt->execute();
                            
                            $msg = '<div class="alert alert-success">Work assigned successfully! Emails have been sent to both the requester and technician.</div>';
                        } else {
                            $msg = '<div class="alert alert-warning">Work assigned but there was an issue sending the emails. Please check the mail configuration.</div>';
                        }
                        
                        echo "<script>location.href='work.php';</script>";
                    } else {
                        $msg = '<div class="alert alert-danger">Error assigning work: ' . $insert_stmt->error . '</div>';
                    }
                } else {
                    $msg = '<div class="alert alert-danger">Technician not found</div>';
                }
            } else {
                $msg = '<div class="alert alert-danger">Request not found</div>';
            }
        } catch (Exception $e) {
            $msg = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get all technicians
$tech_sql = "SELECT * FROM technician_tb";
$tech_result = $conn->query($tech_sql);

$khalti_secret_key = 'live_secret_key_here'; // Replace with your live Khalti Secret Key
$khalti_public_key = 'live_public_key_here'; // Replace with your live Khalti Public Key
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