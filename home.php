<?php
  $page_title = 'User Dashboard';
  require_once('includes/load.php');
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false);}
  
  // Get current user's ID
  $current_user_id = $_SESSION['user_id'];
  
  // Pagination
  $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $items_per_page = 6;
  $total_records = count_user_requests($current_user_id);
  $total_pages = ceil($total_records / $items_per_page);
  
  // Ensure current page is within valid range
  if($current_page < 1) $current_page = 1;
  if($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
  
  // Calculate OFFSET for SQL
  $offset = ($current_page - 1) * $items_per_page;
  
  // Fetch this user's requests with pagination
  $user_requests = find_by_sql("SELECT r.*, c.name as category_name 
                               FROM item_requests r 
                               LEFT JOIN categories c ON r.categorie_id = c.id 
                               WHERE r.user_id = '{$current_user_id}' 
                               ORDER BY r.request_date DESC 
                               LIMIT {$items_per_page} OFFSET {$offset}");
?>
<?php include_once('layouts/header.php'); ?>

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
   
    <!-- User Welcome Message -->
    <p class="lead" style="color: rgba(255,255,255,0.85); font-weight: 300; max-width: 600px; margin: 0 auto;">
      Welcome back, <span style="color: var(--secondary); font-weight: 500;"><?= ucfirst($user['name']); ?></span>! Your inventory management dashboard.
    </p>
   
    <!-- Decorative Line -->
    <div style="width: 150px; height: 4px; background: linear-gradient(to right, rgba(255,214,0,0), var(--secondary), rgba(255,214,0,0)); margin: 15px auto;"></div>
  </div>

  <!-- Dashboard Cards -->
  <div class="row g-4">
    <!-- My Requests Card -->
    <div class="col-md-4">
      <div class="sum-card-fixed shadow-sm h-100" style="background-color: var(--secondary)">
        <!-- Accent corner -->
        <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--primary); transform: rotate(45deg);">
          <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--secondary); font-weight: bold; font-size: 1rem;">
            <?php echo count_user_requests($current_user_id); ?>
          </span>
        </div>
      
        <div class="card-body p-4" style="position: relative; z-index: 1;">
          <div class="text-center mb-3">
            <i class="bi bi-list-check" style="color: var(--primary); font-size: 2.5rem;"></i>
          </div>
          <h2 class="text-center fw-bold mb-0" style="color: var(--primary); font-size: 1.6rem; letter-spacing: 0.5px;">My Requests</h2>
          <div style="width: 60px; height: 3px; background: var(--primary); margin: 8px auto;"></div>
          <p class="text-center mb-0" style="color: var(--primary); font-weight: 500; font-size: 1.1rem;">Total</p>
        </div>
      </div>
    </div>

    <!-- Pending Requests -->
    <div class="col-md-4">
      <div class="sum-card-fixed shadow-sm h-100" style="background-color: var(--primary)">
        <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%);"></span>
        <!-- Accent corner -->
        <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--secondary); transform: rotate(45deg);">
          <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--primary); font-weight: bold; font-size: 1rem;">
            <?php echo count_pending_requests($current_user_id); ?>
          </span>
        </div>
        
        <div class="card-body p-4" style="position: relative; z-index: 1;">
          <div class="text-center mb-3">
            <i class="bi bi-hourglass-split" style="color: var(--secondary); font-size: 2.5rem;"></i>
          </div>
          <h2 class="text-center fw-bold mb-0" style="color: white; font-size: 1.6rem; letter-spacing: 0.5px;">Pending</h2>
          <div style="width: 60px; height: 3px; background: var(--secondary); margin: 8px auto;"></div>
          <p class="text-center mb-0" style="color: white; font-weight: 500; font-size: 1.1rem;">Requests</p>
        </div>
      </div>
    </div>

    <!-- Approved Requests -->
    <div class="col-md-4">
      <div class="sum-card-fixed shadow-sm h-100" style="background-color: var(--secondary)">
        <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%);"></span>
        <!-- Accent corner -->
        <div style="position: absolute; top: -10px; right: -10px; width: 60px; height: 60px; background-color: var(--primary); transform: rotate(45deg);">
          <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); color: var(--secondary); font-weight: bold; font-size: 1rem;">
            <?php echo count_approved_requests($current_user_id); ?>
          </span>
        </div>
        
        <div class="card-body p-4" style="position: relative; z-index: 1;">
          <div class="text-center mb-3">
            <i class="bi bi-check-circle" style="color: var(--primary); font-size: 2.5rem;"></i>
          </div>
          <h2 class="text-center fw-bold mb-0" style="color: var(--primary); font-size: 1.6rem; letter-spacing: 0.5px;">Approved</h2>
          <div style="width: 60px; height: 3px; background: var(--primary); margin: 8px auto;"></div>
          <p class="text-center mb-0" style="color: var(--primary); font-weight: 500; font-size: 1.1rem;">Requests</p>
        </div>
      </div>
    </div>
  </div> 

  <!-- Request New Item Button -->
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
            data-bs-toggle="modal" 
            data-bs-target="#requestModal">
      <span style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%); transform: translateX(-100%); animation: shimmer 2s infinite;"></span>
      <i class="bi bi-plus-circle me-2" style="font-size: 1.3rem;"></i>
      <span>Request New Item</span>
    </button>
  </div>
