    document.addEventListener("DOMContentLoaded", () => {
    // Elements
    const scanBtn = document.getElementById("scanBtn");
    const barcodeModal = new bootstrap.Modal(document.getElementById("barcodeModal"));
    const hiddenInput = document.getElementById("hiddenBarcodeInput");
    const barcodeFeedback = document.getElementById("barcodeFeedback");
    const scanningStatus = document.getElementById("scanningStatus");
    const scannedItemsList = document.getElementById("scannedItemsList");
    const noItemsRow = document.getElementById("noItemsRow");
    const checkoutBtn = document.getElementById("checkoutBtn");
    const clearAllBtn = document.getElementById("clearAllBtn");
    const totalQtyEl = document.getElementById("totalQty");
    
    // Quick quantity panel elements
    const quickQtyPanel = document.getElementById("quickQtyPanel");
    const quickQtyProduct = document.getElementById("quickQtyProduct");
    const quickQtyInput = document.getElementById("quickQtyInput");
    const quickQtyAvailable = document.getElementById("quickQtyAvailable");
    const incQtyBtn = document.getElementById("incQty");
    const decQtyBtn = document.getElementById("decQty");
    const confirmQuickQtyBtn = document.getElementById("confirmQuickQty");
    const closeQuickQtyBtn = document.getElementById("closeQuickQty");
    
    // Toast elements
    const scannerToastEl = document.getElementById("scannerToast");
    const scannerToast = scannerToastEl ? new bootstrap.Toast(scannerToastEl) : null;
    const toastTitle = document.getElementById("toastTitle");
    const toastMessage = document.getElementById("toastMessage");

    // Check if it's initialized
    if (!scannerToast) {
        console.error("Failed to initialize toast");
    }
    
    // Data structures
    let scannedItems = [];
    let currentEditItemId = null;
    
    // Initialize
    scanBtn.addEventListener("click", () => {
        resetScannerUI();
        barcodeModal.show();
        setTimeout(() => hiddenInput.focus(), 500);
    });
    
    // Focus management
    document.getElementById('barcodeModal').addEventListener('shown.bs.modal', () => {
        hiddenInput.value = "";
        hiddenInput.focus();
    });
    
    document.getElementById('barcodeModal').addEventListener('click', () => {
        if (!document.activeElement.matches('input[type="number"], button')) {
            setTimeout(() => hiddenInput.focus(), 100);
        }
    });
    
    // Handle barcode input
    hiddenInput.addEventListener("input", function() {
        if (this.value.includes('\n') || this.value.includes('\r')) {
            processBarcode(this.value.trim().replace(/[\r\n]/g, ''));
            this.value = "";
        }
    });
    
    hiddenInput.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            processBarcode(this.value.trim());
            this.value = "";
            e.preventDefault();
        }
    });
    
    // Process barcode
    function processBarcode(barcode) {
        if (!barcode) return;
        
        updateScanFeedback("Searching...", "info");
        
        fetch(`get_product_by_barcode.php?barcode=${encodeURIComponent(barcode)}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! Status: ${res.status}`);
                }
                // Debug the raw response text
                return res.text().then(text => {
                    console.log("Raw API response:", text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("JSON Parse Error:", e);
                        console.error("Problematic text:", text);
                        throw new Error("Invalid JSON response");
                    }
                });
            })
            .then(data => {
                if (data.error) {
                    updateScanFeedback(`Product not found: ${barcode}`, "error");
                    showToast("Error", `No product found with barcode: ${barcode}`, "danger");
                } else {
                    // Check if product is already in list
                    const existingItemIndex = scannedItems.findIndex(item => item.id === data.id);
                    
                    if (existingItemIndex !== -1) {
                        // Increment quantity if stock allows
                        if (scannedItems[existingItemIndex].quantity < scannedItems[existingItemIndex].available) {
                            scannedItems[existingItemIndex].quantity += 1;
                            updateItemRow(existingItemIndex);
                            highlightRow(scannedItems[existingItemIndex].id);
                        } else {
                            showToast("Maximum Quantity", "Cannot add more - stock limit reached", "warning");
                        }
                    } else {
                        // Add new item
                        const newItem = {
                            id: data.id,
                            name: data.name,
                            quantity: 1,
                            available: parseInt(data.quantity || 0),
                            barcode: barcode
                        };
                        
                        scannedItems.push(newItem);
                        addItemToTable(newItem, scannedItems.length - 1);
                    }
                    
                    updateScanFeedback(`Added: ${data.name}`, "success");
                    showToast("Product Added", data.name, "success");
                    updateTotals();
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                updateScanFeedback("Error fetching product information", "error");
            })
            .finally(() => {
                // Re-focus the hidden input
                setTimeout(() => hiddenInput.focus(), 100);
            });
    }
    
    // Update scan feedback UI
    function updateScanFeedback(message, type) {
        barcodeFeedback.textContent = message;
        
        // Reset classes
        scanningStatus.classList.remove("alert-info", "alert-danger", "alert-success", "scanning-error", "scanning-success");
        
        // Apply appropriate styling
        switch(type) {
            case "error":
                scanningStatus.classList.add("alert-danger", "scanning-error");
                break;
            case "success":
                scanningStatus.classList.add("alert-success", "scanning-success");
                break;
            default:
                scanningStatus.classList.add("alert-info");
        }
        
        // Auto-reset to ready state after success/error
        if (type === "success" || type === "error") {
            setTimeout(() => {
                scanningStatus.classList.remove("alert-danger", "alert-success", "scanning-error", "scanning-success");
                scanningStatus.classList.add("alert-info");
                barcodeFeedback.textContent = "Ready to scan next item";
            }, 3000);
        }
    }
    
    // Add item to the table
    function addItemToTable(item, index) {
        // Hide the "no items" row if it's visible
        if (noItemsRow) {
            noItemsRow.style.display = 'none';
        }
        
        const row = document.createElement('tr');
        row.id = `item-row-${item.id}`;
        row.dataset.index = index;
        
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.name}</td>
            <td>
                <div class="input-group input-group-sm">
                    <button class="btn btn-outline-secondary decrement-btn" type="button" data-id="${item.id}">&minus;</button>
                    <input type="number" class="form-control text-center item-qty" value="${item.quantity}" min="1" max="${item.available}" data-id="${item.id}">
                    <button class="btn btn-outline-secondary increment-btn" type="button" data-id="${item.id}">&plus;</button>
                </div>
            </td>
            <td class="text-center">${item.available}</td>
            <td>
                <button class="btn btn-sm btn-outline-danger remove-item" data-id="${item.id}"><i class="bi bi-trash"></i></button>
            </td>
        `;
        
        scannedItemsList.appendChild(row);
        
        // Add event listeners to the new row
        attachRowEventListeners(row);
        
        // Enable checkout button if we have items
        checkoutBtn.disabled = false;
        
        // Highlight the new row
        highlightRow(item.id);
    }
    
    // Update an existing row
    function updateItemRow(index) {
        const item = scannedItems[index];
        const row = document.getElementById(`item-row-${item.id}`);
        
        if (row) {
            const qtyInput = row.querySelector('.item-qty');
            qtyInput.value = item.quantity;
        }
    }
    
    // Attach event listeners to row elements
    function attachRowEventListeners(row) {
        // Quantity input
        const qtyInput = row.querySelector('.item-qty');
        qtyInput.addEventListener('change', function() {
            const id = this.dataset.id;
            const index = scannedItems.findIndex(item => item.id == id);
            let newQty = parseInt(this.value);
            
            if (isNaN(newQty) || newQty < 1) {
                newQty = 1;
            } else if (newQty > scannedItems[index].available) {
                newQty = scannedItems[index].available;
                showToast("Maximum Quantity", "Cannot exceed available stock", "warning");
            }
            
            scannedItems[index].quantity = newQty;
            this.value = newQty;
            updateTotals();
        });
        
        // Increment button
        const incrementBtn = row.querySelector('.increment-btn');
        incrementBtn.addEventListener('click', function() {
            const id = this.dataset.id;
            const index = scannedItems.findIndex(item => item.id == id);
            
            if (scannedItems[index].quantity < scannedItems[index].available) {
                scannedItems[index].quantity++;
                updateItemRow(index);
                updateTotals();
            } else {
                showToast("Maximum Quantity", "Cannot exceed available stock", "warning");
            }
        });
        
        // Decrement button
        const decrementBtn = row.querySelector('.decrement-btn');
        decrementBtn.addEventListener('click', function() {
            const id = this.dataset.id;
            const index = scannedItems.findIndex(item => item.id == id);
            
            if (scannedItems[index].quantity > 1) {
                scannedItems[index].quantity--;
                updateItemRow(index);
                updateTotals();
            }
        });
        
        // Remove button
        const removeBtn = row.querySelector('.remove-item');
        removeBtn.addEventListener('click', function() {
            const id = this.dataset.id;
            removeItem(id);
        });
    }
    
    // Remove an item
    function removeItem(id) {
        const index = scannedItems.findIndex(item => item.id == id);
        if (index !== -1) {
            // Remove from array
            const removedItem = scannedItems.splice(index, 1)[0];
            
            // Remove from table
            const row = document.getElementById(`item-row-${id}`);
            if (row) {
                row.remove();
            }
            
            // Show "no items" row if no items left
            if (scannedItems.length === 0) {
                noItemsRow.style.display = '';
                checkoutBtn.disabled = true;
            } else {
                // Renumber remaining rows
                document.querySelectorAll('#scannedItemsList tr:not(#noItemsRow)').forEach((row, idx) => {
                    row.cells[0].textContent = idx + 1;
                });
            }
            
            updateTotals();
            showToast("Item Removed", `Removed: ${removedItem.name}`, "info");
        }
    }
    
    // Update totals
    function updateTotals() {
        const totalQty = scannedItems.reduce((sum, item) => sum + item.quantity, 0);
        totalQtyEl.textContent = totalQty;
    }
    
    // Highlight a newly scanned row
    function highlightRow(id) {
        const row = document.getElementById(`item-row-${id}`);
        if (row) {
            // Remove any existing highlights
            document.querySelectorAll('#scannedItemsList tr.last-scanned').forEach(r => {
                r.classList.remove('last-scanned');
            });
            
            // Add highlight
            row.classList.add('last-scanned');
        }
    }
    
    function showToast(title, message, type = "info") {
    // Make sure elements exist before using them
    if (!toastTitle || !toastMessage || !scannerToast) {
        console.error("Toast elements not properly initialized");
        return;
    }
    
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    
    // Get the actual DOM element of the toast if it's a Bootstrap object
    const toastElement = scannerToast._element || document.getElementById("scannerToast");
    
    // Check if the element has classList before using it
    if (toastElement && toastElement.classList) {
        // Remove existing color classes
        toastElement.classList.remove("bg-success", "bg-danger", "bg-warning", "bg-info");
        
        // Add color based on type
        switch(type) {
            case "success":
                toastElement.classList.add("bg-success", "text-white");
                break;
            case "danger":
                toastElement.classList.add("bg-danger", "text-white");
                break;
            case "warning":
                toastElement.classList.add("bg-warning", "text-dark");
                break;
            default:
                toastElement.classList.add("bg-info", "text-white");
        }
    }
    
    // Show the toast
    if (typeof scannerToast.show === 'function') {
        scannerToast.show();
    } else {
        console.error("Toast show method not found");
    }
}
    
    // Quick quantity panel functionality
    function openQuickQuantityPanel(itemId) {
        const item = scannedItems.find(item => item.id == itemId);
        if (!item) return;
        
        currentEditItemId = itemId;
        quickQtyProduct.textContent = item.name;
        quickQtyInput.value = item.quantity;
        quickQtyInput.max = item.available;
        quickQtyAvailable.textContent = item.available;
        
        quickQtyPanel.style.display = 'block';
        quickQtyInput.focus();
        quickQtyInput.select();
    }
    
    function closeQuickQuantityPanel() {
        quickQtyPanel.style.display = 'none';
        currentEditItemId = null;
        setTimeout(() => hiddenInput.focus(), 100);
    }
    
    // Quick quantity panel event listeners
    incQtyBtn.addEventListener('click', function() {
        let currentQty = parseInt(quickQtyInput.value);
        let maxQty = parseInt(quickQtyInput.max);
        
        if (currentQty < maxQty) {
            quickQtyInput.value = currentQty + 1;
        }
    });
    
    decQtyBtn.addEventListener('click', function() {
        let currentQty = parseInt(quickQtyInput.value);
        
        if (currentQty > 1) {
            quickQtyInput.value = currentQty - 1;
        }
    });
    
    confirmQuickQtyBtn.addEventListener('click', function() {
        if (!currentEditItemId) return;
        
        const index = scannedItems.findIndex(item => item.id == currentEditItemId);
        if (index !== -1) {
            const newQty = parseInt(quickQtyInput.value);
            if (!isNaN(newQty) && newQty >= 1 && newQty <= scannedItems[index].available) {
                scannedItems[index].quantity = newQty;
                updateItemRow(index);
                updateTotals();
                closeQuickQuantityPanel();
            }
        }
    });
    
    closeQuickQtyBtn.addEventListener('click', closeQuickQuantityPanel);
    
    // Clear all scanned items
    clearAllBtn.addEventListener('click', function() {
        if (scannedItems.length === 0) return;
        
        if (confirm("Are you sure you want to clear all scanned items?")) {
            scannedItems = [];
            scannedItemsList.innerHTML = '';
            scannedItemsList.appendChild(noItemsRow);
            noItemsRow.style.display = '';
            checkoutBtn.disabled = true;
            updateTotals();
            showToast("Cleared", "All items have been cleared", "info");
        }
    });
    
    // Handle checkout
    checkoutBtn.addEventListener('click', function() {
        if (scannedItems.length === 0) return;
        
        updateScanFeedback("Processing items...", "info");
        checkoutBtn.disabled = true;
        
        // Prepare data for server
        const checkoutData = {
            items: scannedItems.map(item => ({
                product_id: item.id,
                quantity: item.quantity
            }))
        };
        
        console.log("Sending checkout data:", checkoutData);
        
        // Send to server
        fetch('process_product_usage_bulk.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(checkoutData)
        })
        .then(response => {
            // Debug the raw response
            return response.text().then(text => {
                console.log("Raw server response:", text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("JSON Parse Error:", e);
                    console.error("Problematic text:", text);
                    throw new Error("Invalid JSON response from server");
                }
            });
        })
        .then(data => {
            console.log("Processed data:", data);
            if (data.success) {
                updateScanFeedback("Items processed successfully!", "success");
                showToast("Success", `${data.total_items} items have been processed`, "success");
                
                // Clear items after successful checkout
                setTimeout(() => {
                    scannedItems = [];
                    scannedItemsList.innerHTML = '';
                    scannedItemsList.appendChild(noItemsRow);
                    noItemsRow.style.display = '';
                    checkoutBtn.disabled = true;
                    updateTotals();
                    
                    // Close modal after a delay
                    setTimeout(() => barcodeModal.hide(), 1500);
                }, 1000);
            } else {
                updateScanFeedback("Processing failed: " + (data.message || "Unknown error"), "error");
                checkoutBtn.disabled = false;
                showToast("Error", data.message || "Failed to process items", "danger");
            }
        })
        .catch(error => {
            console.error('Error:', error);
            updateScanFeedback("Server error during processing", "error");
            checkoutBtn.disabled = false;
            showToast("Error", "Server error during processing", "danger");
        });
    });
    
    // Reset scanner UI
    function resetScannerUI() {
        barcodeFeedback.textContent = "Scan a product barcode to begin";
        scanningStatus.classList.remove("alert-danger", "alert-success", "scanning-error", "scanning-success");
        scanningStatus.classList.add("alert-info");
        
        // Keep existing items if any
        if (scannedItems.length === 0) {
            noItemsRow.style.display = '';
            checkoutBtn.disabled = true;
        } else {
            noItemsRow.style.display = 'none';
            checkoutBtn.disabled = false;
        }
        
        closeQuickQuantityPanel();
    }
});

// Keep the existing confirmAction function and add toast notification functionality
function confirmAction(action, requestId) {
  let confirmMessage = '';
  let actionUrl = '';
  let notificationType = '';
  let notificationTitle = '';
  let notificationMessage = '';
  
  if(action === 'approve') {
    confirmMessage = 'Are you sure you want to approve this request?';
    actionUrl = 'approve_requests.php';
    notificationType = 'success';
    notificationTitle = 'Request Approved';
    notificationMessage = 'The request has been approved successfully.';
  } else if(action === 'deny') {
    confirmMessage = 'Are you sure you want to deny this request?';
    actionUrl = 'deny_requests.php';
    notificationType = 'danger';
    notificationTitle = 'Request Denied';
    notificationMessage = 'The request has been denied.';
  }
  
  if(confirm(confirmMessage)) {
    // Create and submit a form programmatically
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'request_id';
    input.value = requestId;
    
    form.appendChild(input);
    document.body.appendChild(form);
    
    // Show toast notification
    showToastNotification(notificationTitle, notificationMessage, notificationType);
    
    // Submit the form
    form.submit();
  }
}

/**
 * Show a toast notification
 * @param {string} title - The notification title
 * @param {string} message - The notification message
 * @param {string} type - The notification type (success, danger, warning, info)
 */
function showToastNotification(title, message, type = 'info') {
  const toast = document.getElementById('scannerToast');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');
  const toastTime = document.getElementById('toastTime');
  
  if (!toast || !toastTitle || !toastMessage) {
    console.error('Toast elements not found in the DOM');
    return;
  }
  
  // Set toast content
  toastTitle.textContent = title;
  toastMessage.textContent = message;
  toastTime.textContent = 'just now';
  
  // Set toast styling based on type
  const toastHeader = toast.querySelector('.toast-header');
  if (toastHeader) {
    const bgColor = 
      type === 'danger' ? '#dc3545' : 
      type === 'warning' ? '#ffc107' : 
      type === 'success' ? '#28a745' : 'var(--primary)';
    
    const textColor = type === 'warning' ? '#212529' : 'white';
    
    toastHeader.style.backgroundColor = bgColor;
    toastHeader.style.color = textColor;
  }
  
  // Show the toast
  const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
  bsToast.show();
  
  // Play notification sound if enabled
  const soundEnabled = localStorage.getItem('mkd_notification_sound') !== 'false';
  if (soundEnabled) {
    let sound;
    if (type === 'danger' || type === 'warning') {
      sound = new Audio('libs/sounds/notif.wav');
    } else {
      sound = new Audio('libs/sounds/notif.wav');
    }
    sound.play().catch(e => console.log('Sound play prevented:', e));
  }
}

// Additional function to handle the "Make Available" action
document.addEventListener('DOMContentLoaded', function() {
  // Add event listener for form submissions to make items available
  const availabilityForms = document.querySelectorAll('form[action="request_available.php"]');
  
  availabilityForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      // The form will still submit normally since we're not calling preventDefault()
      // But we can show a toast notification
      const itemName = form.querySelector('input[name="product-title"]').value;
      showToastNotification(
        'Item Made Available', 
        `${itemName} has been added to inventory.`, 
        'success'
      );
    });
  });
  
  // Function to check for notification query parameters
  function checkForNotificationParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const notificationType = urlParams.get('notify_type');
    const notificationMessage = urlParams.get('notify_msg');
    
    if (notificationType && notificationMessage) {
      // Map notification types to titles
      const titles = {
        'success': 'Success',
        'info': 'Information',
        'warning': 'Warning',
        'danger': 'Error'
      };
      
      showToastNotification(
        titles[notificationType] || 'Notification',
        decodeURIComponent(notificationMessage),
        notificationType
      );
      
      // Remove notification parameters from URL without reloading
      const newUrl = window.location.pathname;
      history.pushState({}, document.title, newUrl);
    }
  }
  
  // Check for notification parameters when page loads
  checkForNotificationParams();
});