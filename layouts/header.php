<?php $user = current_user(); ?>
<!DOCTYPE html>
  <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title><?php if (!empty($page_title))
           echo remove_junk($page_title);
            elseif(!empty($user))
           echo ucfirst($user['name']);
            else echo "Inventory Management System";?>
    </title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
    <link rel="stylesheet" href="libs/css/main.css" />
    <link rel="stylesheet" href="libs/css/notification_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    
  </head>
  <body>
  <?php if ($session->isUserLoggedIn(true)): ?>
    <header id="header" class="shadow-sm px-4 py-3 mb-4 d-flex justify-content-between align-items-center" style="background: var(--primary);">
      <!-- Decorative Elements - small and non-intrusive -->
      <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255, 213, 0, 0.15); pointer-events: none;"></div>
      <div style="position: absolute; bottom: -20px; left: -20px; width: 80px; height: 80px; border-radius: 50%; background: rgba(255, 214, 0, 0.08); pointer-events: none;"></div>
      
      <!-- Light beam effect -->
      <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 50%); pointer-events: none;"></div>
      
      <!-- Logo -->
      <div class="d-flex align-items-center">
        <img src="libs/images/mkd-logo.png" alt="logo" class="header-logo">
        <span class="fs-4 fw-bold text-white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.2); ">
          <span style="position: relative; display: inline-block;">MKD</span>
          <span style="color: var(--secondary);"> Inventory</span> System
        </span>
      </div>


  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="scannerToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto" id="toastTitle">Notification Title</strong>
        <small class="text-body-secondary" id="toastTime">just now</small>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="toastMessage">
        Notification message goes here.
      </div>
    </div>
  </div>
</div>
            

      
<div class="d-flex align-items-center gap-4">
  <!-- Notification Bell -->
  <div class="position-relative">
    <a href="#" class="text-white position-relative" id="notificationBell" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-bell-fill fs-4"></i>
      <span id="notificationCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        0
      </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" id="notificationList" style="min-width: 320px; max-height: 400px; overflow-y: auto; border-radius: 8px; border-top: 2px solid rgb(238, 198, 0);">
      <li class="dropdown-item p-2 border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="fw-bold">Notification Settings</span>
          <button id="mark-all-read" class="btn btn-sm btn-warning">Mark all read</button>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="notification-toggle" checked>
          <label class="form-check-label" for="notification-toggle">Enable notifications</label>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="sound-toggle" checked>
          <label class="form-check-label" for="sound-toggle">Enable sound</label>
        </div>
      </li>
      <li class="text-center text-muted small p-3">No new notifications</li>
    </ul>
  </div>

  <!-- User Profile Dropdown -->
  <div class="dropdown">
    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="uploads/users/<?php echo $user['image'];?>" alt="user-image" class="rounded-circle me-2" width="35" height="35" style="border: 2px solid rgb(238, 198, 0);">
      <span class="fw-semibold text-white"><?php echo remove_junk(ucfirst($user['name'])); ?></span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2" style="border-radius: 8px; border: none; border-top: 2px solid rgb(238, 198, 0);">
      <li>
        <a class="dropdown-item" href="profile.php?id=<?php echo (int)$user['id']; ?>">
          <i class="bi bi-person-circle me-2" style="color: #102050;"></i> Profile
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item text-danger" href="logout.php">
          <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</div>
    </header>
  </header>
    <div class="sidebar">
      <?php if($user['user_level'] === '1'): ?>
        <?php include_once('admin_menu.php');?>
      <?php elseif($user['user_level'] === '2'): ?>
        <?php include_once('special_menu.php');?>
      <?php elseif($user['user_level'] === '3'): ?>
        <?php include_once('user_menu.php');?>
      <?php endif;?>
    </div>
<?php endif;?>

  <!-- Main Page -->
  <div class="page">
    <div class="container-fluid">