</div>

<!-- Request Status Section -->
<div class="card shadow-sm border-0 mb-5">
  <div class="card-header cont-head border-bottom d-flex align-items-center" style="background: linear-gradient(90deg, var(--primary) 0%, rgba(8, 8, 101, 0.85) 100%); color: white;">
    <i class="bi bi-bell fs-5 me-2 symbol"></i>
    <h5 class="mb-0">Your Request Status</h5>
  </div>
  
  <?php echo display_msg($msg); ?>
  
  <!-- Wrap everything in user-requests-container -->
  <div id="user-requests-container">
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
  </div> <!-- End of user-requests-container -->
</div>

<!-- Modal: Request New Item -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="submit_request.php" method="POST" class="modal-content border-0 shadow">
      <div class="modal-header" style="background: linear-gradient(90deg, var(--primary) 0%, rgba(8, 8, 101, 0.85) 100%); color: white;">
        <h5 class="modal-title" id="requestModalLabel">
          <i class="bi bi-plus-circle me-2 symbol"></i> Request New Item
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="mb-4">
          <label for="item_name" class="form-label fw-bold">Item Name <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text" style="background-color: var(--primary); color: white;">
              <i class="bi bi-box"></i>
            </span>
            <input type="text" class="form-control" name="item_name" placeholder="Enter item name" required>
          </div>
        </div>

        <div class="mb-4">
          <label for="categorie_id" class="form-label fw-bold">Category <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text" style="background-color: var(--primary); color: white;">
              <i class="bi bi-tag "></i>
            </span>
            <select class="form-select" name="categorie_id" required>
              <option value="">Select a Category</option>
              <?php
                $categories = find_all('categories');
                foreach ($categories as $cat):
              ?>
                <option value="<?= (int)$cat['id']; ?>"><?= remove_junk($cat['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mb-4">
          <label for="quantity" class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text" style="background-color: var(--primary); color: white;">
              <i class="bi bi-123"></i>
            </span>
            <input type="number" class="form-control" name="quantity" value="1" min="1">
          </div>
          <div class="form-text">
            <i class="bi bi-info-circle me-1"></i> Please specify the quantity needed.
          </div>
        </div>

        <!-- New Notes/Description Field -->
        <div class="mb-4">
          <label for="description" class="form-label fw-bold">Notes/Description</label>
          <div class="input-group">
            <span class="input-group-text" style="background-color: var(--primary); color: white;">
              <i class="bi bi-sticky"></i>
            </span>
            <textarea class="form-control" name="description" rows="3" placeholder="Add any additional notes or description about your request"></textarea>
          </div>
        </div>

        <!-- New Checkbox for User Location -->
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="use_user_location" name="use_user_location" value="1">
          <label class="form-check-label" for="use_user_location">
            <i class="bi bi-geo-alt me-1"></i> Place item in my current location
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn main-btn" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancel
        </button>
        <button type="submit" class="btn secondary-btn">
          <i class="bi bi-send me-1"></i> Submit Request
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Add Confirmation Modal -->
<div class="modal fade" id="confirmRequestModal" tabindex="-1" aria-labelledby="confirmRequestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header" style="background: linear-gradient(90deg, var(--primary) 0%, rgba(8, 8, 101, 0.85) 100%); color: white;">
        <h5 class="modal-title" id="confirmRequestModalLabel">
          <i class="bi bi-check-circle me-2"></i> Confirm Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4">
        <div class="text-center mb-3">
          <div style="width: 70px; height: 70px; border-radius: 50%; background-color: rgba(8, 8, 101, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
            <i class="bi bi-clipboard-check fs-2" style="color: var(--primary);"></i>
          </div>
          <h4 class="mt-3 mb-4">Please review your request</h4>
        </div>
        
        <div class="table-responsive">
          <table class="table">
            <tbody>
              <tr>
                <th style="width: 35%; color: var(--primary);">Item Name:</th>
                <td id="confirm-item-name"></td>
              </tr>
              <tr>
                <th style="color: var(--primary);">Category:</th>
                <td id="confirm-category"></td>
              </tr>
              <tr>
                <th style="color: var(--primary);">Quantity:</th>
                <td id="confirm-quantity"></td>
              </tr>
              <tr>
                <th style="color: var(--primary);">Description:</th>
                <td id="confirm-description" style="white-space: pre-line;"></td>
              </tr>
              <tr id="confirm-location-row">
                <th style="color: var(--primary);">Use Your Location:</th>
                <td>
                  <span id="confirm-location"></span>
                  <div id="confirm-location-info" class="form-text"></div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="alert alert-info mt-3">
          <i class="bi bi-info-circle me-2"></i> Please make sure all details are correct before submitting your request.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" id="edit-request-btn" class="btn main-btn">
          <i class="bi bi-pencil-square me-1"></i> Edit Request
        </button>
        <button type="button" id="confirm-request-btn" class="btn secondary-btn">
          <i class="bi bi-check2-circle me-1"></i> Confirm & Submit
        </button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Get the request form
  const requestForm = document.querySelector('#requestModal form');
  
  // Add event listener to the form submission
  if (requestForm) {
    requestForm.addEventListener('submit', function(event) {
      // Prevent the form from submitting immediately
      event.preventDefault();
      
      // Get form values
      const itemName = requestForm.querySelector('input[name="item_name"]').value;
      const categorySelect = requestForm.querySelector('select[name="categorie_id"]');
      const categoryText = categorySelect.options[categorySelect.selectedIndex].text;
      const categoryId = categorySelect.value;
      const quantity = requestForm.querySelector('input[name="quantity"]').value;
      const description = requestForm.querySelector('textarea[name="description"]').value;
      const useUserLocation = requestForm.querySelector('input[name="use_user_location"]').checked;
      
      // Fill in the confirmation modal with these details
      document.getElementById('confirm-item-name').textContent = itemName;
      document.getElementById('confirm-category').textContent = categoryText;
      document.getElementById('confirm-quantity').textContent = quantity;
      document.getElementById('confirm-description').textContent = description || 'None provided';
      document.getElementById('confirm-location').textContent = useUserLocation ? 'Yes, use my current location' : 'No';
      
      // Initialize the consumable category check based on your actual consumable categories
      const consumableCategories = [12]; // Update with your actual consumable category IDs
      const isConsumable = consumableCategories.includes(parseInt(categoryId));
      
      // Show or hide the location information based on whether it's a consumable
      const locationInfo = document.getElementById('confirm-location-info');
      if (isConsumable) {
        locationInfo.innerHTML = '<i class="bi bi-info-circle"></i> Location not applicable for consumable items';
        document.getElementById('confirm-location-row').style.color = '#6c757d'; // Grey out
      } else {
        locationInfo.innerHTML = '';
        document.getElementById('confirm-location-row').style.color = '';
      }
      
      // Show the confirmation modal
      const confirmModal = new bootstrap.Modal(document.getElementById('confirmRequestModal'));
      confirmModal.show();
      
      // Hide the original modal
      const requestModal = bootstrap.Modal.getInstance(document.getElementById('requestModal'));
      requestModal.hide();
    });
  }
  
  // Handle "Edit" button in confirmation modal
  const editButton = document.getElementById('edit-request-btn');
  if (editButton) {
    editButton.addEventListener('click', function() {
      // Hide the confirmation modal
      const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmRequestModal'));
      confirmModal.hide();
      
      // Show the original modal
      const requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
      requestModal.show();
    });
  }
  
  // Handle "Confirm" button in confirmation modal
  const confirmButton = document.getElementById('confirm-request-btn');
  if (confirmButton) {
    confirmButton.addEventListener('click', function() {
      // Submit the original form
      document.querySelector('#requestModal form').submit();
    });
  }
});

