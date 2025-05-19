<?php
session_start();
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_admin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Handle technician addition
if(isset($_POST['add_technician'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);

    // Check if email already exists
    $check_sql = "SELECT empid FROM technician_tb WHERE empEmail = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if($check_result->num_rows > 0) {
        $msg = '<div class="alert alert-danger">Email already exists!</div>';
    } else {
        $sql = "INSERT INTO technician_tb (empName, empEmail, empMobile, empCity, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone, $city);
        
        if($stmt->execute()) {
            $msg = '<div class="alert alert-success">Technician added successfully!</div>';
        } else {
            $msg = '<div class="alert alert-danger">Error adding technician!</div>';
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Handle technician status update
if(isset($_POST['update_status'])) {
    $tech_id = filter_input(INPUT_POST, 'tech_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    $sql = "UPDATE technician_tb SET status = ? WHERE empid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $tech_id);
    
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success">Status updated successfully!</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error updating status!</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technicians - KaamDar</title>
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
        
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background: var(--light-bg);
            border-bottom: 2px solid #ddd;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .table tbody tr:hover {
            background: rgba(243, 150, 28, 0.05);
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.1);
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
                            <a class="nav-link active" href="technicians.php">
                                <i class="fas fa-user-cog"></i> Technicians
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="assign-work.php">
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
                    <h1 class="h2">Technicians</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTechnicianModal">
                        <i class="fas fa-plus"></i> Add New Technician
                    </button>
                </div>

                <?php if(isset($msg)) echo $msg; ?>

                <!-- Technicians Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM technician_tb ORDER BY empName ASC";
                                    $result = $conn->query($sql);
                                    
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>#" . str_pad($row['empid'], 4, '0', STR_PAD_LEFT) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['empName']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['empEmail']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['empMobile']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['empCity']) . "</td>";
                                            echo "<td><span class='badge bg-" . 
                                                ($row['status'] == 'active' ? 'success' : 'danger') . 
                                                "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td>
                                                    <button type='button' class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#statusModal" . $row['empid'] . "'>Update Status</button>
                                                  </td>";
                                            echo "</tr>";

                                            // Status Update Modal
                                            echo "<div class='modal fade' id='statusModal" . $row['empid'] . "' tabindex='-1'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title'>Update Technician Status</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                            </div>
                                                            <form method='POST'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='tech_id' value='" . $row['empid'] . "'>
                                                                    <div class='mb-3'>
                                                                        <label class='form-label'>Status</label>
                                                                        <select name='status' class='form-select' required>
                                                                            <option value='active' " . ($row['status'] == 'active' ? 'selected' : '') . ">Active</option>
                                                                            <option value='inactive' " . ($row['status'] == 'inactive' ? 'selected' : '') . ">Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' name='update_status' class='btn btn-primary'>Update Status</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>No technicians found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Technician Modal -->
    <div class="modal fade" id="addTechnicianModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Technician</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_technician" class="btn btn-primary">Add Technician</button>
                    </div>
                </form>
            </div>
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