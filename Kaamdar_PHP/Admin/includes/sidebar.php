<?php
// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    header("Location: login.php");
    exit;
}
?>
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
    </div>
</div> 