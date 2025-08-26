<?php
$page_title = 'All categories and locations';
require_once('includes/load.php');
page_require_level(1);

// Handle AJAX requests for modal content FIRST, before any HTML output
if (isset($_GET['ajax']) && $_GET['ajax'] === 'modal') {
    // Clear any output buffer and set headers
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    $type = isset($_GET['type']) ? remove_junk($db->escape($_GET['type'])) : '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($type === 'category' && $id > 0) {
        // For categories: show product name and quantity
        $query = "SELECT name as product_name, quantity FROM products WHERE categorie_id = '{$id}' ORDER BY name ASC";
        $result = $db->query($query);
        
        $items = [];
        if ($result && $db->num_rows($result) > 0) {
            while ($row = $db->fetch_assoc($result)) {
                $items[] = [
                    'product_name' => remove_junk($row['product_name']),
                    'quantity' => $row['quantity'] ?: 'N/A'
                ];
            }
        }
        
        echo json_encode(['type' => 'category', 'items' => $items, 'success' => true]);
        
    } elseif ($type === 'location' && $id > 0) {
        // For locations: show stock number, status, and category
        $query = "SELECT s.stock_number, st.name as status_name, c.name as category_name ";
        $query .= "FROM stock s ";
        $query .= "LEFT JOIN status st ON s.status_id = st.id ";
        $query .= "LEFT JOIN products p ON s.product_id = p.id ";
        $query .= "LEFT JOIN categories c ON p.categorie_id = c.id ";
        $query .= "WHERE s.location_id = '{$id}' ";
        $query .= "ORDER BY s.stock_number ASC";
        
        $result = $db->query($query);
        
        $items = [];
        if ($result && $db->num_rows($result) > 0) {
            while ($row = $db->fetch_assoc($result)) {
                $items[] = [
                    'stock_number' => remove_junk($row['stock_number']),
                    'status_name' => remove_junk($row['status_name']),
                    'category_name' => remove_junk($row['category_name'])
                ];
            }
        }
        
        echo json_encode(['type' => 'location', 'items' => $items, 'success' => true]);
        
    } else {
        echo json_encode(['type' => '', 'items' => [], 'success' => false, 'error' => 'Invalid parameters']);
    }
    
    exit;
}

$all_categories = find_all('categories');
$all_locations = find_all('location'); // Fetch locations

// Handle AJAX requests for modal content
if (isset($_GET['ajax']) && $_GET['ajax'] === 'modal') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    header('Content-Type: application/json');
    
    if ($type === 'category') {
        // For categories: show product name and quantity
        $query = "SELECT p.name as product_name, p.quantity 
                  FROM products p 
                  WHERE p.categorie_id = ? 
                  ORDER BY p.name ASC";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode(['type' => 'category', 'items' => $items]);
        exit;
        
    } elseif ($type === 'location') {
        // For locations: show stock number, status, and category
        $query = "SELECT s.stock_number, st.name as status_name, c.name as category_name
                  FROM stock s
                  JOIN status st ON s.status_id = st.id
                  JOIN products p ON s.product_id = p.id
                  JOIN categories c ON p.categorie_id = c.id
                  WHERE s.location_id = ?
                  ORDER BY s.stock_number ASC";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode(['type' => 'location', 'items' => $items]);
        exit;
    }
    
    echo json_encode(['type' => '', 'items' => []]);
    exit;
}

// Get the type (category or location) and its corresponding name from the URL parameters
$type = isset($_POST['type']) ? $_POST['type'] : '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Fetch the name of the selected category or location
$type_name = '';
if ($type === 'category') {
    $category = find_by_id('categories', $id);
    $type_name = $category ? $category['name'] : '';
} elseif ($type === 'location') {
    $location = find_by_id('location', $id);
    $type_name = $location ? $location['name'] : '';
}
?>

<?php include_once('layouts/header.php'); ?>

<!-- Page header -->
<div class="row mb-3">
  <div class="col-12">
    <h2 class="head-label">Item Categorization</h2>
    <input type="text" id="search-bar" class="form-control mb-2 col-md-4" placeholder="Search">
  </div>
</div>

