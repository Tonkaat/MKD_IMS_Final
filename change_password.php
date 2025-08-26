<?php
  $page_title = 'Change Password';
  require_once('includes/load.php');
  page_require_level(3);
?>
<?php $user = current_user(); ?>
<?php
  if (isset($_POST['update'])) {
    $req_fields = array('new-password', 'old-password', 'id');
    validate_fields($req_fields);

    if (empty($errors)) {
      if (sha1($_POST['old-password']) !== current_user()['password']) {
        $session->msg('d', "Your old password does not match.");
        redirect('change_password.php', false);
      }

      $id = (int)$_POST['id'];
      $new = remove_junk($db->escape(sha1($_POST['new-password'])));
      $sql = "UPDATE users SET password ='{$new}' WHERE id='{$db->escape($id)}'";
      $result = $db->query($sql);
      if ($result && $db->affected_rows() === 1) {
        $session->logout();
        $session->msg('s', "Login with your new password.");
        redirect('index.php', false);
      } else {
        $session->msg('d', 'Sorry, failed to update password!');
        redirect('change_password.php', false);
      }
    } else {
      $session->msg("d", $errors);
      redirect('change_password.php', false);
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
  <div class="row justify-content-center">
    <div class="col-md-6">
      <?php echo display_msg($msg); ?>
      <div class="card">
        <div class="card-header cont-head">
          <i class="bi bi-key" style="color: var(--secondary)"></i>
          <strong>Change Your Password</strong>
        </div>
        <div class="card-body">
          <form method="post" action="change_password.php" class="clearfix">
            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input type="password" class="form-control" name="new-password" id="newPassword" placeholder="Enter new password" required>
            </div>
            <div class="mb-3">
              <label for="oldPassword" class="form-label">Old Password</label>
              <input type="password" class="form-control" name="old-password" id="oldPassword" placeholder="Enter old password" required>
            </div>
            <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
            <div class="d-grid gap-2">
              <button type="submit" name="update" class="btn secondary-btn">
                <i class="bi bi-lock-fill me-1"></i> Change Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>