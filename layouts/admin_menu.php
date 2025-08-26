<!-- Sidebar Container -->
<div class="sidebar-container d-flex flex-column h-100">
  <!-- Fixed Navigation Section -->
  <div class="sidebar-nav flex-grow-1 overflow-auto" style="position: sticky; top: 0;">
    <!-- Dashboard -->
    <ul class="nav flex-column">
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark" href="admin.php">
          <i class="bi bi-house-door-fill me-2 fs-5" style="color: var(--primary)"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <!-- User Management -->
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark" href="users.php">
          <i class="bi bi-person-fill me-2 fs-5" style="color: var(--primary)"></i>
          <span>User Management</span>
        </a>
      </li>
      <!-- Inventory Management -->
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark submenu-toggle collapsed" href="#inventorySubmenu" aria-expanded="false">
          <i class="bi bi-box-seam me-2 fs-5" style="color: var(--primary)"></i>
          <span>Inventory Management</span>
          <i class="bi bi-chevron-down ms-auto submenu-indicator"></i>
        </a>
        <div class="submenu" id="inventorySubmenu">
          <ul class="nav flex-column ms-3 mt-2">
            <li class="nav-item"><a class="nav-link py-2" href="product.php">View Inventory</a></li>
            <li class="nav-item"><a class="nav-link py-2" href="categorie.php">Categories</a></li>
          </ul>
        </div>
      </li>
      <!-- Reports -->
      <li class="nav-item mb-2">
        <a class="nav-link d-flex align-items-center text-dark" href="generate_report.php">
          <i class="bi bi-clipboard-data-fill me-2 fs-5" style="color: var(--primary)"></i>
          <span>Reports</span>
        </a>
      </li>
      <!-- Settings -->
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center text-dark" href="print_barcode.php">
          <i class="bi bi-upc me-2 fs-5" style="color: var(--primary)"></i>
          <span>Barcodes</span>
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



<script>
document.addEventListener('DOMContentLoaded', function() {
  // Simple direct toggle functionality
  const submenuToggles = document.querySelectorAll('.submenu-toggle');
  
  submenuToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Get the target submenu
      const targetId = this.getAttribute('href');
      const targetSubmenu = document.querySelector(targetId);
      
      // Toggle submenu directly
      if (targetSubmenu) {
        // Toggle the show class
        targetSubmenu.classList.toggle('show');
        
        // Update the toggle button state
        const isExpanded = targetSubmenu.classList.contains('show');
        this.classList.toggle('collapsed', !isExpanded);
        this.setAttribute('aria-expanded', isExpanded);
      }
    });
  });
  
  // Highlight current page in menu
  const currentPage = window.location.pathname.split('/').pop();
  if (currentPage) {
    const activeLink = document.querySelector(`.sidebar a[href="${currentPage}"]`);
    if (activeLink) {
      activeLink.classList.add('active');
      
      // If in submenu, show parent
      const parentSubmenu = activeLink.closest('.submenu');
      if (parentSubmenu) {
        parentSubmenu.classList.add('show');
        const parentToggle = document.querySelector(`[href="#${parentSubmenu.id}"]`);
        if (parentToggle) {
          parentToggle.classList.remove('collapsed');
          parentToggle.setAttribute('aria-expanded', 'true');
        }
      }
    }
  }
});

</script>