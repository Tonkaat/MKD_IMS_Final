<?php
/**
 * MKD Inventory System - Notification Helper
 * 
 * This file provides functions to create and manage notifications
 * throughout the system. Include this file in any script that needs
 * to create notifications.
 */

require_once('includes/load.php');

/**
 * Add a notification to the system
 * 
 * @param string $title       The notification title
 * @param string $message     The notification message content
 * @param string $type        Notification type: 'info', 'success', 'warning', 'danger'
 * @param string $category    Category: 'inventory', 'request', 'user', 'system', 'alert'
 * @param int $user_id        User ID to target (0 for all users)
 * @param string $link        Optional URL to navigate to when clicked
 * @param int $source_id      Optional ID of the user creating the notification
 * @return int|bool           The new notification ID or false on failure
 */
function add_notification($title, $message, $type = 'info', $category = 'system', $user_id = 0, $link = null, $source_id = null) {
    global $db;
    
    // Validate notification type
    $valid_types = ['info', 'success', 'warning', 'danger'];
    if (!in_array($type, $valid_types)) {
        $type = 'info';
    }
    
    // Validate category
    $valid_categories = ['inventory', 'request', 'user', 'system', 'alert'];
    if (!in_array($category, $valid_categories)) {
        $category = 'system';
    }
    
    // Set source to current user if not specified
    if ($source_id === null) {
        $current_user = current_user();
        $source_id = $current_user ? $current_user['id'] : 0;
    }
    
    // Escape all inputs
    $title = $db->escape($title);
    $message = $db->escape($message);
    $type = $db->escape($type);
    $category = $db->escape($category);
    $user_id = (int)$user_id;
    $source_id = (int)$source_id;
    $link = $link ? $db->escape($link) : null;
    
    $sql = "INSERT INTO notifications (
        title, 
        message, 
        type, 
        category,
        user_id, 
        source_id,
        link, 
        timestamp, 
        is_read
    ) VALUES (
        '{$title}', 
        '{$message}', 
        '{$type}', 
        '{$category}',
        {$user_id}, 
        {$source_id},
        " . ($link ? "'{$link}'" : "NULL") . ", 
        NOW(), 
        0
    )";
    
    if ($db->query($sql)) {
        return $db->insert_id();
    } else {
        return false;
    }
}

/**
 * Add a notification to all admin users
 * 
 * @param string $title       The notification title
 * @param string $message     The notification message content
 * @param string $type        Notification type: 'info', 'success', 'warning', 'danger'
 * @param string $category    Category: 'inventory', 'request', 'user', 'system', 'alert'
 * @param string $link        Optional URL to navigate to when clicked
 * @return array              Array of notification IDs created
 */
function notify_admins($title, $message, $type = 'info', $category = 'system', $link = null) {
    global $db;
    
    // Get all admin users (level 1)
    $admin_query = $db->query("SELECT id FROM users WHERE user_level = 1");
    
    $notification_ids = [];
    
    if ($db->num_rows($admin_query) > 0) {
        while ($admin = $db->fetch_assoc($admin_query)) {
            $notification_id = add_notification(
                $title,
                $message,
                $type,
                $category,
                $admin['id'],
                $link
            );
            
            if ($notification_id) {
                $notification_ids[] = $notification_id;
            }
        }
    }
    
    return $notification_ids;
}

/**
 * Add a system-wide notification (visible to all users)
 * 
 * @param string $title       The notification title
 * @param string $message     The notification message content
 * @param string $type        Notification type: 'info', 'success', 'warning', 'danger'
 * @param string $category    Category: 'inventory', 'request', 'user', 'system', 'alert'
 * @param string $link        Optional URL to navigate to when clicked
 * @return int|bool           The new notification ID or false on failure
 */
function notify_all_users($title, $message, $type = 'info', $category = 'system', $link = null) {
    return add_notification(
        $title,
        $message,
        $type,
        $category,
        0, // user_id 0 means all users
        $link
    );
}

/**
 * Add notification about low stock items
 * 
 * @param int $product_id     The product ID that is low in stock
 * @param string $product_name The product name
 * @param int $quantity       Current quantity
 * @param int $threshold      Low stock threshold
 * @return int|bool           The new notification ID or false on failure
 */
function notify_low_stock($product_id, $product_name, $quantity, $threshold = 5) {
    $product_id = (int)$product_id;
    $product_url = "product.php?id={$product_id}";
    
    return notify_admins(
        "Low Stock Alert",
        "Product '{$product_name}' is running low on stock (Current: {$quantity}, Threshold: {$threshold})",
        "warning",
        "inventory",
        $product_url
    );
}

/**
 * Add notification about a new item request
 * 
 * @param int $request_id     The request ID
 * @param string $item_name   The requested item name
 * @param int $quantity       Requested quantity
 * @param int $user_id        User ID who made the request
 * @return int|bool           The new notification ID or false on failure
 */
function notify_new_request($request_id, $item_name, $quantity, $user_id) {
    $request_id = (int)$request_id;
    $request_url = "item_requests.php?id={$request_id}";
    $user = find_by_id('users', $user_id);
    $username = $user ? $user['username'] : 'Unknown user';
    
    return notify_admins(
        "New Item Request",
        "User {$username} has requested {$quantity} of '{$item_name}'",
        "info",
        "request",
        $request_url
    );
}

/**
 * Add notification about a request status change
 * 
 * @param int $request_id     The request ID
 * @param string $item_name   The requested item name
 * @param string $status      New status of the request
 * @param int $user_id        User ID to notify (requester)
 * @return int|bool           The new notification ID or false on failure
 */
