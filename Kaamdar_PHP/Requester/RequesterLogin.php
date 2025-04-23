<?php
session_start();
include('../dbConnection.php');

if(isset($_POST['login'])) {
    $rEmail = $_POST['rEmail'];
    $rPassword = $_POST['rPassword'];
    
    $sql = "SELECT r_email, r_password FROM requesterlogin_tb WHERE r_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if(password_verify($rPassword, $row['r_password'])) {
            $_SESSION['is_login'] = true;
            $_SESSION['rEmail'] = $rEmail;
            echo "<script> location.href='RequesterDashboard.php'; </script>";
            exit;
        } else {
            $msg = '<div class="alert alert-warning mt-2">Invalid Password</div>';
        }
    } else {
        $msg = '<div class="alert alert-warning mt-2">Invalid Email</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requester Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .custom-margin {
            margin-top: 8vh;
        }
        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .custom-bg {
            background: #f3961c;
        }
    </style>
</head>
<body>
    <div class="container-fluid custom-margin">
        <div class="row justify-content-center">
            <div class="col-sm-6 col-md-4">
                <div class="form-container">
                    <h3 class="text-center mb-4">Requester Login</h3>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="rEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="rEmail" name="rEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="rPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="rPassword" name="rPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 custom-bg" name="login">Login</button>
                        <?php if(isset($msg)) { echo $msg; } ?>
                    </form>
                    <div class="text-center mt-3">
                        <a href="../index.php" class="btn btn-secondary">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 