<?php
  $page_title = 'Admin Dashboard';
  require_once('includes/load.php');
  page_require_level(1);
?>
<?php include_once('layouts/header.php'); ?>
<?php $stockData = getStockBreakdown(); ?>

<!-- Dashboard Welcome Section with Background -->
<div class="py-5 px-4 mb-5 rounded-4 shadow-sm" style="border:7px solid black; background: linear-gradient(90deg, rgba(8, 8, 101, 1) 0%, rgb(12, 67, 138) 100%); position: relative; overflow: hidden; ">
  <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%); animation: shimmer 7s infinite;"></span>
  <!-- Decorative Elements -->
  <div style="position: absolute; top: -60px; right: -70px; width: 190px; height: 180px; border-radius: 50%; background: rgba(255, 213, 0, 0.84);"></div>
  <div style="position: absolute; bottom: 25px; left: 20px; width: 100px; height: 100px; border-radius: 50%; background: rgba(255, 213, 0, 0.84);"></div>
  <div style="position: absolute; top: 40%; right: 10%; width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 214, 0, 0.05);"></div>
 
  <!-- Light beam effect -->
  <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 50%);"></div>
 
  <div class="text-center mb-5 position-relative">
    <!-- Main Heading with Shadow and Highlights -->
    <h1 class="display-4 fw-bold mb-2" style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); letter-spacing: 1px; ">
      <span style="position: relative; display: inline-block;">
        <span style="position: relative; z-index: 2;">MKD</span>
        <span style="position: absolute; top: 3px; left: 3px; z-index: 1; color: var(--secondary); opacity: 0.3;">MKD</span>
      </span>
      <span style="color: var(--secondary); text-shadow: 2px 2px 4px rgba(0,0,0,0.5);"> Inventory</span> System
    </h1>
   
    <!-- Animated Tagline -->
    <p class="lead" style="color: rgba(255,255,255,0.85); font-weight: 300; max-width: 600px; margin: 0 auto;">
      Streamlined inventory management for efficient operations
    </p>
   
    <!-- Decorative Line -->
    <div style="width: 150px; height: 4px; background: linear-gradient(to right, rgba(255,214,0,0), var(--secondary), rgba(255,214,0,0)); margin: 15px auto;"></div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-4">
  <!-- Missing/Lost Items Card -->
  <div class="col-md-4">
    <div class="sum-card shadow-sm h-100" data-bs-toggle="modal" data-bs-target="#missingLostModal">
      <!-- Accent corner -->
      <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--primary); transform: rotate(45deg);">
        <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--secondary); font-weight: bold; font-size: 1rem;">
        <?php echo count_missinglost_items(); ?>
        </span>
      </div>
    
      <div class="card-body p-4" style="position: relative; z-index: 1;">
        <div class="text-center mb-3">
          <i class="bi bi-eye-slash" style="color: var(--primary); font-size: 2.5rem;"></i>
        </div>
        <h2 class="text-center fw-bold mb-0" style="color: var(--primary); font-size: 1.6rem; letter-spacing: 0.5px;"> Missing/Lost </h2>
        <div style="width: 60px; height: 3px; background: var(--primary); margin: 8px auto;"></div>
        <p class="text-center mb-0" style="color: var(--primary); font-weight: 500; font-size: 1.1rem;">Items</p>
        <!-- Hidden counter for functionality -->
        <div style="display: none;"><?php echo count_missinglost_items(); ?></div>
      </div>
    </div>
  </div>

      <!-- Recent Actions -->
    <div class="col-md-4">
      <div class="sum-card shadow-sm h-100" data-bs-toggle="modal" data-bs-target="#defectedModal" style="background-color: var(--primary)">
        <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%);"></span>
        <!-- Accent corner -->
        <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--secondary); transform: rotate(45deg);">
        <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--primary); font-weight: bold; font-size: 1rem;">
        <?php echo count_defected_items(); ?>
        </span>
        </div>
        <div class="card-body p-4" style="position: relative; z-index: 1;">
          <div class="text-center mb-3">
            <i class="bi bi-wrench" style="color: var(--secondary); font-size: 2.5rem;"></i>
          </div>
          <h2 class="text-center fw-bold mb-0" style="color: white; font-size: 1.6rem; letter-spacing: 0.5px;">Defected</h2>
          <div style="width: 60px; height: 3px; background: var(--secondary); margin: 8px auto;"></div>
          <p class="text-center mb-0" style="color: white; font-weight: 500; font-size: 1.1rem;">Items</p>
          <!-- Hidden counter for functionality -->
          <div style="display: none;"><?php echo count_defected_items(); ?></div>
        </div>
      </div>
    </div>

    <!-- Low Stock -->
    <div class="col-md-4">
      <div class="sum-card shadow-sm h-100 " data-bs-toggle="modal" data-bs-target="#lowStockModal">
      <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%);"></span>
        <!-- Accent corner -->
        <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--primary); transform: rotate(45deg);">
            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--secondary); font-weight: bold; font-size: 1rem;">
            <?php echo count_lowstock_items(); ?>
            </span>
        </div>
        
        <div class="card-body p-4" style="position: relative; z-index: 1;">
          <div class="text-center mb-3">
            <i class="bi bi-exclamation-triangle" style="color: var(--primary); font-size: 2.5rem;"></i>
          </div>
          <h2 class="text-center fw-bold mb-0" style="color: var(--primary); font-size: 1.6rem; letter-spacing: 0.5px;">Low Stock</h2>
          <div style="width: 60px; height: 3px; background: var(--primary); margin: 8px auto;"></div>
          <p class="text-center mb-0" style="color: var(--primary); font-weight: 500; font-size: 1.1rem;">Items</p>
          <!-- Hidden counter for functionality -->
          <div style="display: none;">1</div>
        </div>
      </div>
    </div>
  </div> 

  <!-- Quick Scan Button -->
  <div class="d-flex justify-content-center mt-5">
    <button class="btn btn-lg shadow-lg d-flex align-items-center justify-content-center px-5 py-3" 
            style="background-color:var(--secondary); 
                   color: var(--primary); 
                   border-radius: 30px; 
                   font-weight: 600; 
                   letter-spacing: 0.5px;
                   font-size: 1.1rem;
                   border: none;
                   position: relative;
                   overflow: hidden;
                   transition: all 0.3s ease;" 
            id="scanBtn">
      <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%); animation: shimmer 2s infinite;"></span>
      <i class="bi bi-upc-scan me-2" style="font-size: 1.3rem;"></i>
      <span>Scan Barcode</span>
    </button>
  </div>
