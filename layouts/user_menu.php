<!-- Sidebar Container -->
<div class="sidebar-container d-flex flex-column h-100">
  <!-- Fixed Navigation Section -->
  <div class="sidebar-nav flex-grow-1 overflow-auto" style="position: sticky; top: 0;">
    <!-- Dashboard -->
    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark" href="home.php">
          <i class="bi bi-house-door-fill me-2 fs-5" style="color: var(--primary)"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <!-- User Management -->
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark" href="user_inventory.php">
          <i class="bi bi-box-seam  me-2 fs-5" style="color: var(--primary)"></i>
          <span>Inventory Management</span>
        </a>
      </li>
    </ul>
  </div>
  
  <!-- Date and Time Section - Fixed at Bottom -->
  <div class="sidebar-footer border-top mt-auto pt-3">
    <div class="date-item d-flex align-items-center">
      <i class="bi bi-calendar-event me-2" style="color: var(--primary)"></i>
      <span id="current-date">
        <?php
          date_default_timezone_set('Asia/Manila');
          echo date("F j, Y");
        ?>
      </span>
    </div>
    <div class="time-item d-flex align-items-center mt-1">
      <i class="bi bi-clock me-2" style="color: var(--primary)"></i>
      <span id="current-time">
        <?php echo date("g:i a"); ?>
      </span>
    </div>
    <div class="version-info mt-1">
      <small class="text-muted">Version 1.4 @MKD Com-Lab</small>
    </div>
  </div>
</div>



<!-- Sidebar Navigation -->
<!-- <ul class="nav flex-column"> -->
  <!-- Dashboard -->
  <!-- <li class="nav-item mb-2">
    <a class="nav-link d-flex align-items-center text-dark" href="home.php">
      <i class="bi bi-house-door-fill me-2 fs-5 text-primary"></i>
      <span>Dashboard</span>
    </a>
  </li>
  
  <li class="nav-item mb-2">
    <a class="nav-link d-flex align-items-center text-dark" href="user_inventory.php">
      <i class="bi bi-box-seam me-2 fs-5 text-primary"></i>
      <span>Inventory Management</span>
    </a>
  </li>
  
</ul> -->
