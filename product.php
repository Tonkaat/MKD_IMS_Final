<?php
$page_title = 'All Product';
require_once('includes/load.php');
page_require_level(2);

$all_location = find_all('location');
$all_categories = find_all('categorie');
// Fetch categories safely
$categories = $db->query("SELECT * FROM categories");

if (!$categories) {
    die("Query failed: " . $db->error);
}

$categories = $categories->fetch_all(MYSQLI_ASSOC); // Fetch results as an associative array

// Check if 'location' exists in 'stock' table before querying
$location_check = $db->query("SHOW COLUMNS FROM stock LIKE 'location'");

if ($location_check->num_rows > 0) {
    $locations = $db->query("SELECT DISTINCT location FROM stock");

    if (!$locations) {
        die("Query failed: " . $db->error);
    }

    $locations = $locations->fetch_all(MYSQLI_ASSOC);
} else {
    $locations = []; // If 'location' column doesn't exist, return an empty array
}

$products = join_product_table();
?>

<?php include_once('layouts/header.php'); ?>


<h2 class="fw-bold" style="color: var(--primary)">Manage Inventory</h2>

<div class="d-flex justify-content-between mb-3">
  <input type="text" id="search" class="form-control w-50" placeholder="Search" onkeyup="filterProducts()">
  <button class="btn secondary-btn" onclick="window.location.href='add_product.php'">Add New Product</button>
</div>

<?php
// Check if the 'status' and 'message' query parameters are set
if (isset($_GET['status']) && isset($_GET['message'])) {
  $status = $_GET['status'];
  $message = $_GET['message'];

  // Display the message based on the status
  if ($status == 'success') {
      echo "<div class='alert alert-success text-center'>$message</div>";
  } else if ($status == 'error') {
      echo "<div class='alert alert-danger text-center'>$message</div>";
  }
}
?>

<table class="table table-striped table-bordered">
    <thead>
        <tr class="cont-head">
            <th>Product Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
          <?php foreach ($products as $product): ?>
          <tr>
              <td onclick="toggleStock(<?php echo $product['id']; ?>)" style="cursor:pointer;">
                  <?php echo htmlspecialchars($product['name']); ?>
              </td>
              <td><?php echo htmlspecialchars($product['categorie']); ?></td>
              <td><?php echo htmlspecialchars($product['quantity']); ?></td>
              
              <td>
                <a href="edit_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-info btn-sm secondary-btn" title="Edit" data-bs-toggle="tooltip">
                  Edit
                </a>
                <button class="btn btn-success btn-sm" title="Add Stocks" data-bs-toggle="tooltip" onclick="openAddStocksModal(<?php echo (int)$product['id'];?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                  Add Stocks
                </button>
                <button class="btn btn-danger btn-sm" title="Delete" data-bs-toggle="tooltip" onclick="confirmDeleteProduct(<?php echo (int)$product['id'];?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                  Delete
                </button>
              </td>
          </tr>
          
          <!-- Stock List -->
          <tr id="stock-<?php echo $product['id']; ?>" style="display:none;">
              <td colspan="4">
                  <table class="table table-bordered">
                      <tr>
                          <th>Stock ID</th>
                          <th>Location</th>
                          <th>Status</th>     
                          <th>Action</th>
                      </tr>
                      <?php 
                          $stockQuery = "
                          SELECT stock.*, 
                                status.name AS status_name, 
                                location.name AS location_name
                          FROM stock
                          LEFT JOIN status ON stock.status_id = status.id
                          LEFT JOIN location ON stock.location_id = location.id
                          WHERE stock.product_id = " . $product['id'];

                          $stockResult = $db->query($stockQuery);

                          while ($stock = mysqli_fetch_assoc($stockResult)) { ?>
                          <tr>
                              <td><?php echo htmlspecialchars($stock['stock_number']); ?></td>
                              <td><?php echo htmlspecialchars($stock['location_name']); ?></td> <!-- Display location name -->
                              <td><?php echo htmlspecialchars($stock['status_name']); ?></td> <!-- Display status name -->
                              <td>
                                  <button class="btn btn-outline-primary btn-sm" onclick="openLocationModal(<?php echo $stock['id']; ?>)">Change Location</button>
                                  <button class="btn btn-outline-success btn-sm" onclick="openStatusModal(<?php echo $stock['id']; ?>)">Change Status</button>
                                  <button class="btn btn-outline-warning btn-sm" onclick="openRenameModal(<?php echo $stock['id']; ?>, '<?php echo htmlspecialchars($stock['stock_number']); ?>')">Rename</button>
                                  <button class="btn btn-outline-danger btn-sm" onclick="confirmDeleteStock(<?php echo $stock['id']; ?>)">Delete</button>
                              </td>
                          </tr>
                          <?php } ?>
                  </table>
              </td>
          </tr>
      <?php endforeach; ?>
    </tbody>