</div>


<!-- Updated Barcode Modal - Grocery Style (Inventory Focus) -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content shadow-lg">
      <div class="modal-header text-white" style="background-color: var(--primary);">
        <h5 class="modal-title" id="barcodeModalLabel">Product Scanner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Hidden input for barcode scanning -->
        <input type="text" id="hiddenBarcodeInput" style="position: absolute; left: -9999px;" autofocus>
        
        <!-- Scanning status and instructions -->
        <div id="scanningStatus" class="alert d-flex align-items-center mb-3" style="background-color:rgb(193, 208, 255); color: var(--primary);">
          <i class="bi bi-upc-scan me-2 fs-4"></i>
          <div id="barcodeFeedback">Scan a product barcode to begin</div>
        </div>
        
        <!-- Scanned Items Table -->
        <div class="table-responsive mb-3">
          <table class="table table-hover" id="scannedItemsTable">
            <thead style="background-color: var(--primary); color: white;">
              <tr>
                <th style="width: 5%">#</th>
                <th style="width: 40%">Product</th>
                <th style="width: 20%">Quantity</th>
                <th style="width: 10%">Available</th>
                <th style="width: 10%">Actions</th>
              </tr>
            </thead>
            <tbody id="scannedItemsList">
              <!-- Scanned items will be added here dynamically -->
              <tr id="noItemsRow">
                <td colspan="5" class="text-center py-4 text-muted">No items scanned yet</td>
              </tr>
            </tbody>
            <tfoot>
              <tr style="background-color: #e3f2fd; color: var(--primary);" class="fw-bold">
                <td colspan="3" class="text-end">Total Items:</td>
                <td id="totalQty">0</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>
        
        <!-- Quick Quantity Panel -->
        <div id="quickQtyPanel" class="card mb-3" style="display: none; border-color: var(--primary);">
          <div class="card-header" style="background-color: #e3f2fd; color: var(--primary);">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0" id="quickQtyProduct">Select Quantity</h5>
              <button type="button" class="btn-close" id="closeQuickQty"></button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="input-group">
                  <span class="input-group-text" style="background-color: var(--primary); color: white;">Quantity</span>
                  <input type="number" class="form-control" id="quickQtyInput" value="1" min="1">
                  <button class="btn" style="background-color: var(--primary); color: white;" type="button" id="decQty">-</button>
                  <button class="btn" style="background-color: var(--primary); color: white;" type="button" id="incQty">+</button>
                </div>
                <small class="text-muted">Available: <span id="quickQtyAvailable">0</span></small>
              </div>
              <div class="col-md-6 d-flex align-items-center justify-content-end">
                <button class="btn" style="background-color: var(--secondary); color: var(--primary);" id="confirmQuickQty">Confirm</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <div>
          <button type="button" class="btn" style="background-color:var(--danger); color: white;" id="clearAllBtn">
            <i class="bi bi-trash me-1"></i> Clear All
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-color: var(--primary); color: var(--primary);">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
        </div>
        <button id="checkoutBtn" class="btn" style="background-color: var(--secondary); color: var(--primary);" disabled>
          <i class="bi bi-check-circle me-1"></i> Confirm Usage
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="scannerToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header" style="background-color: var(--primary); color: white;">
      <strong class="me-auto" id="toastTitle">Product Scanner</strong>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toastMessage"></div>
  </div>
