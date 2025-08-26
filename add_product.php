<?php
  $page_title = 'Add Product';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(2);
  $all_categories = find_all('categories');
  $all_photo = find_all('media');
  
?>
<?php
 if(isset($_POST['add_product'])){
   $req_fields = array('product-title','product-categorie','product-quantity');
   validate_fields($req_fields);
   if(empty($errors)){
     $p_name  = remove_junk($db->escape($_POST['product-title']));
     $p_cat   = remove_junk($db->escape($_POST['product-categorie']));
     $p_qty   = remove_junk($db->escape($_POST['product-quantity']));
     if (is_null($_POST['product-photo']) || $_POST['product-photo'] === "") {
       $media_id = '0';
     } else {
       $media_id = remove_junk($db->escape($_POST['product-photo']));
     }
     $date    = make_date();
     $query  = "INSERT INTO products (";
     $query .=" name,quantity,categorie_id,media_id,date";
     $query .=") VALUES (";
     $query .=" '{$p_name}', '{$p_qty}', '{$p_cat}', '{$media_id}', '{$date}'";
     $query .=")";
     $query .=" ON DUPLICATE KEY UPDATE name='{$p_name}'";

       if($db->query($query)){
        // Get the last inserted product ID
        $product_id = $db->insert_id();



        if (!$product_id) {
          $session->msg('d','Error: Product ID not retrieved!');
          redirect('add_product.php', false);
        }


        // Add stock items based on quantity
        $qty = (int)$p_qty;
        for($i = 1; $i <= $qty; $i++) {
          $stock_number = $p_name . "-" . sprintf("%03d", $i); // Creates stock numbers like "Laptop-001"
          $stock_query = "INSERT INTO stock (product_id, stock_number, status_id) VALUES ({$product_id}, '{$stock_number}', 1)";
          $db->query($stock_query);
        }

        $user_id = $_SESSION['user_id'];
          // Call the log_recent_action function to log the product addition
        log_recent_action($user_id, "Added new product: $p_name");

        $session->msg('s',"Product added with {$qty} stock items");
        redirect('add_product.php', false);
      }
     else {
       $session->msg('d',' Sorry failed to added!');
       redirect('product.php', false);
     }

   } else{
     $session->msg("d", $errors);
     redirect('add_product.php',false);
   }

 }

?>
<?php include_once('layouts/header.php'); ?>
<div class="col-12 mb-3">
      <a href="javascript:history.back()" class="btn main-btn">
        <i class="bi bi-arrow-left me-1"></i> Back
      </a>
  </div>
<div class="container-fluid mt-4">
  <?php echo display_msg($msg); ?>

  <div class="card shadow-sm rounded col-md-8 mx-auto">
    <div class="card-header cont-head">
      <h5 class="mb-0">
        <i class="bi bi-plus-square me-2" style="color: var(--secondary)"></i> Add New Product
      </h5>
    </div>
    <div class="card-body">
      <form method="post" action="add_product.php" class="needs-validation" novalidate>
        
        <!-- Product Title -->
        <div class="mb-3">
          <label for="product-title" class="form-label">Product Title</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
            <input type="text" id="product-title" name="product-title" class="form-control" placeholder="Enter product title" required>
          </div>
        </div>

        <!-- Category & Photo -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="product-categorie" class="form-label">Product Category</label>
            <select class="form-select" name="product-categorie" id="product-categorie" required>
              <option value="">Select category</option>
              <?php foreach ($all_categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>"><?php echo $cat['name']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label for="product-photo" class="form-label">Product Photo</label>
            <select class="form-select" name="product-photo" id="product-photo">
              <option value="">Select photo</option>
              <?php foreach ($all_photo as $photo): ?>
                <option value="<?php echo (int)$photo['id']; ?>"><?php echo $photo['file_name']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Quantity -->
        <div class="mb-3 col-md-4">
          <label for="product-quantity" class="form-label">Quantity</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-basket"></i></span>
            <input type="number" id="product-quantity" name="product-quantity" class="form-control" placeholder="Enter quantity" required>
          </div>
        </div>

        <!-- Submit -->
        <div class="d-flex justify-content-end mt-4">
          <button type="submit" name="add_product" class="btn secondary-btn">
            <i class="fas fa-plus-circle me-1"></i> Add Product
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<?php include_once('layouts/footer.php'); ?>
