<?php
define('TITLE', 'Change Password');
define('PAGE', 'ChangePassword');
include('includes/header.php'); 
include('../dbConnection.php');

if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

$rEmail = $_SESSION['rEmail'];
$msg = '';

if(isset($_REQUEST['passupdate'])) {
    if(empty($_REQUEST['rPassword']) || empty($_REQUEST['rNewPassword']) || empty($_REQUEST['rConfirmPassword'])) {
        $msg = '<div class="alert alert-warning" role="alert">All fields are required</div>';
    } else {
        $rPassword = $_REQUEST['rPassword'];
        $rNewPassword = $_REQUEST['rNewPassword'];
        $rConfirmPassword = $_REQUEST['rConfirmPassword'];

        // Verify current password
        $sql = "SELECT r_password FROM requesterlogin_tb WHERE r_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $rEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if(password_verify($rPassword, $row['r_password'])) {
            if($rNewPassword === $rConfirmPassword) {
                // Update password
                $hashedPassword = password_hash($rNewPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE requesterlogin_tb SET r_password = ? WHERE r_email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $hashedPassword, $rEmail);
                
                if($stmt->execute()) {
                    $msg = '<div class="alert alert-success" role="alert">Password Updated Successfully</div>';
                } else {
                    $msg = '<div class="alert alert-danger" role="alert">Unable to Update Password</div>';
                }
            } else {
                $msg = '<div class="alert alert-warning" role="alert">New Password and Confirm Password do not match</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger" role="alert">Current Password is incorrect</div>';
        }
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header">
                    <h3 class="text-center font-weight-light my-2">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h3>
                </div>
                <div class="card-body">
                    <?php if(isset($msg)) { echo $msg; } ?>
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="form-group mb-4">
                            <label for="rPassword" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="rPassword" name="rPassword" required>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="rNewPassword" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="rNewPassword" name="rNewPassword" required>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="rConfirmPassword" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="rConfirmPassword" name="rConfirmPassword" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="passupdate" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Password
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