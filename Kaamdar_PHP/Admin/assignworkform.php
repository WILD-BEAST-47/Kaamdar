<?php
if(isset($_REQUEST['view'])){
    $sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_REQUEST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

if(isset($_REQUEST['assign'])){
    // Get form data
    $request_id = $_REQUEST['request_id'];
    $technician_id = $_REQUEST['technician_id'];
    $assigned_date = $_REQUEST['assigned_date'];

    // Get request details
    $req_sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
    $req_stmt = $conn->prepare($req_sql);
    $req_stmt->bind_param("i", $request_id);
    $req_stmt->execute();
    $req_result = $req_stmt->get_result();
    $request = $req_result->fetch_assoc();

    // Get technician details
    $tech_sql = "SELECT * FROM technician_tb WHERE empid = ?";
    $tech_stmt = $conn->prepare($tech_sql);
    $tech_stmt->bind_param("i", $technician_id);
    $tech_stmt->execute();
    $tech_result = $tech_stmt->get_result();
    $technician = $tech_result->fetch_assoc();

    // Insert into assignwork_tb
    $insert_sql = "INSERT INTO assignwork_tb (request_id, request_info, request_desc, requester_name, requester_add1, requester_add2, requester_city, requester_state, requester_zip, requester_email, requester_mobile, assign_tech, assign_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("isssssssissss", 
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

    if($insert_stmt->execute()){
        // Send email to technician
        $tech_subject = "New Work Assignment - KaamDar";
        $tech_message = "Dear " . $technician['empName'] . ",\n\n";
        $tech_message .= "You have been assigned a new work request:\n\n";
        $tech_message .= "Request Details:\n";
        $tech_message .= "Request ID: " . $request['request_id'] . "\n";
        $tech_message .= "Service: " . $request['request_info'] . "\n";
        $tech_message .= "Description: " . $request['request_desc'] . "\n";
        $tech_message .= "Location: " . $request['requester_city'] . "\n";
        $tech_message .= "Assigned Date: " . $assigned_date . "\n\n";
        $tech_message .= "Please log in to your account for more details.\n\n";
        $tech_message .= "Best regards,\nKaamDar Team";
        
        mail($technician['empEmail'], $tech_subject, $tech_message);

        // Send email to requester
        $user_subject = "Work Request Update - KaamDar";
        $user_message = "Dear " . $request['requester_name'] . ",\n\n";
        $user_message .= "Your work request has been assigned to a technician:\n\n";
        $user_message .= "Request Details:\n";
        $user_message .= "Request ID: " . $request['request_id'] . "\n";
        $user_message .= "Service: " . $request['request_info'] . "\n";
        $user_message .= "Technician Name: " . $technician['empName'] . "\n";
        $user_message .= "Assigned Date: " . $assigned_date . "\n\n";
        $user_message .= "The technician will contact you shortly.\n\n";
        $user_message .= "Best regards,\nKaamDar Team";
        
        mail($request['requester_email'], $user_subject, $user_message);

        // Delete from submitrequest_tb
        $delete_sql = "DELETE FROM submitrequest_tb WHERE request_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $request_id);
        $delete_stmt->execute();

        echo "<script>location.href='work.php';</script>";
    }
}
?>

<!-- Work Assignment Form -->
<div class="modal fade" id="assignWorkModal" tabindex="-1" aria-labelledby="assignWorkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignWorkModalLabel">Assign Work</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="request_id" value="<?php if(isset($row['request_id'])) {echo $row['request_id'];} ?>">
                    
                    <div class="mb-3">
                        <label for="technician_id" class="form-label">Select Technician</label>
                        <select class="form-select" id="technician_id" name="technician_id" required>
                            <option value="">Choose a technician...</option>
                            <?php
                            $tech_sql = "SELECT * FROM technician_tb WHERE status = 'active' ORDER BY empName ASC";
                            $tech_result = $conn->query($tech_sql);
                            while($tech = $tech_result->fetch_assoc()){
                                echo '<option value="'.$tech['empid'].'">'.$tech['empName'].' - '.$tech['empCity'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="assigned_date" class="form-label">Assigned Date</label>
                        <input type="date" class="form-control" id="assigned_date" name="assigned_date" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="assign">
                            <i class="fas fa-user-plus me-1"></i> Assign Work
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.modal-header {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    background-color: white;
}

.modal-title {
    color: #333;
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    color: #555;
}

.form-control, .form-select {
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #f3961c;
    box-shadow: 0 0 0 0.2rem rgba(243, 150, 28, 0.25);
}

.btn-primary {
    background-color: #f3961c;
    border-color: #f3961c;
}

.btn-primary:hover {
    background-color: #e08a19;
    border-color: #e08a19;
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.1);
}
</style>

<!-- Bootstrap Modal JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>