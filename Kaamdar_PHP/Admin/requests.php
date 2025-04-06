<?php
session_start();
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_admin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Handle status updates
if(isset($_POST['update_status'])) {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_SANITIZE_NUMBER_INT);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    $sql = "UPDATE submitrequest_tb SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $request_id);
    
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
    <title>Service Requests - KaamDar</title>
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
        
        .badge-warning {
            background: rgba(243, 150, 28, 0.1);
            color: var(--primary-color);
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #1565c0;
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
                            <a class="nav-link active" href="requests.php">
                                <i class="fas fa-clipboard-list"></i> Service Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="technicians.php">
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
                    <h1 class="h2">Service Requests</h1>
                </div>

                <?php if(isset($msg)) echo $msg; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo isset($_GET['status']) && $_GET['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo isset($_GET['status']) && $_GET['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Service Type</label>
                                <select name="service_type" class="form-select">
                                    <option value="">All</option>
                                    <option value="Home Repairs" <?php echo isset($_GET['service_type']) && $_GET['service_type'] == 'Home Repairs' ? 'selected' : ''; ?>>Home Repairs</option>
                                    <option value="Construction" <?php echo isset($_GET['service_type']) && $_GET['service_type'] == 'Construction' ? 'selected' : ''; ?>>Construction</option>
                                    <option value="Cleaning" <?php echo isset($_GET['service_type']) && $_GET['service_type'] == 'Cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date Range</label>
                                <input type="date" name="date" class="form-control" value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="requests.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Service Type</th>
                                        <th>Description</th>
                                        <th>Preferred Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $where = "WHERE 1=1";
                                    $params = [];
                                    $types = "";

                                    if(isset($_GET['status']) && !empty($_GET['status'])) {
                                        $where .= " AND r.status = ?";
                                        $params[] = $_GET['status'];
                                        $types .= "s";
                                    }

                                    if(isset($_GET['service_type']) && !empty($_GET['service_type'])) {
                                        $where .= " AND r.request_info = ?";
                                        $params[] = $_GET['service_type'];
                                        $types .= "s";
                                    }

                                    if(isset($_GET['date']) && !empty($_GET['date'])) {
                                        $where .= " AND DATE(r.request_date) = ?";
                                        $params[] = $_GET['date'];
                                        $types .= "s";
                                    }

                                    $sql = "SELECT r.*, u.r_name as requester_name 
                                           FROM submitrequest_tb r 
                                           JOIN requesterlogin_tb u ON r.requester_email = u.r_email 
                                           $where 
                                           ORDER BY r.request_date DESC";
                                    
                                    $stmt = $conn->prepare($sql);
                                    if(!empty($params)) {
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    if($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>#" . str_pad($row['request_id'], 6, '0', STR_PAD_LEFT) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['request_info']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['request_desc']) . "</td>";
                                            echo "<td>" . $row['request_date'] . "</td>";
                                            echo "<td><span class='badge bg-" . 
                                                ($row['status'] == 'pending' ? 'warning' : 
                                                ($row['status'] == 'approved' ? 'success' : 
                                                ($row['status'] == 'rejected' ? 'danger' : 'info'))) . 
                                                "' id='status-badge-" . $row['request_id'] . "'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "<td>
                                                    <a href='view-request.php?id=" . $row['request_id'] . "' class='btn btn-sm btn-primary'>View</a>
                                                    <button type='button' class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#statusModal" . $row['request_id'] . "'>Update Status</button>
                                                  </td>";
                                            echo "</tr>";

                                            // Status Update Modal
                                            echo "<div class='modal fade' id='statusModal" . $row['request_id'] . "' tabindex='-1'>
                                                    <div class='modal-dialog'>
                                                        <div class='modal-content'>
                                                            <div class='modal-header'>
                                                                <h5 class='modal-title'>Update Request Status</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                            </div>
                                                            <form id='statusForm" . $row['request_id'] . "'>
                                                                <div class='modal-body'>
                                                                    <input type='hidden' name='request_id' value='" . $row['request_id'] . "'>
                                                                    <div class='mb-3'>
                                                                        <label class='form-label'>Status</label>
                                                                        <select name='status' class='form-select' required>
                                                                            <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                                                                            <option value='approved' " . ($row['status'] == 'approved' ? 'selected' : '') . ">Approved</option>
                                                                            <option value='rejected' " . ($row['status'] == 'rejected' ? 'selected' : '') . ">Rejected</option>
                                                                            <option value='completed' " . ($row['status'] == 'completed' ? 'selected' : '') . ">Completed</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class='modal-footer'>
                                                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                                    <button type='submit' class='btn btn-primary'>Update Status</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                  </div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center'>No requests found</td></tr>";
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle status update forms
            const statusForms = document.querySelectorAll('form[id^="statusForm"]');
            statusForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const requestId = formData.get('request_id');
                    const status = formData.get('status');
                    
                    // Send AJAX request
                    fetch('update-status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the status badge
                            const badge = document.getElementById('status-badge-' + requestId);
                            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                            
                            // Update badge class based on status
                            badge.className = 'badge bg-' + 
                                (status === 'pending' ? 'warning' : 
                                (status === 'approved' ? 'success' : 
                                (status === 'rejected' ? 'danger' : 'info')));
                            
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal' + requestId));
                            modal.hide();
                            
                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                Status updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                            document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.card'));
                            
                            // Remove alert after 3 seconds
                            setTimeout(() => {
                                alertDiv.remove();
                            }, 3000);
                        } else {
                            // Show error message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                Error updating status: ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;
                            document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.card'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            An error occurred while updating the status.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.querySelector('.main-content').insertBefore(alertDiv, document.querySelector('.card'));
                    });
                });
            });
        });
    </script>
</body>
</html> 