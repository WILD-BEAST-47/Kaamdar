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
            $sql = "SELECT * FROM submitrequest_tb WHERE requester_email = ?";
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
                    // Check if request is assigned
                    $assign_sql = "SELECT * FROM assignwork_tb WHERE request_id = ?";
                    $assign_stmt = $conn->prepare($assign_sql);
                    $assign_stmt->bind_param("i", $row['request_id']);
                    $assign_stmt->execute();
                    $assign_result = $assign_stmt->get_result();
                    
                    $status = "Pending";
                    if($assign_result->num_rows > 0) {
                        $status = "Assigned";
                    }
                    
                    echo '<tr>
                        <td>'.$row['request_id'].'</td>
                        <td>'.$row['request_info'].'</td>
                        <td>'.$row['request_desc'].'</td>
                        <td>'.$row['requester_city'].'</td>
                        <td>'.$row['request_date'].'</td>
                        <td>'.$status.'</td>
                        <td>
                            <a href="ViewAssignedWork.php?id='.$row['request_id'].'" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>';
                }
                echo '</tbody></table>';
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