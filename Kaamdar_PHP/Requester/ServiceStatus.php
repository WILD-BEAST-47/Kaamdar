<?php
define('TITLE', 'Service Status');
define('PAGE', 'ServiceStatus');
include('includes/header.php'); 
include('../dbConnection.php');

if($_SESSION['is_login']){
    $rEmail = $_SESSION['rEmail'];
} else {
    echo "<script> location.href='RequesterLogin.php'; </script>";
}
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-center mb-4">Service Request Status</h3>
            <?php
            // Get all service requests for the logged-in requester
            $sql = "SELECT s.*, a.assign_date, a.assign_tech, t.empName, t.empMobile 
                    FROM submitrequest_tb s 
                    LEFT JOIN assignwork_tb a ON s.request_id = a.request_id 
                    LEFT JOIN technician_tb t ON a.assign_tech = t.empid 
                    WHERE s.requester_email = ? 
                    ORDER BY s.request_date DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $rEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0) {
                echo '<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Service Info</th>
                            <th>Description</th>
                            <th>City</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                
                while($row = $result->fetch_assoc()) {
                    $status = $row['assign_tech'] ? "Assigned" : "Pending";
                    
                    echo '<tr>
                        <td>'.$row['request_id'].'</td>
                        <td>'.$row['request_info'].'</td>
                        <td>'.$row['request_desc'].'</td>
                        <td>'.$row['requester_city'].'</td>
                        <td>'.$row['request_date'].'</td>
                        <td>'.$status.'</td>
                        <td>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#workDetailsModal'.$row['request_id'].'">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>';
                }
                echo '</tbody></table>';

                // Add modals for each request
                $result->data_seek(0);
                while($row = $result->fetch_assoc()) {
                    echo '<div class="modal fade" id="workDetailsModal'.$row['request_id'].'" tabindex="-1" aria-labelledby="workDetailsModalLabel'.$row['request_id'].'" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="workDetailsModalLabel'.$row['request_id'].'">
                                        Work Details - Request #'.$row['request_id'].'
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Request Information</h6>
                                            <p><strong>Service Type:</strong> '.$row['request_info'].'</p>
                                            <p><strong>Description:</strong> '.$row['request_desc'].'</p>
                                            <p><strong>Request Date:</strong> '.$row['request_date'].'</p>
                                            <p><strong>City:</strong> '.$row['requester_city'].'</p>
                                        </div>';
                    
                    if($row['assign_tech']) {
                        echo '<div class="col-md-6">
                                <h6>Technician Information</h6>
                                <p><strong>Name:</strong> '.$row['empName'].'</p>
                                <p><strong>Contact:</strong> '.$row['empMobile'].'</p>
                                <p><strong>Assigned Date:</strong> '.$row['assign_date'].'</p>
                            </div>';
                    }
                    
                    echo '</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">No Service Requests Found</h4>
                    <p>You haven\'t submitted any service requests yet.</p>
                    <hr>
                    <p class="mb-0">Click <a href="SubmitRequest.php">here</a> to submit a new service request.</p>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 