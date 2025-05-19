<?php
include('../includes/session.php');
include('../dbConnection.php');

// Check if requester is logged in
if(!isset($_SESSION['is_login'])) {
    header("Location: RequesterLogin.php");
    exit;
}

// Check session timeout (30 minutes)
$timeout = 1800; // 30 minutes in seconds
if(isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: RequesterLogin.php");
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
   min-height: 100vh;
  }
  
  /* Fixed Top Navbar */
  .top-navbar {
   background: var(--primary-color) !important;
   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
   padding: 0.5rem 1rem;
   height: 60px;
   position: fixed;
   top: 0;
   left: 0;
   right: 0;
   z-index: 1030;
  }
  
  .top-navbar .navbar-brand {
   color: white !important;
   font-weight: 600;
   font-size: 1.5rem;
   padding: 0;
  }

  /* Fixed Sidebar */
  .sidebar {
   background: white;
   min-height: calc(100vh - 60px);
   box-shadow: 2px 0 5px rgba(0,0,0,0.1);
   padding: 1rem 1rem;
   position: fixed;
   top: 60px;
   left: 0;
   width: 250px;
   z-index: 1020;
   overflow-y: auto;
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
  
  /* Main Content Area */
  .main-content {
   padding: 1.5rem;
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
   margin-bottom: 2rem;
   padding-bottom: 1rem;
   border-bottom: 2px solid var(--primary-color);
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

  /* Responsive Adjustments */
  @media (max-width: 768px) {
   .sidebar {
    width: 100%;
    position: relative;
    top: 0;
    min-height: auto;
   }
   
   .main-content {
    margin-left: 0;
    margin-top: 60px;
   }
  }
 </style>
</head>

<body>
 <!-- Top Navbar -->
 <nav class="navbar navbar-expand-lg top-navbar">
  <div class="container-fluid">
   <a class="navbar-brand" href="RequesterDashboard.php">
    <i class="fas fa-tools"></i> KaamDar
   </a>
   <div class="ms-auto d-flex align-items-center">
    <span class="text-white me-3"><?php echo $_SESSION['rName']; ?></span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">
     <i class="fas fa-sign-out-alt"></i> Logout
    </a>
   </div>
  </div>
 </nav>

 <!-- Side Bar -->
 <div class="container-fluid">
  <div class="row">
   <nav class="col-sm-3 col-md-2 sidebar">
    <div class="sidebar-sticky">
     <ul class="nav flex-column">
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'dashboard') { echo 'active'; } ?>" href="RequesterDashboard.php">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'servicestatus') { echo 'active'; } ?>" href="ServiceStatus.php">
        <i class="fas fa-clipboard-list"></i>
        Service Status
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'submitrequest') { echo 'active'; } ?>" href="SubmitRequest.php">
        <i class="fas fa-plus-circle"></i>
        Submit Request
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'products') { echo 'active'; } ?>" href="products.php">
        <i class="fas fa-shopping-bag"></i>
        Products
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'cart') { echo 'active'; } ?>" href="cart.php">
        <i class="fas fa-shopping-cart"></i>
        Cart
        <?php
        // Show cart count if items exist
        if(isset($_SESSION['r_login_id'])) {
            $cart_sql = "SELECT COUNT(*) as count FROM shopping_cart_tb WHERE user_id = ?";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("i", $_SESSION['r_login_id']);
            $cart_stmt->execute();
            $cart_result = $cart_stmt->get_result();
            $cart_count = $cart_result->fetch_assoc()['count'];
            if($cart_count > 0) {
                echo '<span class="badge bg-primary ms-2">' . $cart_count . '</span>';
            }
        }
        ?>
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'myorders') { echo 'active'; } ?>" href="my-orders.php">
        <i class="fas fa-list"></i>
        My Orders
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'requesterprofile') { echo 'active'; } ?>" href="RequesterProfile.php">
        <i class="fas fa-user"></i>
        Profile
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php if(PAGE == 'changepass') { echo 'active'; } ?>" href="ChangePass.php">
        <i class="fas fa-key"></i>
        Change Password
       </a>
      </li>
     </ul>
    </div>
   </nav>

   <!-- Main Content -->
   <main class="col-sm-9 col-md-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
     <h1 class="h2"><?php echo TITLE; ?></h1>
    </div>