</div>
                
<!-- Missing/Lost Items Modal -->
<div class="modal fade" id="defectedModal" tabindex="-1" aria-labelledby="defectedModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header text-white rounded-top-4" style="background-color: var(--bs-danger);">
        <h5 class="modal-title fw-semibold" id="defectedModalLabel">
          <i class="bi bi-exclamation-octagon-fill me-2 symbol"></i> Defected Items
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
        <?php
        $missing_lost_items = get_defected_items();
        if (!empty($missing_lost_items)) {
            echo '<ul class="list-group list-group-flush">';
            foreach ($missing_lost_items as $item) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<div>';
                echo '<span><i class="bi bi-box-seam me-2" style="color: var(--primary)"></i> <strong>' . htmlspecialchars($item['stock_number']) . '</strong> located in <strong>' . htmlspecialchars($item['location_name']) . '</strong> is <strong>' . htmlspecialchars($item['status_name']) . '.</strong></span>';
                echo '<div class="text-muted small mt-1">Updated: ' . htmlspecialchars($item['updated_at']) . '</div>';
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="alert" style="background-color: #fff3cd; color: #856404;">';
            echo '<i class="bi bi-info-circle-fill me-2"></i> No missing/lost items reported.';
            echo '</div>';
            notifyMissingItem();
        }
        ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn main-btn" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Missing/Lost Items Modal -->