</table>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="locationModalLabel">
          <i class="bi bi-geo-alt-fill me-2"></i> Select Location
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="locationForm" action="update_stock_location.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
           <input type="hidden" id="stockIdLocation" name="stock_id">
            <label for="location" class="form-label">Choose a Location:</label>
            <select id="location" name="location" class="form-select rounded">
              <option value="">Select a location</option>
              <?php foreach ($all_location as $loc): ?>
                <option value="<?php echo (int)$loc['id']; ?>"
                  <?php if (isset($stock['location_id']) && $stock['location_id'] === (int)$loc['id']) echo "selected"; ?>>
                  <?php echo remove_junk($loc['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary rounded-2">
            <i class="bi bi-arrow-repeat me-1"></i> Update Location
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">
          <i class="bi bi-toggle-on me-2"></i> Select Status
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="statusForm" action="update_stock_status.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <input type="hidden" id="stockIdStatus" name="stock_id">
            <label for="status" class="form-label">Choose a Status:</label>
            <select id="status" name="status" class="form-select rounded">
              <option value="">Select a status</option>
              <option value="1">Available</option>
              <option value="2">Borrowed</option>
              <option value="3">Missing</option>
              <option value="4">Lost</option>
              <option value="5">Defected</option>
              <option value="6">Placed</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary rounded-2">
            <i class="bi bi-arrow-repeat me-1"></i> Update Status
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Rename Stock ID Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="renameModalLabel">
          <i class="bi bi-pencil-square me-2"></i> Rename Stock ID
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="renameForm" action="update_stock_name.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <input type="hidden" id="stockIdRename" name="stock_id">
            <label for="stockNumber" class="form-label">New Stock ID:</label>
            <input type="text" class="form-control" id="stockNumber" name="stock_number" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary rounded-2">
            <i class="bi bi-save me-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Stock Confirmation Modal -->
<div class="modal fade" id="deleteStockModal" tabindex="-1" aria-labelledby="deleteStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteStockModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this stock item? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteStockForm" action="delete_stock.php" method="POST">
          <input type="hidden" id="stockIdDelete" name="stock_id">
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Product Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteProductModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i> Confirm Delete
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong id="productNameToDelete"></strong>?</p>
        <p class="text-danger">This action cannot be undone and will remove all associated stock items.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteProductForm" action="delete_product.php" method="GET">
          <input type="hidden" id="productIdDelete" name="id">
          <button type="submit" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add Stocks Modal -->
<div class="modal fade" id="addStocksModal" tabindex="-1" aria-labelledby="addStocksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="addStocksModalLabel">
          <i class="bi bi-plus-circle-fill me-2"></i> Add Stocks
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addStocksForm" action="add_stocks.php" method="POST">
        <div class="modal-body">
          <input type="hidden" id="productId" name="product_id">
          <div class="mb-3">
            <label for="productName" class="form-label">Product:</label>
            <input type="text" class="form-control" id="productName" disabled>
          </div>
          <div class="mb-3">
            <label for="quantity" class="form-label">Quantity to Add:</label>
            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Add Stocks
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Search and Button Layout */
  .form-control {
    border-radius: 10px;
  }

  .btn {
    border-radius: 10px;
  }

  /* Table Styling */
  .table-striped tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
  }

  .table-dark th {
    background-color: #343a40;
    color: white;
  }

  .btn-sm {
    border-radius: 5px;
    margin-right: 5px;
  }

  /* Button styling */
  #myBtn {
    margin-top: 10px;
    cursor: pointer;
  }
</style>

<script>
    function toggleStock(productId) {
        var stockRow = document.getElementById('stock-' + productId);
        stockRow.style.display = stockRow.style.display === 'none' ? 'table-row' : 'none';
    }
    
    function filterProducts() {
        var searchFilter = document.getElementById('search').value.toLowerCase();

        // Select all product rows (excluding the stock rows)
        var rows = document.querySelectorAll('tbody tr:not([id^=stock])');
        
        rows.forEach(row => {
            var productName = row.cells[0].innerText.toLowerCase();
            var matchesSearch = productName.includes(searchFilter);

            // Display row if it matches all filters
            if (matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Remove the old confirm function
    // function confirmDelete() {
    //     return confirm("Are you sure you want to delete this product? This action cannot be undone.");
    // }

    function confirmDeleteProduct(productId, productName) {
        document.getElementById("productIdDelete").value = productId;
        document.getElementById("productNameToDelete").textContent = productName;
        const modal = new bootstrap.Modal(document.getElementById("deleteProductModal"));
        modal.show();
    }

    function confirmDeleteStock(stockId) {
        document.getElementById("stockIdDelete").value = stockId;
        const modal = new bootstrap.Modal(document.getElementById("deleteStockModal"));
        modal.show();
    }

    function openStatusModal(stockId) {
        document.getElementById("stockIdStatus").value = stockId;
        const modal = new bootstrap.Modal(document.getElementById("statusModal"));
        modal.show();
    }

    function openLocationModal(stockId) {
        document.getElementById("stockIdLocation").value = stockId;
        const modal = new bootstrap.Modal(document.getElementById("locationModal"));
        modal.show();
    }
    
    function openRenameModal(stockId, currentName) {
        document.getElementById("stockIdRename").value = stockId;
        document.getElementById("stockNumber").value = currentName;
        const modal = new bootstrap.Modal(document.getElementById("renameModal"));
        modal.show();
    }
    
    function openAddStocksModal(productId, productName) {
        document.getElementById("productId").value = productId;
        document.getElementById("productName").value = productName;
        const modal = new bootstrap.Modal(document.getElementById("addStocksModal"));
        modal.show();
    }
</script>
<?php include_once('layouts/footer.php'); ?>