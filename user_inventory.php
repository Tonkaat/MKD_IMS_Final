<?php
$page_title = 'Inventory by Location';
require_once('includes/load.php');
page_require_level(3);

// Get the logged-in user's location_id
$user = current_user(); // Make sure this function is correctly defined in `load.php`

// Ensure $user is properly set before accessing properties
$user_location_id = isset($user['location_id']) ? $user['location_id'] : '';

// Get location name for display purposes
$location_name = 'Unknown Location';
if (!empty($user_location_id)) {
    $location_result = $db->query("SELECT name FROM location WHERE id = '{$db->escape($user_location_id)}'");
    if ($location_result && $location_result->num_rows > 0) {
        $location_data = $location_result->fetch_assoc();
        $location_name = $location_data['name'];
    }
}

$all_locations = find_all('location');
$all_categories = find_all('categorie');

// Fetch categories safely
$categories = $db->query("SELECT * FROM categories");

if (!$categories) {
    die("Query failed: " . $db->error);
}

$categories = $categories->fetch_all(MYSQLI_ASSOC); // Fetch results as an associative array

// Check if 'location_id' exists in 'stock' table before querying
$location_check = $db->query("SHOW COLUMNS FROM stock LIKE 'location_id'");

if ($location_check && $location_check->num_rows > 0) {
    $locations = $db->query("SELECT DISTINCT s.location_id, l.name 
                            FROM stock s
                            JOIN location l ON s.location_id = l.id");

    if (!$locations) {
        die("Query failed: " . $db->error);
    }

    $locations = $locations->fetch_all(MYSQLI_ASSOC);
} else {
    $locations = []; // If 'location_id' column doesn't exist, return an empty array
}

// Fetch products based on the user's location - only products with stock > 0
$products = [];
if (!empty($user_location_id)) {
    $sql = "SELECT products.id, 
                   products.name AS product_name,
                   COUNT(stock.id) AS stock_count, 
                   categories.name AS categorie
            FROM products
            JOIN stock ON products.id = stock.product_id AND stock.location_id = '{$db->escape($user_location_id)}'
            JOIN categories ON products.categorie_id = categories.id
            GROUP BY products.id
            HAVING stock_count > 0";

    $result = $db->query($sql);
    if ($result) {
        $products = $db->while_loop($result);
    } else {
        // Handle query error
        $error = $db->error;
    }
}
?>

<?php include_once('layouts/header.php'); ?>

<h2 class="fw-bold" style="color: var(--primary)">Inventory in <?= htmlspecialchars($location_name) ?></h2>

<div class="d-flex justify-content-between mb-3">
  <input type="text" id="search" class="form-control w-50" placeholder="Search" onkeyup="filterProducts()">
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

<?php if (empty($products)): ?>
  <div class="alert alert-warning text-center">No products with inventory found at this location.</div>
<?php elseif (isset($error)): ?>
  <div class="alert alert-danger">Error loading products: <?= htmlspecialchars($error) ?></div>
<?php else: ?>
  <table class="table table-striped table-bordered">
      <thead>
          <tr class="cont-head">
              <th>Product Name</th>
              <th>Category</th>
              <th>Quantity</th>
          </tr>
      </thead>
      <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td onclick="toggleStock(<?php echo $product['id']; ?>)" style="cursor:pointer;">
                    <?php echo htmlspecialchars($product['product_name']); ?>
                </td>
                <td><?php echo htmlspecialchars($product['categorie']); ?></td>
                <td><?php echo htmlspecialchars($product['stock_count']); ?></td>
            </tr>
            
            <!-- Stock List -->
            <tr id="stock-<?php echo $product['id']; ?>" style="display:none;">
                <td colspan="3">
                    <table class="table table-bordered">
                        <tr>
                            <th>Stock ID</th>
                            <th>Status</th>     
                            <th>Action</th>
                        </tr>
                        <?php 
                            $stockQuery = "SELECT stock.id, stock.stock_number, status.name AS status_name, status.id AS status_id
                                           FROM stock
                                           LEFT JOIN status ON stock.status_id = status.id
                                           WHERE stock.product_id = {$product['id']} 
                                           AND stock.location_id = '{$db->escape($user_location_id)}'";
                            $stockResult = $db->query($stockQuery);
                            
                            if ($stockResult && $stockResult->num_rows > 0):
                              while ($stock = $stockResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stock['stock_number']); ?></td>
                                <td><?php echo htmlspecialchars($stock['status_name']); ?></td>
                                <td>
                                    <button class="btn btn-outline-warning btn-sm" onclick="openReportModal(<?= $stock['id'] ?>)">Report Flagged</button>
                                    <?php if ($stock['status_name'] === 'Missing'): ?>
                                      <button class="btn btn-outline-success btn-sm" onclick="openConfirmModal('found', <?= $stock['id'] ?>)">Found</button>
                                    <?php endif; ?>
                                    <?php if ($stock['status_name'] === 'Defected'): ?>
                                      <button class="btn btn-outline-success btn-sm" onclick="openConfirmModal('fixed', <?= $stock['id'] ?>)">Fixed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile;
                            else: ?>
                              <tr><td colspan="3">No stock items found at this location.</td></tr>
                            <?php endif; ?>
                    </table>
                </td>
            </tr>
        <?php endforeach; ?>
      </tbody>
  </table>
<?php endif; ?>

<!-- Report Missing/Lost Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="reportModalLabel">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> Report Item as Flagged
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="reportForm" action="report_missing.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <input type="hidden" id="stockIdReport" name="stock_id">
            <label for="status" class="form-label">Choose Status:</label>
            <select id="status" name="status" class="form-select rounded" required>
              <option value="">Select Status</option>
              <option value="3">Missing</option>
              <option value="4">Lost</option>
              <option value="5">Defected</option>
            </select>
          </div>
          <div id="reportMessage" class="mt-3 text-success fw-bold d-none"></div>
          <button id="availableAgainBtn" type="button" class="btn btn-success mt-2 d-none"
                  onclick="markAsAvailable(document.getElementById('stockIdReport').value)">
            Report as Available Again
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">
            <i class="bi bi-x me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary rounded-2">
            <i class="bi bi-exclamation-triangle me-1"></i> Submit Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-3">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmationModalLabel">
          <i class="bi bi-question-circle-fill me-2"></i> Confirm Action
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="confirmationMessage">Are you sure you want to proceed with this action?</p>
        <input type="hidden" id="confirmStockId" value="">
        <input type="hidden" id="confirmAction" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary rounded-2" data-bs-dismiss="modal">
          <i class="bi bi-x me-1"></i> Cancel
        </button>
        <button type="button" id="confirmActionBtn" class="btn btn-success rounded-2" onclick="processConfirmation()">
          <i class="bi bi-check-circle me-1"></i> Confirm
        </button>
      </div>
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
  }

  /* Button styling */
  #myBtn {
    margin-top: 10px;
    cursor: pointer;
  }
  
  /* Modal styling */
  .modal-content {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .modal-header {
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
  }
  
  .modal-footer {
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
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
            var categoryName = row.cells[1].innerText.toLowerCase();
            var matchesSearch = productName.includes(searchFilter) || categoryName.includes(searchFilter);

            // Display row if it matches all filters
            if (matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function openReportModal(stockId) {
        document.getElementById("stockIdReport").value = stockId;
        const modal = new bootstrap.Modal(document.getElementById("reportModal"));
        modal.show();
    }
    
    function openConfirmModal(action, stockId) {
        const confirmModal = document.getElementById("confirmationModal");
        const confirmMessage = document.getElementById("confirmationMessage");
        const confirmStockId = document.getElementById("confirmStockId");
        const confirmAction = document.getElementById("confirmAction");
        const confirmActionBtn = document.getElementById("confirmActionBtn");
        
        confirmStockId.value = stockId;
        confirmAction.value = action;
        
        if (action === 'found') {
            confirmMessage.textContent = "Are you sure this item has been found and is now available again?";
            confirmActionBtn.classList.remove('btn-primary');
            confirmActionBtn.classList.add('btn-success');
            confirmActionBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirm Found';
        } else if (action === 'fixed') {
            confirmMessage.textContent = "Are you sure this item has been fixed and is now available again?";
            confirmActionBtn.classList.remove('btn-primary');
            confirmActionBtn.classList.add('btn-success');
            confirmActionBtn.innerHTML = '<i class="bi bi-tools me-1"></i> Confirm Fixed';
        }
        
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
    }
    
    function processConfirmation() {
        const stockId = document.getElementById("confirmStockId").value;
        const action = document.getElementById("confirmAction").value;
        
        if (action === 'found') {
            window.location.href = "found_item.php?stock_id=" + stockId + "&status=6";
        } else if (action === 'fixed') {
            window.location.href = "fixed_item.php?stock_id=" + stockId + "&status=6";
        }
    }

    // Form submission handler
    document.addEventListener('DOMContentLoaded', function() {
        var reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                var statusSelect = document.getElementById('status');
                if (!statusSelect.value) {
                    e.preventDefault();
                    alert('Please select a status');
                    return false;
                }
                // Form will submit normally if validation passes
            });
        }
    });
</script>

<?php include_once('layouts/footer.php'); ?>