<?php
session_start();
include('../dbConnection.php');

// Check if user is logged in
if(!isset($_SESSION['is_login'])) {
    echo "<script> location.href='../RequesterLogin.php'; </script>";
    exit;
}

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
 <title>KaamDar - Service Management System</title>
 <!-- Bootstrap CSS -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

 <!-- Font Awesome CSS -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
  }
  
  .top-navbar {
   background: white !important;
   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
   padding: 1rem;
  }
  
  .top-navbar .navbar-brand {
   color: var(--primary-color) !important;
   font-weight: 600;
   font-size: 1.5rem;
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
 </style>
</head>

<body>
 <!-- Top Navbar -->
 <nav class="navbar navbar-expand-lg top-navbar fixed-top">
  <div class="container-fluid">
   <a class="navbar-brand" href="RequesterProfile.php">
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
       <a class="nav-link <?php echo (PAGE == 'RequesterProfile') ? 'active' : ''; ?>" href="RequesterProfile.php">
        <i class="fas fa-user"></i> Profile
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php echo (PAGE == 'SubmitRequest') ? 'active' : ''; ?>" href="SubmitRequest.php">
        <i class="fas fa-tools"></i> Submit Request
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php echo (PAGE == 'ServiceStatus') ? 'active' : ''; ?>" href="ServiceStatus.php">
        <i class="fas fa-clock"></i> Service Status
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php echo (PAGE == 'products') ? 'active' : ''; ?>" href="products.php">
        <i class="fas fa-shopping-bag"></i> Products
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php echo (PAGE == 'Cart') ? 'active' : ''; ?>" href="cart.php">
        <i class="fas fa-shopping-cart"></i> Cart
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
       <a class="nav-link <?php echo (PAGE == 'MyOrders') ? 'active' : ''; ?>" href="my-orders.php">
        <i class="fas fa-box"></i> My Orders
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link <?php echo (PAGE == 'ChangePassword') ? 'active' : ''; ?>" href="ChangePassword.php">
        <i class="fas fa-key"></i> Change Password
       </a>
      </li>
      <li class="nav-item">
       <a class="nav-link" href="../logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
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