<div class="modal fade" id="missingLostModal" tabindex="-1" aria-labelledby="missingLostModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header text-white rounded-top-4" style="background-color: var(--bs-danger);">
        <h5 class="modal-title fw-semibold" id="missingLostModalLabel">
          <i class="bi bi-exclamation-octagon-fill me-2 symbol"></i> Missing/Lost Items
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
        <?php
        $missing_lost_items = get_missing_lost_items();
        if (!empty($missing_lost_items)) {
            echo '<ul class="list-group list-group-flush">';
            foreach ($missing_lost_items as $item) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<div>';
                echo '<span><i class="bi bi-box-seam me-2" style="color: var(--primary)"></i> <strong>' . htmlspecialchars($item['stock_number']) . '</strong> located in <strong>' . htmlspecialchars($item['location_name']) . '</strong> is <strong>' . htmlspecialchars($item['status_name']) . '.</strong></span>';
                echo '<div class="text-muted small mt-1">Updated: ' . htmlspecialchars($item['updated_at']) . '</div>';
                echo '</div>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="alert" style="background-color: #fff3cd; color: #856404;">';
            echo '<i class="bi bi-info-circle-fill me-2"></i> No missing/lost items reported.';
            echo '</div>';
            notifyMissingItem();
        }
        ?>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn main-btn" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
    
<!-- Low Stock Items Modal -->
<div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <div class="modal-header text-white rounded-top-4" style="background-color: var(--primary);">
        <h5 class="modal-title fw-semibold" id="lowStockModalLabel">
          <i class="bi bi-exclamation-octagon-fill me-2 symbol"></i> Low Stock Items
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Placeholder for low stock item list -->
        <div class="alert" style="background-color: #e3f2fd; color: var(--primary);">
          All items are sufficiently stocked.
        </div>
        <!-- Table will be dynamically generated here when items exist -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background-color: #e3f2fd; color: var(--primary); border: 1px solid var(--primary);" data-bs-dismiss="modal">Close</button>
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
          <div class="mb-3">
            <label for="stockPrefix" class="form-label">Stock ID Prefix (optional):</label>
            <input type="text" class="form-control" id="stockPrefix" name="stock_prefix" placeholder="e.g., LAP-">
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

<!-- Control Panel (Quick Actions) -->
<div class="d-flex justify-content-center mt-4">
    <a href="add_product.php" class="btn btn-lg shadow-lg rounded-pill d-flex align-items-center justify-content-center px-4 py-2" style="background-color:var(--primary); color: white;" id="addItemBtn">
        <i class="bi bi-plus-circle me-2" style="color: var(--secondary)"></i> <!-- Add Item icon -->
        <span>Add Item</span>
    </a>
    <a href="generate_report.php" class="btn btn-lg mx-2 shadow-lg rounded-pill d-flex align-items-center justify-content-center px-4 py-2" style="background-color: var(--secondary); color: var(--primary);" id="generateReportBtn">
        <i class="bi bi-file-earmark-bar-graph me-2"></i> <!-- Generate Report icon -->
        <span>Generate Report</span>
    </a>
    <a href="product.php" class="btn btn-lg mx-2 shadow-lg rounded-pill d-flex align-items-center justify-content-center px-4 py-2" style="background-color: var(--primary); color: white;" id="manageUsersBtn">
        <i class="bi bi-box me-2" style="color: var(--secondary)"></i> <!-- Manage Users icon -->
        <span>Manage Inventory</span>
    </a>
</div>




