<?php
include('dbConnection.php');
session_start();

if (!isset($_SESSION['is_login'])) {
    if (isset($_REQUEST['rEmail'])) {
        $rEmail = mysqli_real_escape_string($conn, trim($_REQUEST['rEmail']));
        $rPassword = mysqli_real_escape_string($conn, trim($_REQUEST['rPassword']));

        // Fetch user data from the database
        $sql = "SELECT r_email, r_password FROM requesterlogin_tb WHERE r_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $rEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verify the password
            if (password_verify($rPassword, $row['r_password'])) {
                // Set session variables
                $_SESSION['is_login'] = true;
                $_SESSION['rEmail'] = $rEmail;
                // Redirect to Requester Profile Page
                echo "<script> location.href='RequesterProfile.php'; </script>";
                exit;
            } else {
                $msg = '<div class="alert alert-warning mt-2" role="alert"> Invalid Email or Password </div>';
            }
        } else {
            $msg = '<div class="alert alert-warning mt-2" role="alert"> Invalid Email or Password </div>';
        }
    }
} else {
    // If already logged in, redirect to Requester Profile Page
    echo "<script> location.href='../RequesterProfile.php'; </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to KaamDar</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="css/all.min.css">
    <style>
        .custom-margin {
            margin-top: 8vh;
        }
    </style>
</head>
<body>
    <div class="mb-3 text-center mt-5" style="font-size: 30px;">
        <i class="fas fa-stethoscope"></i>
        <span>KaamDar</span>
    </div>
    <p class="text-center" style="font-size: 20px;">
        <i class="fas fa-user-secret text-danger"></i>
        <span>Requester Area (Demo)</span>
    </p>
    <div class="container-fluid mb-5">
        <div class="row justify-content-center custom-margin">
            <div class="col-sm-6 col-md-4">
                <form action="" class="shadow-lg p-4" method="POST">
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <label for="email" class="pl-2 font-weight-bold">Email</label>
                        <input type="email" class="form-control" placeholder="Email" name="rEmail" required>
                    </div>
                    <div class="form-group">
                        <i class="fas fa-key"></i>
                        <label for="pass" class="pl-2 font-weight-bold">Password</label>
                        <input type="password" class="form-control" placeholder="Password" name="rPassword" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger mt-3 btn-block shadow-sm font-weight-bold">Login</button>
                    <?php if (isset($msg)) { echo $msg; } ?>
                </form>
                <div class="text-center">
                    <a class="btn btn-info mt-3 shadow-sm font-weight-bold" href="UserRegistration.php">Sign Up</a>
                    <a class="btn btn-info mt-3 shadow-sm font-weight-bold" href="index.php">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/all.min.js"></script>
</body>
</html>