<?php    
define('TITLE', 'Edit Product');
define('PAGE', 'Edit Product');
include('includes/header.php'); 
include('../dbConnection.php');

// Check if admin is logged in
if(!isset($_SESSION['is_adminlogin'])) {
    echo "<script> location.href='login.php'; </script>";
    exit;
}

// Get product details
if(isset($_REQUEST['id'])) {
    $pid = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "SELECT * FROM assets_tb WHERE pid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}

// Handle form submission
if(isset($_REQUEST['psubmit'])) {
    // Validate input
    $pname = trim($_REQUEST['pname']);
    $pdop = trim($_REQUEST['pdop']);
    $pava = trim($_REQUEST['pava']);
    $ptotal = trim($_REQUEST['ptotal']);
    $poriginalcost = trim($_REQUEST['poriginalcost']);
    $psellingcost = trim($_REQUEST['psellingcost']);
    $description = trim($_REQUEST['description']);

    // Handle image upload
    $image_url = $row['image_url']; // Keep existing image by default
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $upload_dir = '../assets/images/products/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if(move_uploaded_file($_FILES['product_image']['tmp_name'], $target_path)) {
                // Delete old image if exists
                if($row['image_url'] && file_exists('../' . $row['image_url'])) {
                    unlink('../' . $row['image_url']);
                }
                $image_url = 'assets/images/products/' . $new_filename;
            } else {
                $msg = '<div class="alert alert-warning">Failed to upload image</div>';
            }
        } else {
            $msg = '<div class="alert alert-warning">Invalid file type. Allowed types: ' . implode(', ', $allowed) . '</div>';
        }
    }

    // Update product
    $sql = "UPDATE assets_tb SET pname=?, pdop=?, pava=?, ptotal=?, poriginalcost=?, psellingcost=?, description=?, image_url=? WHERE pid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiddssi", $pname, $pdop, $pava, $ptotal, $poriginalcost, $psellingcost, $description, $image_url, $pid);
    
    if($stmt->execute()) {
        $msg = '<div class="alert alert-success">Product Updated Successfully</div>';
    } else {
        $msg = '<div class="alert alert-danger">Unable to Update Product</div>';
    }
    $stmt->close();
}
?>
<div class="col-sm-6 mt-5  mx-3 jumbotron">
  <h3 class="text-center">Update Product Details</h3>
  <?php
 if(isset($_REQUEST['view'])){
  $sql = "SELECT * FROM assets_tb WHERE pid = {$_REQUEST['id']}";
 $result = $conn->query($sql);
 $row = $result->fetch_assoc();
 }
 ?>
  <form action="" method="POST">
    <div class="form-group">
      <label for="pid">Product ID</label>
      <input type="text" class="form-control" id="pid" name="pid" value="<?php if(isset($row['pid'])) {echo $row['pid']; }?>"
        readonly>
    </div>
    <div class="form-group">
      <label for="pname">Name</label>
      <input type="text" class="form-control" id="pname" name="pname" value="<?php if(isset($row['pname'])) {echo $row['pname']; }?>">
    </div>
    <div class="form-group">
      <label for="pdop">DOP</label>
      <input type="date" class="form-control" id="pdop" name="pdop" value="<?php if(isset($row['pdop'])) {echo $row['pdop']; }?>">
    </div>
    <div class="form-group">
      <label for="pava">Available</label>
      <input type="text" class="form-control" id="pava" name="pava" value="<?php if(isset($row['pava'])) {echo $row['pava']; }?>"
        onkeypress="isInputNumber(event)">
    </div>
    <div class="form-group">
      <label for="ptotal">Total</label>
      <input type="text" class="form-control" id="ptotal" name="ptotal" value="<?php if(isset($row['ptotal'])) {echo $row['ptotal']; }?>"
        onkeypress="isInputNumber(event)">
    </div>
    <div class="form-group">
      <label for="poriginalcost">Original Cost Each</label>
      <input type="text" class="form-control" id="poriginalcost" name="poriginalcost" value="<?php if(isset($row['poriginalcost'])) {echo $row['poriginalcost']; }?>"
        onkeypress="isInputNumber(event)">
    </div>
    <div class="form-group">
      <label for="psellingcost">Selling Price Each</label>
      <input type="text" class="form-control" id="psellingcost" name="psellingcost" value="<?php if(isset($row['psellingcost'])) {echo $row['psellingcost']; }?>"
        onkeypress="isInputNumber(event)">
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-danger" id="psubmit" name="psubmit">Update</button>
      <a href="assets.php" class="btn btn-secondary">Close</a>
    </div>
    <?php if(isset($msg)) {echo $msg; } ?>
  </form>
</div>
<!-- Only Number for input fields -->
<script>
  function isInputNumber(evt) {
    var ch = String.fromCharCode(evt.which);
    if (!(/[0-9]/.test(ch))) {
      evt.preventDefault();
    }
  }
</script>
<?php
include('includes/footer.php'); 
?>