<!-- Dashboard Controls Section -->
<div class="container-fluid">
  <!-- Main Row -->
  <div class="row mt-4">
    <!-- Left Column: Currently Logged In Users -->
    <div class="col-md-6 mb-4 ">
      <div class="card shadow-lg h-100">
        <div class="card-header text-white d-flex align-items-center" style="background-color: var(--primary);">
          <i class="bi bi-person me-2" style="color: var(--secondary)"></i>
          <span class="fw-bold">Currently Logged In Users</span>
        </div>
        <div class="card-body">
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Last Login</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $logged_in_users = fetch_logged_in_users();
              if (!empty($logged_in_users)) {
                foreach ($logged_in_users as $user) {
                  echo "<tr>";
                  echo "<td>" . remove_junk($user['username']) . "</td>";
                  echo "<td>" . remove_junk($user['user_level']) . "</td>";
                  echo "<td>" . date("F j, Y, g:i a", strtotime($user['last_login'])) . "</td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='3' class='text-center'>No users currently logged in</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Right Column: Category Breakdown -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-lg h-100">
        <div class="card-header text-white d-flex align-items-center" style="background-color: var(--primary);">
          <i class="bi bi-pie-chart me-2" style="color: var(--secondary)"></i>
          <span class="fw-bold">Category Breakdown</span>
        </div>
        <div class="card-body d-flex justify-content-center align-items-center">
          <canvas id="pieChart" style="max-width: 100%; height: auto;"></canvas>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Second Row: User Item Requests -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow-lg">
        <div class="card-header text-white d-flex align-items-center" style="background-color: var(--primary);">
          <i class="bi bi-send me-2" style="color: var(--secondary)"></i>
          <span class="fw-bold">User Item Requests</span>
        </div>
        <div class="card-body">
          <div id="requests-container">
            <?php include 'load_requests.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Chart.js for Graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Pie Chart (Stock Category Breakdown)
    var ctx2 = document.getElementById("pieChart").getContext("2d");

    // Get stock breakdown data from PHP
    var stockCategories = <?php echo json_encode($stockData); ?>;

    // Prepare data for the Pie Chart
    var labels = [];
    var data = [];
    
    // Generate dynamic colors for each category
    var backgroundColor = [];
    var hue = 0; // Starting Hue value

    stockCategories.forEach(function(item, index) {
        labels.push(item.category_name); // Category name
        data.push(item.total_stock); // Total stock for the category

        // Generate a color using HSL (with varying hue and constant saturation/lightness)
        var color = `hsl(${hue}, 70%, 60%)`; // HSL color (hue, saturation, lightness)
        backgroundColor.push(color);
        
        // Increment the hue to get a new color for the next category
        hue += 360 / stockCategories.length; // Adjust hue based on the number of categories
        if (hue > 360) hue = 0; // Reset hue after a full circle
    });

    var pieChart = new Chart(ctx2, {
        type: "pie",
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor
            }]
        }
    });

    // Low Stock Monitoring System for Consumable Items (Category ID: 12)
