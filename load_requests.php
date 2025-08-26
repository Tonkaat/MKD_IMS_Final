<?php
// Include database connection and necessary functions
require_once('includes/load.php');

// Pagination configuration
$items_per_page = 5;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
  
// Count total requests for pagination
$total_rows = $db->query("SELECT COUNT(*) as count FROM item_requests")->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $items_per_page);
  
// Fetch paginated requests with description
$requests = $db->query("SELECT r.*, u.name AS user_name, u.username, r.categorie_id, r.description, r.use_user_location
                      FROM item_requests r 
                      LEFT JOIN users u ON r.user_id = u.id 
                      ORDER BY r.request_date DESC
                      LIMIT {$offset}, {$items_per_page}");
?>

<!-- Item Requests Table -->
<div class="table-responsive">
  <?php if ($requests && $requests->num_rows > 0): ?>
  <table class="table table-hover align-middle mb-0">
    <thead class="bg-light">
      <tr>
        <th>User</th>
        <th>Item Name</th>
        <th>Quantity</th>
        <th>Status</th>
        <th>Requested At</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($req = $requests->fetch_assoc()): ?>
      <tr>
        <td>
          <span class="fw-medium"><?php echo remove_junk(ucfirst($req['user_name'] ?? $req['username'])); ?></span>
        </td>
        <td>
          <?php echo remove_junk(ucfirst($req['item_name'])); ?>
        </td>
        <td>
          <span class="badge bg-light text-dark"><?php echo (int)$req['quantity']; ?></span>
        </td>
        <td>
          <?php if ($req['status'] == 'Pending'): ?>
            <span class="badge bg-warning text-dark">
              <i class="bi bi-hourglass-split me-1"></i> Pending
            </span>
          <?php elseif ($req['status'] == 'Approved'): ?>
            <span class="badge bg-info">
              <i class="bi bi-check-circle me-1"></i> Approved
            </span>
          <?php elseif ($req['status'] == 'Added'): ?>
            <span class="badge bg-success">
              <i class="bi bi-check-all me-1"></i> Added
            </span>
          <?php elseif ($req['status'] == 'Denied'): ?>
            <span class="badge bg-danger">
              <i class="bi bi-x-circle me-1"></i> Denied
            </span>
          <?php else: ?>
            <span class="badge bg-secondary">
              <?php echo $req['status']; ?>
            </span>
          <?php endif; ?>
        </td>
        <td><?php echo read_date($req['request_date']); ?></td>
        <td class="text-end">
          <button type="button" class="btn btn-sm" 
                  style="background-color: var(--primary); color: white;"
                  data-bs-toggle="modal" 
                  data-bs-target="#requestModal<?php echo $req['id']; ?>">
            <i class="bi bi-info-circle me-1"></i> Details
          </button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="text-center p-5">
    <div class="mb-4">
      <i class="bi bi-inbox text-muted" style="font-size: 2.5rem;"></i>
    </div>
    <h5 class="text-muted">No Item Requests</h5>
    <p class="text-muted small">There are currently no item requests from users.</p>
  </div>
  <?php endif; ?>
</div>

<!-- Request Detail Modals -->
<?php
if ($requests) {
  $requests->data_seek(0); // Reset the result pointer
  while ($req = $requests->fetch_assoc()):
?>
<div class="modal fade" id="requestModal<?php echo $req['id']; ?>" tabindex="-1" aria-labelledby="requestModalLabel<?php echo $req['id']; ?>" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--primary); color: white;">
        <h5 class="modal-title" id="requestModalLabel<?php echo $req['id']; ?>">
          <i class="bi bi-info-circle me-2"></i> Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Requestor</label>
            <p class="mb-0"><?php echo remove_junk(ucfirst($req['user_name'] ?? $req['username'])); ?></p>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Item Name</label>
            <p class="mb-0"><?php echo remove_junk(ucfirst($req['item_name'])); ?></p>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Quantity</label>
            <p class="mb-0"><?php echo (int)$req['quantity']; ?></p>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Status</label>
            <p class="mb-0">
              <?php if ($req['status'] == 'Pending'): ?>
                <span class="badge bg-warning text-dark">
                  <i class="bi bi-hourglass-split me-1"></i> Pending
                </span>
              <?php elseif ($req['status'] == 'Approved'): ?>
                <span class="badge bg-info">
                  <i class="bi bi-check-circle me-1"></i> Approved
                </span>
              <?php elseif ($req['status'] == 'Added'): ?>
                <span class="badge bg-success">
                  <i class="bi bi-check-all me-1"></i> Added
                </span>
              <?php elseif ($req['status'] == 'Denied'): ?>
                <span class="badge bg-danger">
                  <i class="bi bi-x-circle me-1"></i> Denied
                </span>
              <?php else: ?>
                <span class="badge bg-secondary">
                  <?php echo $req['status']; ?>
                </span>
              <?php endif; ?>
            </p>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Request Date</label>
            <p class="mb-0"><?php echo read_date($req['request_date']); ?></p>
          </div>
          <div class="col-6">
            <label class="form-label text-muted small mb-1">Use User Location</label>
            <p class="mb-0">
              <?php if ($req['use_user_location'] == 1): ?>
                <span class="badge bg-success">Yes</span>
              <?php else: ?>
                <span class="badge bg-secondary">No</span>
              <?php endif; ?>
            </p>
          </div>
          <div class="col-12">
            <label class="form-label text-muted small mb-1">Notes/Description</label>
            <div class="p-2 bg-light rounded">
              <?php if (!empty($req['description'])): ?>
                <p class="mb-0 small"><?php echo nl2br(remove_junk($req['description'])); ?></p>
              <?php else: ?>
                <p class="text-muted mb-0 small"><i>No description provided</i></p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
        
        <!-- Action buttons based on status -->
        <?php if ($req['status'] == 'Pending'): ?>
          <div>
            <button type="button" class="btn btn-sm btn-success" onclick="confirmAction('approve', <?php echo $req['id']; ?>)">
              <i class="bi bi-check2 me-1"></i> Approve
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="confirmAction('deny', <?php echo $req['id']; ?>)">
              <i class="bi bi-x me-1"></i> Deny
            </button>
          </div>
        <?php elseif ($req['status'] == 'Approved'): ?>
          <form method="POST" action="request_available.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to make this item available?');">
            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
            <input type="hidden" name="product-title" value="<?php echo remove_junk($req['item_name']); ?>">
            <input type="hidden" name="product-categorie" value="<?php echo (int)$req['categorie_id']; ?>">
            <input type="hidden" name="product-quantity" value="<?php echo (int)$req['quantity']; ?>">
            <input type="hidden" name="product-location" value="">
            <input type="hidden" name="product-photo" value="0">
            <button type="submit" name="make_available" class="btn btn-sm" style="background-color: var(--primary); color: white;">
              <i class="bi bi-plus-circle me-1"></i> Make Available
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php 
  endwhile; 
}
?>

