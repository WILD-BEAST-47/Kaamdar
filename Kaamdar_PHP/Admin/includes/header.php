<?php
include('../includes/session.php');
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    header("Location: login.php");
    exit;
}

// Check session timeout (30 minutes)
$timeout = 1800; // 30 minutes in seconds
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Prevent session fixation
if(!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if(time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Debug session
error_log("Session data: " . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo TITLE ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custome CSS -->
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
            padding-top: 60px;
        }

        .top-navbar {
            background: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
            height: 60px;
        }
        
        .top-navbar .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
            padding: 0;
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 60px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding: 1rem 1rem;
            position: fixed;
            top: 60px;
            left: 0;
            width: 250px;
        }

        .sidebar .nav-link {
            color: var(--text-color);
            padding: 0.8rem 1.2rem;
            margin: 0.3rem 0;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 1rem;
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
            margin-right: 1rem;
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 0.5rem;
            background: var(--light-bg);
            min-height: calc(100vh - 60px);
            margin-left: 250px;
            margin-top: 60px;
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
        
        .btn-secondary {
            background: var(--secondary-color);
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-secondary:hover {
            background: rgba(51, 51, 51, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-control {
            border-radius: 4px;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(243, 150, 28, 0.1);
        }

        .alert {
            border-radius: 4px;
            border: none;
            padding: 1rem;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .page-title {
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            padding-bottom: 0.25rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .d-flex.justify-content-between {
            margin-bottom: 0.5rem;
            padding-top: 0.5rem;
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

        .badge-primary {
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
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-tools"></i> KaamDar
            </a>
        </div>
    </nav>

    <!-- Side Bar -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-sm-3 col-md-2 sidebar d-print-none">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'dashboard') { echo 'active'; } ?>" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'work') { echo 'active'; } ?>" href="work.php">
                                <i class="fas fa-tools"></i>
                                Work Order
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'request') { echo 'active'; } ?>" href="request.php">
                                <i class="fas fa-clipboard-list"></i>
                                Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'assets') { echo 'active'; } ?>" href="assets.php">
                                <i class="fas fa-box"></i>
                                Assets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'technician') { echo 'active'; } ?>" href="technician.php">
                                <i class="fas fa-user-cog"></i>
                                Technician
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'requesters') { echo 'active'; } ?>" href="requester.php">
                                <i class="fas fa-users"></i>
                                Requester
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'sellreport') { echo 'active'; } ?>" href="soldproductreport.php">
                                <i class="fas fa-chart-line"></i>
                                Sell Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'workreport') { echo 'active'; } ?>" href="workreport.php">
                                <i class="fas fa-chart-bar"></i>
                                Work Report
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if(PAGE == 'changepass') { echo 'active'; } ?>" href="changepass.php">
                                <i class="fas fa-key"></i>
                                Change Password
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-sm-9 col-md-10 ms-sm-auto main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="page-title"><?php echo TITLE; ?></h1>
                </div>