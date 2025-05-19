<?php
session_start();
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_admin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Handle work assignment
if(isset($_POST['assign_work'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $technician_id = filter_input(INPUT_POST, 'technician_id', FILTER_SANITIZE_NUMBER_INT);
    $assigned_date = filter_input(INPUT_POST, 'assigned_date', FILTER_SANITIZE_STRING);

    // Start transaction
    $conn->begin_transaction();

    try {
        // First check if the request is already assigned
        $check_sql = "SELECT * FROM assigned_works WHERE request_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if($check_result->num_rows > 0) {
            $msg = '<div class="alert alert-warning">This request has already been assigned!</div>';
        } else {
            // Get technician details
            $tech_sql = "SELECT empName FROM technician_tb WHERE empid = ?";
            $tech_stmt = $conn->prepare($tech_sql);
            $tech_stmt->bind_param("i", $technician_id);
            $tech_stmt->execute();
            $tech_result = $tech_stmt->get_result();
            $tech_row = $tech_result->fetch_assoc();
            $tech_name = $tech_row['empName'];
            $tech_stmt->close();

            // Update the request with technician assignment
            $sql = "UPDATE requests SET assign_tech = ?, assign_date = ? WHERE request_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $tech_name, $assigned_date, $request_id);
            
            if($stmt->execute()) {
                $conn->commit();
                $msg = '<div class="alert alert-success">Work assigned successfully!</div>';
            } else {
                throw new Exception("Error assigning work");
            }
            $stmt->close();
        }
        $check_stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        $msg = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get request details if request_id is provided
$request_details = null;
if(isset($_GET['request_id'])) {
    $request_id = filter_input(INPUT_GET, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM requests WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $request_details = $result->fetch_assoc();
    } else {
        $msg = '<div class="alert alert-warning">Request not found!</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Work - KaamDar</title>
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
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 60px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 2rem 1rem;
        }
        
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 0.8rem 1.2rem;
            margin: 0.3rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link:hover {
            color: var(--primary-color);
            background: rgba(243, 150, 28, 0.05);
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            color: var(--primary-color);
            background: rgba(243, 150, 28, 0.1);
            font-weight: 500;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.8rem;
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 2rem;
            background: var(--light-bg);
            min-height: calc(100vh - 60px);
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
        
        .form-control, .form-select {
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 4px;
            padding: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(243, 150, 28, 0.25);
        }
        
        .alert {
            border-radius: 4px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="requests.php">
                                <i class="fas fa-clipboard-list"></i> Service Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="technicians.php">
                                <i class="fas fa-user-cog"></i> Technicians
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="assign-work.php">
                                <i class="fas fa-tasks"></i> Assign Work
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Assign Work</h1>
                </div>

                <?php if(isset($msg)) echo $msg; ?>

                <!-- Request Selection Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Select Request</label>
                                <select name="request_id" class="form-select" required>
                                    <option value="">Select a request...</option>
                                    <?php
                                    $sql = "SELECT * FROM requests WHERE assign_tech IS NULL OR assign_tech = '' ORDER BY request_id DESC";
                                    $result = $conn->query($sql);
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $selected = isset($_GET['request_id']) && $_GET['request_id'] == $row['request_id'] ? 'selected' : '';
                                            echo "<option value='" . $row['request_id'] . "' $selected>
                                                    #" . str_pad($row['request_id'], 6, '0', STR_PAD_LEFT) . " - 
                                                    " . htmlspecialchars($row['request_info']) . " - 
                                                    " . htmlspecialchars($row['requester_name']) . "
                                                  </option>";
                                        }
                                    } else {
                                        echo "<option value='' disabled>No unassigned requests found</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Load Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if($request_details): ?>
                <!-- Request Details and Assignment Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Request Information</h6>
                                <p><strong>Request ID:</strong> #<?php echo str_pad($request_details['request_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                <p><strong>Service Type:</strong> <?php echo htmlspecialchars($request_details['request_info']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($request_details['request_desc']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Requester Information</h6>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($request_details['requester_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($request_details['requester_email']); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($request_details['requester_mobile']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($request_details['requester_add1'] . ', ' . $request_details['requester_add2']); ?></p>
                                <p><strong>City:</strong> <?php echo htmlspecialchars($request_details['requester_city']); ?></p>
                            </div>
                        </div>

                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="request_id" value="<?php echo $request_details['request_id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Select Technician</label>
                                <select name="technician_id" class="form-select" required>
                                    <option value="">Select a technician...</option>
                                    <?php
                                    $sql = "SELECT * FROM technician_tb WHERE status = 'active' ORDER BY empName ASC";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['empid'] . "'>" . 
                                             htmlspecialchars($row['empName']) . " - " . 
                                             htmlspecialchars($row['empCity']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Assigned Date</label>
                                <input type="date" class="form-control" name="assigned_date" required>
                            </div>

                            <button type="submit" name="assign_work" class="btn btn-primary">
                                <i class="fas fa-check"></i> Assign Work
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html> 