<!-- Pagination controls -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Item requests pagination" class="mt-3">
  <ul class="pagination justify-content-center">
    <!-- Previous button -->
    <li class="page-item <?php if($current_page <= 1){ echo 'disabled'; } ?>">
      <a class="page-link" href="?page=<?php echo $current_page-1; ?>" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
    
    <?php
    // Define how many page links to show before and after the current page
    $max_visible_links = 2;
    
    // Always show first page
    if ($current_page > 1 + $max_visible_links) {
        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
        
        // Show ellipsis if there's a gap
        if ($current_page > 2 + $max_visible_links) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Show page links around current page
    $start_page = max(1, $current_page - $max_visible_links);
    $end_page = min($total_pages, $current_page + $max_visible_links);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        echo '<li class="page-item ' . ($current_page == $i ? 'active' : '') . '">';
        echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
        echo '</li>';
    }
    
    // Always show last page
    if ($current_page < $total_pages - $max_visible_links) {
        // Show ellipsis if there's a gap
        if ($current_page < $total_pages - $max_visible_links - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
    }
    ?>
    
    <!-- Next button -->
    <li class="page-item <?php if($current_page >= $total_pages){ echo 'disabled'; } ?>">
      <a class="page-link" href="?page=<?php echo $current_page+1; ?>" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<script>
// Use event delegation for pagination links
document.addEventListener('DOMContentLoaded', function() {
  // Initial setup of the container that will hold all requests
  const requestsContainer = document.getElementById('requests-container');
  if (!requestsContainer) {
    console.error('Requests container not found, make sure to add id="requests-container" to your container element');
    return;
  }
  
  // Use event delegation to handle pagination clicks
  document.body.addEventListener('click', function(e) {
    // Find if a pagination link was clicked
    const target = e.target.closest('.pagination .page-link');
    if (target) {
      e.preventDefault();
      const href = target.getAttribute('href');
      if (href) {
        const pageParam = new URLSearchParams(href.split('?')[1]);
        const page = pageParam.get('page');
        
        // Update URL without refreshing the page
        window.history.pushState({page: page}, '', `?page=${page}`);
        
        // Use AJAX to load the content
        fetch(`load_requests.php?page=${page}`)
          .then(response => response.text())
          .then(html => {
            requestsContainer.innerHTML = html;
          })
          .catch(error => {
            console.error('Error loading requests:', error);
          });
      }
    }
  });
  
  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    const page = e.state && e.state.page ? e.state.page : 1;
    
    fetch(`load_requests.php?page=${page}`)
      .then(response => response.text())
      .then(html => {
        requestsContainer.innerHTML = html;
      })
      .catch(error => {
        console.error('Error loading requests:', error);
      });
  });
});
  </script>