function notify_request_status($request_id, $item_name, $status, $user_id) {
    $request_id = (int)$request_id;
    $request_url = "item_requests.php?id={$request_id}";
    
    $type = 'info';
    switch ($status) {
        case 'Approved':
            $type = 'success';
            break;
        case 'Denied':
            $type = 'danger';
            break;
        case 'Pending':
        default:
            $type = 'info';
    }
    
    return add_notification(
        "Request {$status}",
        "Your request for '{$item_name}' has been {$status}",
        $type,
        "request",
        $user_id,
        $request_url
    );
}

/**
 * Add notification about missing/lost items
 * 
 * @param int $product_id     The product ID that is missing
 * @param string $product_name The product name
 * @param string $status      Status ('missing' or 'lost')
 * @return int|bool           The new notification ID or false on failure
 */
function notify_missing_item($product_id, $product_name, $status = 'missing') {
    $product_id = (int)$product_id;
    $product_url = "product.php?id={$product_id}";
    
    $status_text = $status === 'lost' ? 'Lost' : 'Missing';
    
    return notify_admins(
        "{$status_text} Item Alert",
        "Product '{$product_name}' has been marked as {$status_text}",
        "danger",
        "alert",
        $product_url
    );
}

/**
 * Add notification about item return
 * 
 * @param int $checkout_id    The checkout ID
 * @param string $item_name   The item name
 * @param int $user_id        User ID who returned the item
 * @return int|bool           The new notification ID or false on failure
 */
function notify_item_return($checkout_id, $item_name, $user_id) {
    $checkout_id = (int)$checkout_id;
    $checkout_url = "checkout_history.php?id={$checkout_id}";
    $user = find_by_id('users', $user_id);
    $username = $user ? $user['username'] : 'Unknown user';
    
    return notify_admins(
        "Item Returned",
        "User {$username} has returned '{$item_name}'",
        "success",
        "inventory",
        $checkout_url
    );
}

/**
 * Add notification about item checkout
 * 
 * @param int $checkout_id    The checkout ID
 * @param string $item_name   The item name
 * @param int $quantity       Quantity checked out
 * @param int $user_id        User ID who checked out
 * @param string $due_date    Due date for return
 * @return int|bool           The new notification ID or false on failure
 */
function notify_item_checkout($checkout_id, $item_name, $quantity, $user_id, $due_date) {
    $checkout_id = (int)$checkout_id;
    $checkout_url = "checkout_history.php?id={$checkout_id}";
    $user = find_by_id('users', $user_id);
    $username = $user ? $user['username'] : 'Unknown user';
    
    return notify_admins(
        "Item Checked Out",
        "User {$username} has checked out {$quantity} of '{$item_name}' (Due: {$due_date})",
        "info",
        "inventory",
        $checkout_url
    );
}

/**
 * Add notification about an upcoming due date
 * 
 * @param int $checkout_id    The checkout ID
 * @param string $item_name   The item name
 * @param int $user_id        User ID to notify
 * @param string $due_date    Due date for return
 * @return int|bool           The new notification ID or false on failure
 */
function notify_upcoming_due_date($checkout_id, $item_name, $user_id, $due_date) {
    $checkout_id = (int)$checkout_id;
    $checkout_url = "user_checkouts.php";
    
    return add_notification(
        "Item Due Soon",
        "Your checkout of '{$item_name}' is due on {$due_date}",
        "warning",
        "inventory",
        $user_id,
        $checkout_url
    );
}

/**
 * Add notification about an overdue item
 * 
 * @param int $checkout_id    The checkout ID
 * @param string $item_name   The item name
 * @param int $user_id        User ID to notify
 * @param string $due_date    Due date that has passed
 * @return int|bool           The new notification ID or false on failure
 */
function notify_overdue_item($checkout_id, $item_name, $user_id, $due_date) {
    $checkout_id = (int)$checkout_id;
    $checkout_url = "user_checkouts.php";
    
    // Notify user
    add_notification(
        "Overdue Item",
        "Your checkout of '{$item_name}' was due on {$due_date}. Please return it ASAP.",
        "danger",
        "alert",
        $user_id,
        $checkout_url
    );
    
    // Also notify admins
    $user = find_by_id('users', $user_id);
    $username = $user ? $user['username'] : 'Unknown user';
    
    return notify_admins(
        "Overdue Item Alert",
        "User {$username} has an overdue item: '{$item_name}' (Due: {$due_date})",
        "danger",
        "alert",
        "checkout_history.php?id={$checkout_id}"
    );
}

/**
 * Delete notifications older than a certain date
 * 
 * @param int $days           Number of days to keep notifications
 * @return int                Number of notifications deleted
 */
function clean_old_notifications($days = 30) {
    global $db;
    
    $days = (int)$days;
    $sql = "DELETE FROM notifications WHERE timestamp < DATE_SUB(NOW(), INTERVAL {$days} DAY)";
    
    $db->query($sql);
    return $db->affected_rows();
}

/**
 * Count unread notifications for a user
 * 
 * @param int $user_id        User ID to check
 * @return int                Number of unread notifications
 */
function count_unread_notifications($user_id) {
    global $db;
    
    $user_id = (int)$user_id;
    $sql = "SELECT COUNT(*) as count FROM notifications 
            WHERE (user_id = {$user_id} OR user_id = 0) 
            AND is_read = 0";
    
    $result = $db->query($sql);
    if ($result) {
        $count = $db->fetch_assoc($result);
        return (int)$count['count'];
    }
    
    return 0;
}