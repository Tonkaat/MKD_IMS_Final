/**
 * MKD Inventory System - Notification Bell Integration
 * This script connects the notification bell UI with the existing notification system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Reference to notification bell elements
    const notificationBell = document.getElementById('notificationBell');
    const notificationCount = document.getElementById('notificationCount');
    const notificationList = document.getElementById('notificationList');
    
    // Notification sound elements - reuse existing sounds
    const notificationSound = new Audio('libs/sounds/notif.wav');
    const warningSound = new Audio('libs/sounds/notif.wav');
    
    // Check if notifications and sound are enabled in localStorage
    let notificationsEnabled = localStorage.getItem('mkd_notifications_enabled') !== 'false';
    let soundEnabled = localStorage.getItem('mkd_notification_sound') !== 'false';
    
    // Add notification settings to the dropdown menu
    const addNotificationSettings = () => {
        // Create settings elements
        const settingsItem = document.createElement('li');
        settingsItem.className = 'dropdown-item p-2 border-bottom';
        settingsItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold">Notification Settings</span>
                <button id="mark-all-read" class="btn btn-sm btn-warning">
                    Mark all read
                </button>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notification-toggle" ${notificationsEnabled ? 'checked' : ''}>
                <label class="form-check-label" for="notification-toggle">Enable notifications</label>
            </div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="sound-toggle" ${soundEnabled ? 'checked' : ''}>
                <label class="form-check-label" for="sound-toggle">Enable sound</label>
            </div>
        `;
        
        // Insert settings at the top of the notification list
        if (notificationList.firstChild) {
            notificationList.insertBefore(settingsItem, notificationList.firstChild);
        } else {
            notificationList.appendChild(settingsItem);
        }
        
        // Add event listeners for settings toggles
        document.getElementById('notification-toggle').addEventListener('change', function() {
            notificationsEnabled = this.checked;
            localStorage.setItem('mkd_notifications_enabled', this.checked);
        });
        
        document.getElementById('sound-toggle').addEventListener('change', function() {
            soundEnabled = this.checked;
            localStorage.setItem('mkd_notification_sound', this.checked);
        });
        
        document.getElementById('mark-all-read').addEventListener('click', function(e) {
            e.preventDefault();
            markAllNotificationsAsRead();
        });
    };
    
    // Call to add settings to the dropdown
    addNotificationSettings();
    
    // Play notification sound based on type
    const playNotificationSound = (type) => {
        if (!soundEnabled) return;
        
        switch(type) {
            case 'warning':
            case 'danger':
                warningSound.play().catch(e => console.log('Sound play prevented:', e));
                break;
            default:
                notificationSound.play().catch(e => console.log('Sound play prevented:', e));
        }
    };
    
    // Update notification counter in the bell UI
    const updateNotificationCounter = (count) => {
        if (!notificationCount) return;
        
        notificationCount.textContent = count;
        
        // Show/hide based on count
        if (count > 0) {
            notificationCount.style.display = 'inline';
            // Add pulse animation if new notifications arrived
            notificationBell.classList.add('notification-animation');
            // Remove animation after 3 seconds
            setTimeout(() => {
                notificationBell.classList.remove('notification-animation');
            }, 3000);
        } else {
            notificationCount.style.display = 'none';
            notificationBell.classList.remove('notification-animation');
        }
    };
    
    // Format relative time for notifications
    const timeSince = (date) => {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        let interval = seconds / 31536000;
        if (interval > 1) {
            return Math.floor(interval) + " years ago";
        }
        
        interval = seconds / 2592000;
        if (interval > 1) {
            return Math.floor(interval) + " months ago";
        }
        
        interval = seconds / 86400;
        if (interval > 1) {
            return Math.floor(interval) + " days ago";
        }
        
        interval = seconds / 3600;
        if (interval > 1) {
            return Math.floor(interval) + " hours ago";
        }
        
        interval = seconds / 60;
        if (interval > 1) {
            return Math.floor(interval) + " minutes ago";
        }
        
        return "Just now";
    };
    
    // Get color for notification type
    const getColorForType = (type) => {
        switch(type) {
            case 'success': return '#28a745';
            case 'warning': return '#ffc107';
            case 'danger': return '#dc3545';
            case 'info': return 'var(--primary)';
            default: return 'var(--primary)';
        }
    };
    
    // Get icon for notification type and category
    const getNotificationIcon = (type, category) => {
        switch (category) {
            case 'inventory':
                return 'bi-box-seam';
            case 'request':
                return 'bi-send';
            case 'user':
                return 'bi-person';
            case 'system':
                return 'bi-gear';
            case 'alert':
                return 'bi-exclamation-triangle';
            default:
                // Default icons based on type
                switch (type) {
                    case 'success':
                        return 'bi-check-circle';
                    case 'warning':
                        return 'bi-exclamation-triangle';
                    case 'danger':
                        return 'bi-exclamation-octagon';
                    case 'info':
                    default:
                        return 'bi-info-circle';
                }
        }
    };
    
    // Render notifications in the dropdown
    const renderNotifications = (notifications) => {
        if (!notificationList) return;
        
        // Clear existing notifications but keep settings
        const settingsItem = notificationList.querySelector('.dropdown-item');
        notificationList.innerHTML = '';
        if (settingsItem) {
            notificationList.appendChild(settingsItem);
        }
        
        // Show message if no notifications
        if (notifications.length === 0) {
            const emptyItem = document.createElement('li');
            emptyItem.className = 'text-center text-muted small p-3';
            emptyItem.textContent = 'No new notifications';
            notificationList.appendChild(emptyItem);
            return;
        }
        
        // Add each notification to dropdown
        notifications.forEach((notification) => {
            const timeAgo = timeSince(new Date(notification.timestamp));
            const icon = getNotificationIcon(notification.type, notification.category);
            const color = getColorForType(notification.type);
            
            const notifItem = document.createElement('li');
            notifItem.className = `dropdown-item p-2 ${notification.read ? '' : 'unread'}`;
            notifItem.setAttribute('data-id', notification.id);
            
            notifItem.innerHTML = `
                <a href="${notification.link || '#'}" class="text-decoration-none text-dark">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-2">
                            <i class="bi ${icon}" style="color: ${color}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">${notification.title}</div>
                            <div class="small">${notification.message}</div>
                            <div class="text-muted smaller">${timeAgo} • ${notification.source || 'System'}</div>
                        </div>
                    </div>
                </a>
            `;
            
            notificationList.appendChild(notifItem);
            
            // Add divider after each item except the last
            if (notification !== notifications[notifications.length - 1]) {
                const divider = document.createElement('li');
                divider.className = 'dropdown-divider';
                notificationList.appendChild(divider);
            }
            
            // Add click event to mark as read
            notifItem.addEventListener('click', function() {
                markNotificationAsRead(notification.id);
            });
        });
    };
    
    // Mark individual notification as read
    const markNotificationAsRead = (notificationId) => {
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to mark notification as read
                const notifItem = notificationList.querySelector(`[data-id="${notificationId}"]`);
                if (notifItem) {
                    notifItem.classList.remove('unread');
                }
                
                // Update counter
                const unreadItems = notificationList.querySelectorAll('.unread');
                updateNotificationCounter(unreadItems.length);
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    };
    
    // Mark all notifications as read
    const markAllNotificationsAsRead = () => {
        fetch('mark_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI - remove unread class from all items
                const unreadItems = notificationList.querySelectorAll('.unread');
                unreadItems.forEach(item => item.classList.remove('unread'));
                
                // Reset counter
                updateNotificationCounter(0);
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    };
    
    // Show toast notification (reuse existing function from admin-notifications.js)
    const showToast = (notification) => {
        const toast = document.getElementById('scannerToast');
        const toastTitle = document.getElementById('toastTitle');
        const toastMessage = document.getElementById('toastMessage');
        
        if (!toast || !toastTitle || !toastMessage) {
            // If toast elements don't exist, try to create them dynamically
            createToastIfNotExists();
            // Try again with the newly created elements
            return showToast(notification);
        }
        
        // Set toast content
        toastTitle.textContent = notification.title;
        toastMessage.textContent = notification.message;
        
        // Set toast styling based on type
        const toastHeader = toast.querySelector('.toast-header');
        if (toastHeader) {
            const bgColor = notification.type === 'danger' ? '#dc3545' : 
                          notification.type === 'warning' ? '#ffc107' : 
                          notification.type === 'success' ? '#28a745' : 'var(--primary)';
            
            const textColor = notification.type === 'warning' ? '#212529' : 'white';
            
            toastHeader.style.backgroundColor = bgColor;
            toastHeader.style.color = textColor;
        }
        
        // Show the toast
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
    };
    
    // Create toast elements if they don't exist
    const createToastIfNotExists = () => {
        if (document.getElementById('scannerToast')) return;
        
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '11';
        
        const toast = document.createElement('div');
        toast.id = 'scannerToast';
        toast.className = 'toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto" id="toastTitle">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toastMessage">
                Notification message
            </div>
        `;
        
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
    };
    
    // Add notification styles
    const addNotificationStyles = () => {
        const style = document.createElement('style');
        style.textContent = `
            #notificationList {
                min-width: 320px;
                max-height: 400px;
                overflow-y: auto;
                padding: 0;
            }
            
            #notificationList .unread {
                background-color: rgba(13, 71, 161, 0.1);
                border-left: 3px solid var(--primary);
            }
            
            #notificationList .dropdown-item:hover {
                background-color: rgba(13, 71, 161, 0.05);
            }
            
            .smaller {
                font-size: 0.75rem;
            }
            
            @keyframes notification-pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            
            .notification-animation {
                animation: notification-pulse 1s infinite;
            }
        `;
        document.head.appendChild(style);
    };
    
    // Storage for notification cache with more detailed tracking
    let notificationsCache = {
        lastUpdate: 0,
        items: [],
        requests: [],
        lowStock: [],
        missingItems: [],
        lastNotificationIds: new Set()
    };
    
    // Process notifications data from server
    const processNotifications = (data) => {
        // Calculate unread count
        const unreadCount = data.items.filter(item => !item.read).length;
        updateNotificationCounter(unreadCount);
        
        // Check for new notifications (only if we've fetched before)
        if (notificationsCache.lastUpdate > 0) {
            // Find truly new notifications by comparing IDs and read status
            const newNotifications = data.items.filter(notification => {
                // Check if this is a completely new notification or one that became unread
                const wasInCache = notificationsCache.lastNotificationIds.has(notification.id);
                const existingNotification = notificationsCache.items.find(n => n.id === notification.id);
                
                // It's new if:
                // 1. It wasn't in cache before, OR
                // 2. It was read before but now it's unread (shouldn't happen normally), OR
                // 3. It's unread and we haven't processed it as unread before
                return !wasInCache || 
                       (existingNotification && existingNotification.read && !notification.read) ||
                       (!notification.read && !notificationsCache.items.some(n => n.id === notification.id && !n.read));
            });
            
            // Show notifications if enabled and there are new ones
            if (newNotifications.length > 0 && notificationsEnabled) {
                // Find highest priority notification
                const priorityOrder = ['info', 'success', 'warning', 'danger'];
                const highestPriorityNotification = newNotifications.reduce((highest, notification) => {
                    const currentPriority = priorityOrder.indexOf(notification.type);
                    const highestPriority = priorityOrder.indexOf(highest.type);
                    return currentPriority > highestPriority ? notification : highest;
                });
                
                // Play sound based on priority
                playNotificationSound(highestPriorityNotification.type);
                
                // Show toast for each new notification (limited to prevent spam)
                const toastLimit = 3; // Maximum toasts to show at once
                newNotifications.slice(0, toastLimit).forEach((notification, index) => {
                    setTimeout(() => {
                        showToast(notification);
                    }, index * 1000); // Show toasts 1 second apart
                });
                
                // If there are more notifications than the toast limit, show a summary
                if (newNotifications.length > toastLimit) {
                    setTimeout(() => {
                        showToast({
                            title: 'Multiple Notifications',
                            message: `You have ${newNotifications.length - toastLimit} more notifications`,
                            type: 'info'
                        });
                    }, toastLimit * 1000);
                }
            }
            
            // Check for low stock changes
            if (data.lowStock.length > notificationsCache.lowStock.length) {
                const newItemsCount = data.lowStock.length - notificationsCache.lowStock.length;
                if (notificationsEnabled) {
                    playNotificationSound('warning');
                    showToast({
                        title: 'Low Stock Alert',
                        message: `${newItemsCount} new items are running low on stock`,
                        type: 'warning'
                    });
                }
            }
            
            // Check for missing items changes
            if (data.missingItems.length > notificationsCache.missingItems.length) {
                const newItemsCount = data.missingItems.length - notificationsCache.missingItems.length;
                if (notificationsEnabled) {
                    playNotificationSound('danger');
                    showToast({
                        title: 'Missing Items Alert',
                        message: `${newItemsCount} new items marked as missing/lost`,
                        type: 'danger'
                    });
                }
            }
            
            // Check for new pending requests
            const pendingRequests = data.requests.filter(req => req.status === 'Pending');
            const oldPendingRequests = notificationsCache.requests.filter(req => req.status === 'Pending');
            
            if (pendingRequests.length > oldPendingRequests.length) {
                const newRequestsCount = pendingRequests.length - oldPendingRequests.length;
                if (notificationsEnabled) {
                    playNotificationSound('info');
                    showToast({
                        title: 'New Requests',
                        message: `${newRequestsCount} new item requests pending approval`,
                        type: 'info'
                    });
                }
            }
        }
        
        // Update cache with new data
        notificationsCache = {
            ...data,
            lastUpdate: Date.now(),
            lastNotificationIds: new Set(data.items.map(n => n.id))
        };
        
        // Render notifications in dropdown
        renderNotifications(data.items);
    };
    
    // Fetch notifications from server with error handling
    const fetchNotifications = () => {
        fetch('get_notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                processNotifications(data);
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                // Show error notification if notifications are enabled
                if (notificationsEnabled) {
                    showToast({
                        title: 'Connection Error',
                        message: 'Failed to fetch notifications. Check your connection.',
                        type: 'danger'
                    });
                }
            });
    };
    
    // Initialize notification system
    const initNotifications = () => {
        // Add styles
        addNotificationStyles();
        
        // Create toast container if it doesn't exist
        createToastIfNotExists();
        
        // Fetch notifications immediately
        fetchNotifications();
        
        // Set up polling interval (every 5 seconds)
        setInterval(fetchNotifications, 5000);
        
        // Handle visibility change to fetch immediately when tab becomes active
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Wait a bit before fetching to avoid spam when switching tabs
                setTimeout(fetchNotifications, 1000);
            }
        });
    };
    
    // Start the notification system
    initNotifications();
}); 