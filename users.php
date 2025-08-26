<?php
  $page_title = 'All Users';
  require_once('includes/load.php');
?>
<?php
// Check permission level
page_require_level(1);
// Fetch all users including locations
$all_users = find_all_user();
?>
<?php include_once('layouts/header.php'); ?>

<h2 class="fw-bold " style="color: var(--primary)">User Management</h2>

<div class="mb-3">
    <?php echo display_msg($msg); ?>
</div>

<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: var(--primary)">
      <h5 class="mb-0">
        <i class="bi bi-person-lines-fill me-2" style="color: var(--secondary)"></i> Users
      </h5>
      <a href="add_user.php" class="btn btn-sm rounded-pill" style="background-color: var(--secondary)">
        <i class="bi bi-person-plus me-2"></i> Add New User
      </a>
    </div>
    <div class="card-body p-3">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width: 60px;">#</th>
              <th>Name</th>
              <th>Username</th>
              <th class="text-center" style="width: 20%;">User Role</th>
              <th class="text-center" style="width: 20%;">Location</th> <!-- New Column for Location -->
              <th class="text-center" style="width: 15%;">Status</th>
              <th class="text-center" style="width: 20%;">Last Login</th>
              <th class="text-center" style="width: 100px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_users as $a_user): ?>
              <?php if ($a_user['group_name'] != "Admin"): ?>
                <tr>
                  <td class="text-center"><?php echo count_id(); ?></td>
                  <td><?php echo remove_junk(ucwords($a_user['user_name'])); ?></td>
                  <td><?php echo remove_junk(ucwords($a_user['username'])); ?></td>
                  <td class="text-center"><?php echo remove_junk(ucwords($a_user['group_name'])); ?></td>
                  <td class="text-center"><?php echo remove_junk(ucwords($a_user['location'])); ?></td>
                  <td class="text-center">
                    <?php if ($a_user['status'] === '1'): ?>
                      <span class="badge py-1 px-2" style="background-color:var(--secondary); color:var(--primary)">Active</span>
                    <?php else: ?>
                      <span class="badge bg-danger py-1 px-2">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo read_date($a_user['last_login']); ?></td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="edit_user.php?id=<?php echo (int)$a_user['id']; ?>" class="btn-sm btn main-btn" title="Edit">
                        <i class="bi bi-pencil-square fs-5"></i>
                      </a>
                      <a href="#" class="btn btn-sm btn-outline-danger" title="Remove" onclick="confirmDelete(<?php echo (int)$a_user['id']; ?>)">
                        <i class="bi bi-trash fs-5"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
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
        <p id="deleteConfirmMessage">Are you sure you want to delete this user?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function confirmDelete(userId) {
    const deleteUrl = 'delete_user.php?id=' + userId;
    document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
  }
</script>



<?php include_once('layouts/footer.php'); ?>
