<?php
require_once('includes/load.php');
page_require_level(3);

// report_items.php
if (isset($_POST['stock_id']) && isset($_POST['status'])) {
    $stock_id = (int)$_POST['stock_id'];
    $status = (int)$_POST['status'];
    $user_id = (int)$_SESSION['user_id'];

    // Get stock item and product details for the notification
    $stock_query = "SELECT s.*, p.name AS product_name
                    FROM stock s
                    LEFT JOIN products p ON s.product_id = p.id
                    WHERE s.id = {$stock_id}";
    $stock_item = $db->query($stock_query)->fetch_assoc();

    // Get current user details
    $user_query = "SELECT name, username FROM users WHERE id = {$user_id}";
    $current_user = $db->query($user_query)->fetch_assoc();

    // Ensure the status is valid
    if ($status == 3 || $status == 4 || $status == 5) {
        $query = "UPDATE stock SET status_id = {$status} WHERE id = {$stock_id}";
        
        // Set notification details based on status
        $status_name = '';
        $notification_type = 'warning';
        $notification_title = '';
        $notification_message = '';
        
        if ($status == 3) { // Missing
            $status_name = 'Missing';
            $notification_title = 'Item Reported Missing';
            $notification_message = "Item {$stock_item['stock_number']} ({$stock_item['product_name']}) has been reported missing by {$current_user['name']}.";
        } elseif ($status == 4) { // Lost
            $status_name = 'Lost';
            $notification_title = 'Item Reported Lost';
            $notification_message = "Item {$stock_item['stock_number']} ({$stock_item['product_name']}) has been reported lost by {$current_user['name']}.";
            $notification_type = 'danger';
        } elseif ($status == 5) { // Maintenance (Defected)
            $status_name = 'Defected';
            $notification_title = 'Item Requires Maintenance';
            $notification_message = "Item {$stock_item['stock_number']} ({$stock_item['product_name']}) has been marked defected by {$current_user['name']}.";
            $notification_type = 'info';
        }
        
    } elseif ($status == 1) {
        $query = "UPDATE stock SET status_id = 1 WHERE id = {$stock_id}";
        $status_name = 'Available';
        $notification_title = 'Item Marked Available';
        $notification_message = "Item {$stock_item['stock_number']} ({$stock_item['product_name']}) has been marked as available by {$current_user['name']}.";
        $notification_type = 'success';
    }

    if ($db->query($query)) {
        // Create notification for admins
        if (isset($notification_title)) {
            // Create system notification for admins (user_id = 1 for admin)
            $admin_notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                                VALUES (1, '{$notification_title}', 
                                '{$notification_message}', '{$notification_type}', 
                                'inventory', 'product.php')";
            $db->query($admin_notif_query);
            
            // Log the action
            log_recent_action($user_id, "Changed item #{$stock_id} status to {$status_name}");
        }
        
        // Success
        header("Location: user_inventory.php?status=success&message=Item status updated successfully.");
    } else {
        // Error
        header("Location: user_inventory.php?status=error&message=Failed to update item status.");
    }
} else {
    // Missing parameters
    header("Location: user_inventory.php?status=error&message=Missing parameters.");
}
?>