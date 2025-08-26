<?php
  $page_title = 'Edit product';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(2);
?>
<?php
$product = find_by_id('products', (int)$_GET['id']);
$all_categories = find_all('categories');
$all_photo = find_all('media');

if (!$product) {
  $session->msg("d", "Missing product id.");
  redirect('product.php');
}
?>

<?php
if (isset($_POST['product'])) {
  $req_fields = array('product-title', 'product-categorie');
  validate_fields($req_fields);

  if (empty($errors)) {
    $p_name  = remove_junk($db->escape($_POST['product-title']));
    $p_cat   = (int)$_POST['product-categorie'];
    $product_id = (int)$_GET['id'];

    // Set media ID
    if (empty($_POST['product-photo'])) {
      $media_id = '0';
    } else {
      $media_id = remove_junk($db->escape($_POST['product-photo']));
    }

    // Only update name, category, and media_id — DO NOT touch quantity
    $query  = "UPDATE products SET ";
    $query .= "name = '{$p_name}', ";
    $query .= "categorie_id = '{$p_cat}', ";
    $query .= "media_id = '{$media_id}' ";
    $query .= "WHERE id = '{$product_id}'";

    $result = $db->query($query);

    if ($db->affected_rows() === 1) {
      $session->msg('s', "Product updated successfully");
      redirect('product.php', false);
    } else {
      $session->msg('d', 'Sorry, failed to update!');
      redirect('edit_product.php', false);
    }
  } else {
    $session->msg("d", $errors);
    exit();
  }
}
?>
<?php include_once('layouts/header.php'); ?>
<div class="col-12 mb-3">
      <a href="javascript:history.back()" class="btn main-btn">
        <i class="bi bi-arrow-left me-1"></i> Back
      </a>
</div>
<div class="container-field mt-4 col-md-8 mx-auto">
  <?php echo display_msg($msg); ?>

  <div class="card shadow-sm rounded">
    <div class="card-header cont-head">
      <h5 class="mb-0">
        <i class="bi bi-pencil-square me-2" style="color: var(--secondary)"></i> Edit Product
      </h5>
    </div>
    <div class="card-body">
      <form method="post" action="edit_product.php?id=<?php echo (int)$product['id'] ?>">
        
        <!-- Product Title -->
        <div class="mb-3">
          <label for="product-title" class="form-label">Product Title</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
            <input type="text" id="product-title" class="form-control" name="product-title" value="<?php echo remove_junk($product['name']); ?>" required>
          </div>
        </div>

        <!-- Category & Image -->
        <div class="mb-3">
            <label for="product-categorie" class="form-label">Category</label>
            <select class="form-select " name="product-categorie" id="product-categorie" required>
              <option value="">Select a category</option>
              <?php foreach ($all_categories as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>" <?php if ($product['categorie_id'] === $cat['id']) echo "selected"; ?>>
                  <?php echo remove_junk($cat['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
        </div>


        <!-- Submit Button -->
        <div class="d-flex justify-content-end">
          <button type="submit" name="product" class="btn secondary-btn">
            <i class="fas fa-save me-1"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
