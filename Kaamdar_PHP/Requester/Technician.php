<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

// Get all technicians
$sql = "SELECT * FROM technician_tb ORDER BY empName ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Technicians - KaamDar</title>
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
        
        .technician-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            margin-bottom: 1.5rem;
        }
        
        .technician-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .technician-card .card-body {
            padding: 1.5rem;
        }
        
        .technician-card .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .technician-card .card-text {
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .technician-card .badge {
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        .modal-header .btn-close {
            color: white;
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
                <h2 class="text-center mb-4">Available Technicians</h2>
            </div>
        </div>
        
        <div class="row">
            <?php
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-md-6 col-lg-4">
                            <div class="technician-card">
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($row['empName']) . '</h5>
                                    <p class="card-text"><i class="fas fa-envelope"></i> ' . htmlspecialchars($row['empEmail']) . '</p>
                                    <p class="card-text"><i class="fas fa-phone"></i> ' . htmlspecialchars($row['empMobile']) . '</p>
                                    <p class="card-text"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($row['empCity']) . '</p>
                                    <span class="badge">Available</span>
                                </div>
                            </div>
                          </div>';
                }
            } else {
                echo '<div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No technicians available at the moment.
                        </div>
                      </div>';
            }
            ?>
        </div>
        
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="button" class="btn btn-primary" onclick="window.location.href='SubmitRequest.php'">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 