<!-- Success/Error Message Box -->
<?php if ($session->has_msg()): ?>
  <div class="alert alert-<?= $session->msg_type(); ?> alert-dismissible fade show" role="alert">
    <?= $session->msg(); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<div class="row">
  <!-- Add Category/Location -->
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm border-0">
      <div class="card-header cont-head fw-semibold">
        <i class="bi bi-plus-circle me-1 symbol"></i> Add Category or Location
      </div>
      <div class="card-body">
        <form action="add_category_location.php" method="POST">
          <div class="mb-3">
            <label for="type" class="form-label">Add as:</label>
            <select name="type" id="type" class="form-select" required>
              <option value="category">Category</option>
              <option value="location">Location</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>
          <button type="submit" class="btn secondary-btn w-100">Add</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Categories and Locations -->
  <div class="col-md-9">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header cont-head fw-semibold">
        <i class="bi bi-grid-3x3-gap-fill me-1 symbol"></i> Categories
      </div>
      <div class="card-body">
      <table class="table table-hover table-bordered align-middle">
        <thead class="cont-head-sec">
          <tr>
            <th class="text-center" style="width: 50px; background-color:rgb(224, 225, 234) !important"><i class="bi bi-hash"></i></th>
            <th>Category Name</th>
            <th class="text-center" style="width: 85px; background-color:rgb(224, 225, 234) !important">Actions</th>
          </tr>
        </thead>
          <tbody>
            <?php foreach ($all_categories as $cat): ?>
            <tr>
              <td class="text-center"><?= count_id(); ?></td>
              <td>
                <a href="#" class="text-decoration-none open-modal" style="color: var(--primary)" data-type="category" data-id="<?= $cat['id']; ?>" data-name="<?= remove_junk(ucfirst($cat['name'])); ?>">
                  <?= remove_junk(ucfirst($cat['name'])); ?>
                </a>
              </td>
              <td class="text-end">
                <a href="edit_categorie.php?id=<?= (int)$cat['id']; ?>" class="btn btn-sm secondary-btn" title="Edit">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <a href="javascript:void(0);" onclick="confirmDelete('category', <?= (int)$cat['id']; ?>, '<?= remove_junk($cat['name']); ?>')" class="btn btn-sm btn-danger" title="Remove">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Locations Section -->
    <div class="card shadow-sm border-0">
      <div class="card-header cont-head fw-semibold">
        <i class="bi bi-geo-alt-fill me-1 symbol"></i> Locations
      </div>
      <div class="card-body">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width: 50px; background-color:rgb(224, 225, 234) !important"><i class="bi bi-hash"></i></th>
              <th>Location Name</th>
              <th class="text-center" style="width: 85px; background-color:rgb(224, 225, 234) !important">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_locations as $loc): ?>
              <tr>
                <td class="text-center"><?= count_id(); ?></td>
                <td>
                  <a href="#" class="text-decoration-none open-modal" style="color: var(--primary)" data-type="location" data-id="<?= $loc['id']; ?>" data-name="<?= remove_junk(ucfirst($loc['name'])); ?>">
                    <?= remove_junk(ucfirst($loc['name'])); ?>
                  </a>
                </td>
                <td class="text-end">
                  <a href="edit_location.php?id=<?= (int)$loc['id']; ?>" class="btn btn-sm secondary-btn" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="javascript:void(0);" onclick="confirmDelete('location', <?= (int)$loc['id']; ?>, '<?= remove_junk($loc['name']); ?>')" class="btn btn-sm btn-danger" title="Remove">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="itemsModalLabel">Items</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="modal-search-bar" class="form-control mb-3" placeholder="Search">
        <table class="table table-bordered table-hover">
          <thead class="table-light" id="modal-table-header">
            <!-- Dynamic headers will be inserted here -->
          </thead>
          <tbody id="modal-content">
            <tr>
              <td colspan="5" class="text-center">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="deleteConfirmMessage">Are you sure you want to delete this item?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('search-bar').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');
    
    rows.forEach(function(row) {
        var productName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Search function for filtering the modal items
document.getElementById('modal-search-bar').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('#modal-content tr');
    
    rows.forEach(function(row) {
        if (row.children.length > 1) { // Skip loading row
            var textContent = row.textContent.toLowerCase();
            if (textContent.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    });
});

// Handle modal opening
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('open-modal') || e.target.closest('.open-modal')) {
        e.preventDefault();
        var link = e.target.classList.contains('open-modal') ? e.target : e.target.closest('.open-modal');
        var type = link.getAttribute('data-type');
        var id = link.getAttribute('data-id');
        var name = link.getAttribute('data-name');
        
        // Set modal title
        document.getElementById('itemsModalLabel').textContent = (type === 'category' ? 'Products in Category: ' : 'Items in Location: ') + name;
        
        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('itemsModal'));
        modal.show();
        
        // Load data via AJAX
        loadModalData(type, id);
    }
});

function loadModalData(type, id) {
    var modalContent = document.getElementById('modal-content');
    var modalHeader = document.getElementById('modal-table-header');
    
    // Show loading
    modalContent.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    
    // Set headers based on type
    if (type === 'category') {
        modalHeader.innerHTML = `
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
            </tr>
        `;
    } else if (type === 'location') {
        modalHeader.innerHTML = `
            <tr>
                <th>Stock Number</th>
                <th>Status</th>
                <th>Category</th>
            </tr>
        `;
    }
    
    // Fetch data
    fetch(`?ajax=modal&type=${type}&id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get as text first
        })
        .then(text => {
            try {
                return JSON.parse(text); // Try to parse JSON
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            var html = '';
            
            if (data.success && data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    if (data.type === 'category') {
                        html += `
                            <tr>
                                <td>${escapeHtml(item.product_name)}</td>
                                <td>${escapeHtml(item.quantity)}</td>
                            </tr>
                        `;
                    } else if (data.type === 'location') {
                        html += `
                            <tr>
                                <td>${escapeHtml(item.stock_number)}</td>
                                <td>${escapeHtml(item.status_name)}</td>
                                <td>${escapeHtml(item.category_name)}</td>
                            </tr>
                        `;
                    }
                });
            } else {
                var colspan = (data.type === 'category') ? 2 : 3;
                var message = data.error || 'No items found';
                html = `<tr><td colspan="${colspan}" class="text-center">${escapeHtml(message)}</td></tr>`;
            }
            
            modalContent.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading modal data:', error);
            var colspan = (type === 'category') ? 2 : 3;
            modalContent.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">Error loading data: ${escapeHtml(error.message)}</td></tr>`;
        });
}

// Helper function to escape HTML
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Function to handle delete confirmation
function confirmDelete(type, id, name) {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    var confirmMessage = document.getElementById('deleteConfirmMessage');
    var confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Update the confirmation message
    if (type === 'category') {
        confirmMessage.textContent = `Are you sure you want to delete the category "${name}"?`;
        confirmBtn.href = `delete_categorie.php?id=${id}`;
    } else if (type === 'location') {
        confirmMessage.textContent = `Are you sure you want to delete the location "${name}"?`;
        confirmBtn.href = `delete_locations.php?id=${id}`;
    }
    
    // Show the modal
    deleteModal.show();
}
</script>

<?php include_once('layouts/footer.php'); ?>