// Low Stock Monitoring System for Consumable Items (Category ID: 12)
document.addEventListener('DOMContentLoaded', function() {
  // Initialize the low stock tracking system
  const lowStockTracker = {
    // Store for all inventory items
    inventory: [],
    
    // Configuration
    lowStockThreshold: 5,
    consumableCategoryId: 12, // Category ID for consumable items
    
    // Initialize the system
    init: function() {
      // Load inventory data from database
      this.loadInventory();
      
      // Set up event listeners
      this.setupEventListeners();
    },
    
    // Load inventory data from database
    loadInventory: function() {
      // Using Fetch API to get data from server
      fetch('stock_count.php')
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Inventory data loaded:', data); // Debug output
          
          // Check for stock level changes and create notifications
          if (this.inventory.length > 0) {
            this.checkStockChanges(this.inventory, data);
          }
          
          this.inventory = data;
          this.updateLowStockDisplay();
        })
        .catch(error => {
          console.error('Error fetching inventory:', error);
          // Show error message in modal
          const modalBody = document.querySelector('#lowStockModal .modal-body');
          if (modalBody) {
            modalBody.innerHTML = `
              <div class="alert alert-danger">
                Error loading inventory data: ${error.message}. Please try refreshing the page.
              </div>
            `;
          }
        });
    },
    
    // Check for stock level changes and create notifications
    checkStockChanges: function(oldData, newData) {
      // Create map of old data for easy lookup
      const oldDataMap = {};
      oldData.forEach(item => {
        oldDataMap[item.id] = this.getItemStockCount(item);
      });
      
      // Check each item in new data
      newData.forEach(item => {
        const productId = item.id;
        const productName = item.name;
        const newQuantity = this.getItemStockCount(item);
        
        // If item exists in old data, check for changes
        if (oldDataMap.hasOwnProperty(productId)) {
          const oldQuantity = oldDataMap[productId];
          
          // 1. If stock level is now below threshold, create low stock notification
          if (newQuantity <= this.lowStockThreshold && newQuantity > 0 && oldQuantity > this.lowStockThreshold) {
            this.notifyLowStock(productId, productName, newQuantity, this.lowStockThreshold);
          }
          
          // 2. If stock level is now zero, create out of stock notification
          if (newQuantity === 0 && oldQuantity > 0) {
            this.notifyAdmins(
              "Out of Stock Alert",
              `Product '${productName}' is now out of stock`,
              "danger",
              "inventory",
              `product.php?id=${productId}`
            );
          }
          
          // 3. If product was previously out of stock but now has inventory
          if (oldQuantity === 0 && newQuantity > 0) {
            this.notifyAdmins(
              "Product Restocked",
              `Product '${productName}' is back in stock (Quantity: ${newQuantity})`,
              "success",
              "inventory",
              `product.php?id=${productId}`
            );
          }
        }
      });
    },
    
    // Notify about low stock items
    notifyLowStock: function(productId, productName, quantity, threshold) {
      this.notifyAdmins(
        "Low Stock Alert",
        `Product '${productName}' is running low (${quantity}/${threshold})`,
        "warning",
        "inventory",
        `product.php?id=${productId}`
      );
    },
    
    // Send notification to admins
    notifyAdmins: function(title, message, type, category, link) {
      // Using Fetch API to send notification to server
      fetch('create_notification.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          title: title,
          message: message,
          type: type, // success, warning, danger, etc.
          category: category,
          link: link
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Notification created:', data);
      })
      .catch(error => {
        console.error('Error creating notification:', error);
      });
    },
    
    // Get all consumable items currently below threshold
    getLowStockItems: function() {
      return this.inventory.filter(item => {
        // Check if item has low stock using actual_stock provided by PHP
        const stockCount = this.getItemStockCount(item);
        return stockCount < this.lowStockThreshold;
      });
    },
    
    // Calculate actual stock count from item data
    getItemStockCount: function(item) {
      // Use the actual_stock field which is calculated in the PHP backend
      if (item.actual_stock !== undefined) {
        return parseInt(item.actual_stock);
      }
      
      // If actual_stock is missing, try quantity field
      if (item.quantity !== null && item.quantity !== undefined && item.quantity !== '') {
        return parseInt(item.quantity);
      }
      
      // If quantity is missing, try stock_count
      if (item.stock_count !== undefined) {
        return parseInt(item.stock_count);
      }
      
      // Default to 0 if no stock information is available
      return 0;
    },
    
    // Set up necessary event listeners
    setupEventListeners: function() {
      // Refresh inventory data periodically (every 5 minutes)
      setInterval(() => {
        this.loadInventory();
      }, 300000); // 5 minutes in milliseconds
      
      // Event listener for updating inventory after changes
      document.addEventListener('inventoryUpdated', () => {
        this.loadInventory();
      });
      
      // Add replenish action in the modal
      const modalElement = document.getElementById('lowStockModal');
      if (modalElement) {
        modalElement.addEventListener('click', (e) => {
          if (e.target.classList.contains('replenish-item') || e.target.closest('.replenish-item')) {
            const itemElement = e.target.classList.contains('replenish-item') ? 
                               e.target : 
                               e.target.closest('.replenish-item');
            const itemId = parseInt(itemElement.getAttribute('data-item-id'));
            const itemName = itemElement.getAttribute('data-item-name');
            this.openAddStocksModal(itemId, itemName);
          }
        });
      }
      
      // Listen for form submissions from the addStocksModal
      document.addEventListener('submit', (e) => {
        if (e.target.id === 'addStocksForm') {
          e.preventDefault();
          
          const formData = new FormData(e.target);
          const productId = formData.get('product_id');
          const quantity = formData.get('quantity');
          
          // Send form data to server using XMLHttpRequest to handle redirects properly
          const xhr = new XMLHttpRequest();
          xhr.open('POST', 'add_stocks.php', true);
          
          xhr.onload = function() {
            // Consider any response as successful since PHP redirects
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addStocksModal'));
            if (modal) {
              modal.hide();
            }
            
            // Show success message
            alert(`Successfully added ${quantity} stock(s) to product`);
            
            // Reload inventory
            lowStockTracker.loadInventory();
          };
          
          xhr.onerror = function() {
            console.error('Error adding stocks');
            alert('Failed to add stocks. Please try again.');
          };
          
          xhr.send(formData);
        }
      });
    },
    
    // Open the Add Stocks Modal
    openAddStocksModal: function(productId, productName) {
      // Close the low stock modal if it's open
      const lowStockModal = bootstrap.Modal.getInstance(document.getElementById('lowStockModal'));
      if (lowStockModal) {
        lowStockModal.hide();
      }
      
      // Set values in the Add Stocks Modal
      document.getElementById('productId').value = productId;
      document.getElementById('productName').value = productName || 'Unknown Product';
      
      // Open the Add Stocks Modal
      const addStocksModal = new bootstrap.Modal(document.getElementById('addStocksModal'));
      addStocksModal.show();
    },
    
    // Update the low stock display in both card and modal
    updateLowStockDisplay: function() {
      const lowStockItems = this.getLowStockItems();
      
      // Update counter on the card
      const counterElement = document.querySelector('.card[data-bs-target="#lowStockModal"] .card-body div:last-child');
      if (counterElement) {
        counterElement.textContent = lowStockItems.length;
      }
      
      // Update modal content
      const modalBody = document.querySelector('#lowStockModal .modal-body');
      if (modalBody) {
        if (lowStockItems.length > 0) {
          let tableHtml = `
            <table class="table table-hover">
              <thead style="background-color: #0d47a1; color: white;">
                <tr>
                  <th>Item Name</th>
                  <th>Quantity</th>
                  <th>Location</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
          `;
          
          lowStockItems.forEach(item => {
            const stockCount = this.getItemStockCount(item);
            tableHtml += `
              <tr>
                <td>${item.name}</td>
                <td><span class="badge ${stockCount <= 2 ? 'bg-danger' : 'bg-warning text-dark'}">${stockCount}</span></td>
                <td>${item.location_id || 'N/A'}</td>
                <td>             
                  <button class="btn btn-sm btn-success replenish-item" data-item-id="${item.id}" data-item-name="${item.name}">
                    <i class="bi bi-plus-circle"></i> Add Stocks
                  </button>
                </td>
              </tr>
            `;
          });
          
          tableHtml += `
              </tbody>
            </table>
          `;
          
          modalBody.innerHTML = tableHtml;
          
          // Visual notification on the card (make it pulse)
          const lowStockCard = document.querySelector('.card[data-bs-target="#lowStockModal"]');
          if (lowStockCard && !lowStockCard.classList.contains('pulse-animation')) {
            lowStockCard.classList.add('pulse-animation');
          }
        } else {
          modalBody.innerHTML = `
            <div class="alert" style="background-color: #e3f2fd; color: #102050;">
              All consumable items are sufficiently stocked.
            </div>
          `;
          
          // Remove pulse animation if no low stock items
          const lowStockCard = document.querySelector('.card[data-bs-target="#lowStockModal"]');
          if (lowStockCard) {
            lowStockCard.classList.remove('pulse-animation');
          }
        }
      }
    }
  };
  
  // Initialize the low stock tracking system
  lowStockTracker.init();
  
  // Add to global scope for debugging purposes
  window.lowStockTracker = lowStockTracker;
  
  // Add CSS for the pulse animation
  const style = document.createElement('style');
  style.textContent = `
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.03); }
      100% { transform: scale(1); }
    }
    
    .pulse-animation {
      animation: pulse 1.5s infinite;
    }
  `;
  document.head.appendChild(style);


});

</script>
<script type="text/javascript" src="libs/js/admin.js"></script>
<script type="text/javascript" src="libs/js/admin-notifications.js"></script>

<?php include_once('layouts/footer.php'); ?>



