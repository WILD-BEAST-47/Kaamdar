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
?>

<div class="col-sm-9 col-md-10">
    <div class="row mx-5 text-center">
        <div class="col-sm-4 mt-5">
            <div class="card text-white bg-primary mb-3" style="max-width: 18rem;">
                <div class="card-header">
                    <i class="fas fa-tools fa-2x mb-2"></i>
                    <h5>Requested Services</h5>
                </div>
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <?php
                        $sql = "SELECT COUNT(*) FROM submitrequest_tb WHERE requester_email = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $rEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = mysqli_fetch_row($result);
                        echo $row[0];
                        ?>
                    </h2>
                    <a class="btn btn-light" href="ServiceStatus.php">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 mt-5">
            <div class="card text-white bg-success mb-3" style="max-width: 18rem;">
                <div class="card-header">
                    <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                    <h5>Assigned Works</h5>
                </div>
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <?php
                        $sql = "SELECT COUNT(*) FROM assignwork_tb WHERE requester_email = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $rEmail);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = mysqli_fetch_row($result);
                        echo $row[0];
                        ?>
                    </h2>
                    <a class="btn btn-light" href="WorkOrder.php">View Details</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 mt-5">
            <div class="card text-white bg-info mb-3" style="max-width: 18rem;">
                <div class="card-header">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h5>Available Services</h5>
                </div>
                <div class="card-body">
                    <h2 class="card-title mb-4">
                        <?php
                        $sql = "SELECT COUNT(*) FROM technician_tb";
                        $result = $conn->query($sql);
                        $row = mysqli_fetch_row($result);
                        echo $row[0];
                        ?>
                    </h2>
                    <a class="btn btn-light" href="Technician.php">View Details</a>
                </div>
            </div>
        </div>
    </div>
    <div class="mx-5 mt-5">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header">
                <h3 class="text-center font-weight-light my-2">
                    <i class="fas fa-user-edit me-2"></i>Update Profile
                </h3>
            </div>
            <div class="card-body">
                <?php if(isset($msg)) { echo $msg; } ?>
                <form action="" method="POST" class="needs-validation" novalidate>
                    <div class="form-group mb-4">
                        <label for="rEmail" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="rEmail" name="rEmail" 
                                   value="<?php echo $rEmail; ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group mb-4">
                        <label for="rName" class="form-label">Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="rName" name="rName" 
                                   value="<?php echo $_SESSION['rName']; ?>" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="submit" name="nameupdate" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
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

<?php
include('includes/footer.php'); 
$conn->close();
?>