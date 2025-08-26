     </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      <script src="libs/js/admin-notifications.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>
  <script type="text/javascript" src="libs/js/functions.js"></script>
  <script>
  /**
   * Show a toast notification
   * 
   * @param {string} title - Notification title
   * @param {string} message - Notification message
   * @param {string} type - Notification type: 'info', 'success', 'warning', 'danger'
   * @param {number} delay - Auto-hide delay in milliseconds (default: 5000)
   */
  function showToastNotification(title, message, type = 'info', delay = 5000) {
    const toast = document.getElementById('scannerToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastTime = document.getElementById('toastTime');
    
    if (!toast || !toastTitle || !toastMessage || !toastTime) return;
    
    // Set toast content
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    toastTime.textContent = 'just now';
    
    // Set toast styling based on type
    const toastHeader = toast.querySelector('.toast-header');
    if (toastHeader) {
      // Remove any existing type classes
      toastHeader.classList.remove('toast-info', 'toast-success', 'toast-warning', 'toast-danger');
      
      // Add the appropriate type class
      toastHeader.classList.add(`toast-${type}`);
    }
    
    // Show the toast
    const bsToast = new bootstrap.Toast(toast, { delay: delay });
    bsToast.show();
  }
  
  // Listen for custom events to show toast from anywhere in the application
  document.addEventListener('mkd:notification', function(event) {
    const { title, message, type, delay } = event.detail;
    showToastNotification(title, message, type, delay);
  });
  
  // document.dispatchEvent(new CustomEvent('mkd:notification', { 
  //   detail: {
  //     title: 'New Request', 
  //     message: 'Item request submitted successfully', 
  //     type: 'success'
  //   }
  // }));

</script>
  </body>
</html>

<?php if(isset($db)) { $db->db_disconnect(); } ?>
