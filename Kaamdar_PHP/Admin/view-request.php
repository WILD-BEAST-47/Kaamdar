<?php
session_start();
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_admin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get request ID from URL
$request_id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) : 0;

if($request_id > 0) {
    // Get request details
    $sql = "SELECT * FROM submitrequest_tb WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $request = $result->fetch_assoc();
    } else {
        $msg = '<div class="alert alert-warning">Request not found!</div>';
    }
    $stmt->close();
} else {
    $msg = '<div class="alert alert-warning">Invalid request ID!</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request - KaamDar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f3961c;
            --secondary-color: #333;
            --accent-color: #f3961c;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --dark-bg: #333;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: white;
            color: var(--text-color);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 1rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: rgba(243, 150, 28, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(243, 150, 28, 0.2);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <a href="requests.php" class="btn btn-primary mb-3">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
                <h2 class="mb-0">Request Details</h2>
            </div>
        </div>

        <?php if(isset($msg)) echo $msg; ?>

        <?php if(isset($request)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Request Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Service Details</h6>
                        <p><strong>Request ID:</strong> #<?php echo str_pad($request['request_id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Service Type:</strong> <?php echo htmlspecialchars($request['request_info']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($request['request_desc']); ?></p>
                        <?php if($request['assign_tech']): ?>
                            <p><strong>Assigned Technician:</strong> <?php echo htmlspecialchars($request['assign_tech']); ?></p>
                            <p><strong>Assignment Date:</strong> <?php echo $request['assign_date']; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Requester Information</h6>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($request['requester_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($request['requester_email']); ?></p>
                        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($request['requester_mobile']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($request['requester_add1'] . ', ' . $request['requester_add2']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($request['requester_city']); ?></p>
                        <p><strong>State:</strong> <?php echo htmlspecialchars($request['requester_state']); ?></p>
                        <p><strong>ZIP:</strong> <?php echo htmlspecialchars($request['requester_zip']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 