// User side pagination script with event delegation
document.addEventListener('DOMContentLoaded', function() {
  // Initial setup of the container that will hold user requests
  const requestsContainer = document.getElementById('user-requests-container');
  if (!requestsContainer) {
    console.error('User requests container not found, make sure to add id="user-requests-container" to your container element');
    return;
  }
  
  // Use event delegation to handle pagination clicks
  document.body.addEventListener('click', function(e) {
    // Find if a pagination link was clicked
    const target = e.target.closest('.pagination .page-link');
    if (target) {
      // Make sure we're handling pagination within our user-requests-container
      if (target.closest('#user-requests-container')) {
        e.preventDefault();
        const href = target.getAttribute('href');
        if (href && href !== '#') {
          // Extract page number from href
          const pageMatch = href.match(/page=(\d+)/);
          const page = pageMatch ? pageMatch[1] : 1;
          
          // Update URL without refreshing the page
          window.history.pushState({page: page}, '', `home.php?page=${page}`);
          
          // Use AJAX to load the content
          fetch(`load_user_requests.php?page=${page}`)
            .then(response => response.text())
            .then(html => {
              requestsContainer.innerHTML = html;
            })
            .catch(error => {
              console.error('Error loading user requests:', error);
            });
        }
      }
    }
  });
  
  // Handle browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    const page = e.state && e.state.page ? e.state.page : 1;
    
    fetch(`load_user_requests.php?page=${page}`)
      .then(response => response.text())
      .then(html => {
        requestsContainer.innerHTML = html;
      })
      .catch(error => {
        console.error('Error loading user requests:', error);
      });
  });
});
</script>

<?php include_once('layouts/footer.php'); ?>



<!-- Add animation styles -->
<style>
  @keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
  }
  
  .sum-card {
    cursor: pointer;
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
  }
  
  
  .page-link {
    color: var(--primary);
  }
  
  .page-item.active .page-link {
    background-color: var(--primary);
    border-color: var(--primary);
  }
</style>