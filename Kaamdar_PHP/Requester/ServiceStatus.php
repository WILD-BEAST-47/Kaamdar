<?php
define('TITLE', 'Service Status');
define('PAGE', 'ServiceStatus');
include('includes/header.php'); 
include('../dbConnection.php');

if($_SESSION['is_login']){
    $rEmail = $_SESSION['rEmail'];
} else {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
}
?>

<div class="col-sm-9 col-md-10 mt-5">
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-center mb-4">Service Request Status</h3>
            <?php
            // Get all service requests for the logged-in requester
            $sql = "SELECT s.*, a.assign_tech, a.assign_date, t.empName, t.empMobile, t.empEmail, t.empCity 
                    FROM submitrequest_tb s 
                    LEFT JOIN assignwork_tb a ON s.request_id = a.request_id 
                    LEFT JOIN technician_tb t ON a.assign_tech = t.empid 
                    WHERE s.requester_email = ? 
                    ORDER BY s.request_date DESC, s.request_id DESC";
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
                    if($row['request_id'] == 93) {
                        error_log('Request #93 - assign_tech: ' . $row['assign_tech']);
                        error_log('Request #93 - empName: ' . $row['empName']);
                        error_log('Request #93 - empMobile: ' . $row['empMobile']);
                    }
                    $status = $row['assign_tech'] ? "Assigned" : "Pending";
                    
                    echo '<tr>
                        <td>'.$row['request_id'].'</td>
                        <td>'.$row['request_info'].'</td>
                        <td>'.$row['request_desc'].'</td>
                        <td>'.$row['requester_city'].'</td>
                        <td>'.$row['request_date'].'</td>
                        <td>'.$status.'</td>
                        <td>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#workDetailsModal" 
                                data-request-id="'.$row['request_id'].'"
                                data-request-info="'.htmlspecialchars($row['request_info']).'"
                                data-request-desc="'.htmlspecialchars($row['request_desc']).'"
                                data-request-date="'.$row['request_date'].'"
                                data-requester-city="'.htmlspecialchars($row['requester_city']).'"
                                data-tech-name="'.htmlspecialchars($row['empName'] ?? '').'"
                                data-tech-contact="'.htmlspecialchars($row['empMobile'] ?? '').'"
                                data-assign-date="'.($row['assign_date'] ?? '').'">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>';
                }
                echo '</tbody></table>';

                // Single modal for all requests
                echo '<div class="modal fade" id="workDetailsModal" tabindex="-1" aria-labelledby="workDetailsModalLabel" aria-hidden="true">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="workDetailsModalLabel">Work Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Request Information</h6>
                                    <p><strong>Request ID:</strong> <span id="modal-request-id"></span></p>
                                    <p><strong>Service Type:</strong> <span id="modal-request-info"></span></p>
                                    <p><strong>Description:</strong> <span id="modal-request-desc"></span></p>
                                    <p><strong>Request Date:</strong> <span id="modal-request-date"></span></p>
                                    <p><strong>City:</strong> <span id="modal-requester-city"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Technician Information</h6>
                                    <div id="tech-info">
                                        <p><strong>Name:</strong> <span id="modal-tech-name"></span></p>
                                        <p><strong>Contact:</strong> <span id="modal-tech-contact"></span></p>
                                        <p><strong>Assigned Date:</strong> <span id="modal-assign-date"></span></p>
                                    </div>
                                    <div id="no-tech-info" style="display: none;">
                                        <p>No technician assigned yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>';

                // Add JavaScript to handle modal content
                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const modal = document.getElementById("workDetailsModal");
                    
                    // Remove the mousemove event listener that was blocking clicks
                    modal.addEventListener("show.bs.modal", function(event) {
                        const button = event.relatedTarget;
                        
                        // Get data from button attributes
                        const requestId = button.getAttribute("data-request-id");
                        const requestInfo = button.getAttribute("data-request-info");
                        const requestDesc = button.getAttribute("data-request-desc");
                        const requestDate = button.getAttribute("data-request-date");
                        const requesterCity = button.getAttribute("data-requester-city");
                        const techName = button.getAttribute("data-tech-name");
                        const techContact = button.getAttribute("data-tech-contact");
                        const assignDate = button.getAttribute("data-assign-date");
                        
                        // Update modal content
                        document.getElementById("modal-request-id").textContent = "#" + requestId;
                        document.getElementById("modal-request-info").textContent = requestInfo;
                        document.getElementById("modal-request-desc").textContent = requestDesc;
                        document.getElementById("modal-request-date").textContent = requestDate;
                        document.getElementById("modal-requester-city").textContent = requesterCity;
                        
                        // Show/hide technician info based on assignment
                        const techInfo = document.getElementById("tech-info");
                        const noTechInfo = document.getElementById("no-tech-info");
                        
                        if (techName && techContact) {
                            document.getElementById("modal-tech-name").textContent = techName;
                            document.getElementById("modal-tech-contact").textContent = techContact;
                            document.getElementById("modal-assign-date").textContent = assignDate;
                            techInfo.style.display = "block";
                            noTechInfo.style.display = "none";
                        } else {
                            techInfo.style.display = "none";
                            noTechInfo.style.display = "block";
                        }
                    });
                });
                </script>';
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
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex !important;
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
    margin: 0 auto;
    transform: none !important;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1rem;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    flex-shrink: 0;
}

/* Remove Bootstrap's default modal backdrop */
.modal-backdrop {
    display: none !important;
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

/* Prevent modal movement */
.modal-dialog {
    margin: 0;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* Ensure buttons are clickable */
.btn {
    pointer-events: auto !important;
    cursor: pointer;
}

/* Ensure modal content is clickable */
.modal-content {
    pointer-events: auto !important;
}
</style>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 