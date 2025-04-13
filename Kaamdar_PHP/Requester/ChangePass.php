<?php
define('TITLE', 'Change Password');
define('PAGE', 'ChangePass');
include('includes/header.php');
include('../dbConnection.php');

// Check if requester is logged in
if (!isset($_SESSION['is_login'])) {
    header("Location: RequesterLogin.php");
    exit();
}

$rEmail = $_SESSION['rEmail'];

if (isset($_REQUEST['passupdate'])) {
    if ($_REQUEST['rPassword'] == "") {
        $passmsg = '<div class="alert alert-warning col-sm-6 ml-5 mt-2">Fill All Fields</div>';
    } else {
        $rPass = $_REQUEST['rPassword'];
        $sql = "UPDATE requesterlogin_tb SET r_password = ? WHERE r_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $rPass, $rEmail);
        if ($stmt->execute()) {
            $passmsg = '<div class="alert alert-success col-sm-6 ml-5 mt-2">Updated Successfully</div>';
        } else {
            $passmsg = '<div class="alert alert-danger col-sm-6 ml-5 mt-2">Unable to Update</div>';
        }
    }
}
?>

<div class="col-sm-9 col-md-10">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Change Password</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="inputEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="inputEmail" value="<?php echo $rEmail; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="inputnewpassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="inputnewpassword" name="rPassword" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="passupdate">Update</button>
                            <button type="reset" class="btn btn-secondary">Reset</button>
                            <?php if(isset($passmsg)) { echo $passmsg; } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
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
    margin-top: 1rem;
}
</style>

<?php include('includes/footer.php'); ?> 