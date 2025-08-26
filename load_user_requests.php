<?php
// Include database connection and necessary functions
require_once('includes/load.php');

// Check if user is logged in
if (!$session->isUserLoggedIn()) {
  redirect('index.php', false);
}

// Get current user ID from session
$user_id = (int)$_SESSION['user_id'];

// Pagination configuration
$items_per_page = 5; // You can adjust this as needed
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Count total requests for this user
$total_rows = $db->query("SELECT COUNT(*) as count FROM item_requests WHERE user_id = {$user_id}")->fetch_assoc()['count'];
$total_pages = ceil($total_rows / $items_per_page);

// Fetch paginated user requests with category name
$user_requests = find_requests_by_user_with_pagination($user_id, $offset, $items_per_page);
?>

<div class="card-body p-0">
  <?php if (empty($user_requests)): ?>
    <div class="p-5 text-center">
      <div style="width: 80px; height: 80px; background-color: rgba(8, 8, 101, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
        <i class="bi bi-inbox-fill fs-1" style="color: var(--primary);"></i>
      </div>
      <h4 style="color: var(--primary); font-weight: 600;">No Requests Found</h4>
      <p class="text-muted mb-4">You haven't made any requests yet.</p>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
        <i class="bi bi-plus-circle me-1"></i> Make Your First Request
      </button>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background-color: rgba(8, 8, 101, 0.05);">
          <tr>
            <th class="ps-3">Item Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Request Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($user_requests as $request): ?>
            <tr>
              <td class="ps-3">
                <span style="font-weight: 500;"><?= remove_junk(ucfirst($request['item_name'])); ?></span>
              </td>
              <td>
                <span class="badge rounded-pill" style="background-color: rgba(8, 8, 101, 0.1); color: var(--primary);">
                  <?= remove_junk(ucfirst($request['category_name'])); ?>
                </span>
              </td>
              <td>
                <span class="badge bg-light text-dark"><?= (int)$request['quantity']; ?></span>
              </td>
              <td>
                <?php if ($request['status'] == 'Pending'): ?>
                  <span class="badge" style="background-color: #FFC107; color: #212529;">
                    <i class="bi bi-hourglass-split me-1"></i> Pending
                  </span>
                <?php elseif ($request['status'] == 'Approved'): ?>
                  <span class="badge" style="background-color: #0DCAF0;">
                    <i class="bi bi-check-circle me-1"></i> Approved
                  </span>
                <?php elseif ($request['status'] == 'Added'): ?>
                  <span class="badge" style="background-color: #198754;">
                    <i class="bi bi-check-all me-1"></i> Added to Inventory
                  </span>
                <?php elseif ($request['status'] == 'Denied'): ?>
                  <span class="badge" style="background-color: #DC3545;">
                    <i class="bi bi-x-circle me-1"></i> Denied
                  </span>
                <?php else: ?>
                  <span class="badge bg-secondary">
                    <?= $request['status']; ?>
                  </span>
                <?php endif; ?>
              </td>
              <td><?= read_date($request['request_date']); ?></td>
              <td>
                <!-- Details Button -->
                <button type="button" class="btn btn-sm main-btn" 
                        data-bs-toggle="modal" 
                        data-bs-target="#descriptionModal<?= $request['id']; ?>">
                  <i class="bi bi-info-circle me-1"></i> Details
                </button>
                
                <!-- Description Modal -->
                <div class="modal fade" id="descriptionModal<?= $request['id']; ?>" tabindex="-1" aria-labelledby="descriptionModalLabel<?= $request['id']; ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                      <div class="modal-header" style="background: linear-gradient(90deg, var(--primary) 0%, rgba(8, 8, 101, 0.85) 100%); color: white;">
                        <h5 class="modal-title" id="descriptionModalLabel<?= $request['id']; ?>">
                          <i class="bi bi-info-circle me-2"></i> Request Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4">
                        <div class="row g-3">
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Item Name</label>
                            <p class="mb-0 fw-medium"><?= remove_junk(ucfirst($request['item_name'])); ?></p>
                          </div>
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Category</label>
                            <p class="mb-0"><?= remove_junk(ucfirst($request['category_name'])); ?></p>
                          </div>
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Quantity</label>
                            <p class="mb-0"><?= (int)$request['quantity']; ?></p>
                          </div>
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Status</label>
                            <p class="mb-0">
                              <?php if ($request['status'] == 'Pending'): ?>
                                <span class="badge" style="background-color: #FFC107; color: #212529;">
                                  <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                              <?php elseif ($request['status'] == 'Approved'): ?>
                                <span class="badge" style="background-color: #0DCAF0;">
                                  <i class="bi bi-check-circle me-1"></i> Approved
                                </span>
                              <?php elseif ($request['status'] == 'Added'): ?>
                                <span class="badge" style="background-color: #198754;">
                                  <i class="bi bi-check-all me-1"></i> Added to Inventory
                                </span>
                              <?php elseif ($request['status'] == 'Denied'): ?>
                                <span class="badge" style="background-color: #DC3545;">
                                  <i class="bi bi-x-circle me-1"></i> Denied
                                </span>
                              <?php else: ?>
                                <span class="badge bg-secondary">
                                  <?= $request['status']; ?>
                                </span>
                              <?php endif; ?>
                            </p>
                          </div>
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Request Date</label>
                            <p class="mb-0"><?= read_date($request['request_date']); ?></p>
                          </div>
                          <div class="col-6">
                            <label class="form-label text-muted small mb-1">Use User Location</label>
                            <p class="mb-0">
                              <?php if ($request['use_user_location'] == 1): ?>
                                <span class="badge bg-success">Yes</span>
                              <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                              <?php endif; ?>
                            </p>
                          </div>
                          <div class="col-12">
                            <label class="form-label text-muted small mb-1">Notes/Description</label>
                            <div class="p-2 bg-light rounded">
                              <?php if (!empty($request['description'])): ?>
                                <p class="mb-0 small"><?= nl2br(remove_junk($request['description'])); ?></p>
                              <?php else: ?>
                                <p class="text-muted mb-0 small"><i>No description provided</i></p>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
      <div class="card-footer bg-white border-0 d-flex justify-content-center pt-4">
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <!-- Previous Page Button -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= ($current_page <= 1) ? '#' : 'home.php?page='.($current_page-1) ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
              </a>
            </li>
            
            <!-- Page Numbers -->
            <?php
              $start_page = max(1, $current_page - 2);
              $end_page = min($total_pages, $current_page + 2);
              
              if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="home.php?page=1">1</a></li>';
                if ($start_page > 2) {
                  echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                }
              }
              
              for ($i = $start_page; $i <= $end_page; $i++) {
                echo '<li class="page-item '.($i == $current_page ? 'active' : '').'">
                        <a class="page-link" href="home.php?page='.$i.'">'.$i.'</a>
                      </li>';
              }
              
              if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                  echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="home.php?page='.$total_pages.'">'.$total_pages.'</a></li>';
              }
            ?>
            
            <!-- Next Page Button -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= ($current_page >= $total_pages) ? '#' : 'home.php?page='.($current_page+1) ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
              </a>
            </li>
          </ul>
        </nav>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php
// Helper function to find user requests with pagination (if not already defined in your includes)
// You can put this in your functions.php file
function find_requests_by_user_with_pagination($user_id, $offset, $limit) {
  global $db;
  
  $sql = "SELECT r.*, c.name as category_name 
          FROM item_requests r 
          LEFT JOIN categories c ON c.id = r.categorie_id 
          WHERE r.user_id = '{$user_id}'
          ORDER BY r.request_date DESC 
          LIMIT {$offset}, {$limit}";
          
  return $db->query($sql);
}
?>