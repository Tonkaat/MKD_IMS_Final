<?php
require_once('includes/load.php');
page_require_level(3);

// Initialize response variables
$status = 'error';
$message = 'Failed to update item status.';

// Check if stock_id and status are provided
if (isset($_GET['stock_id']) && isset($_GET['status'])) {
    $stock_id = intval($_GET['stock_id']);
    $status_id = intval($_GET['status']);
    $user_id = (int)$_SESSION['user_id'];

    // Get current user details
    $user_query = "SELECT name, username FROM users WHERE id = {$user_id}";
    $current_user = $db->query($user_query)->fetch_assoc();

    // Validate parameters
    if ($stock_id > 0 && $status_id > 0) {
        // Get the current stock item to verify it exists and has 'Defected' status
        $stock_query = "SELECT s.*, p.name AS product_name
                        FROM stock s
                        LEFT JOIN products p ON s.product_id = p.id
                        WHERE s.id = {$stock_id}";
        $current_stock = $db->query($stock_query)->fetch_assoc();
        
        if ($current_stock && $current_stock['status_id'] == 5) { // 5 is the ID for 'Maintenance' status
            // Update the stock status to 'Available' (or whatever status ID is passed)
            $sql = "UPDATE stock SET status_id = '{$db->escape($status_id)}' WHERE id = '{$db->escape($stock_id)}'";
            
            if ($db->query($sql)) {
                // Create notification for admins
                $notification = [
                    'user_id' => 1, // For admins
                    'title' => 'Item Repaired',
                    'message' => "Item {$current_stock['stock_number']} ({$current_stock['product_name']}) has been repaired and is now available again. Fixed by {$current_user['name']}.",
                    'type' => 'success',
                    'category' => 'inventory',
                    'link' => 'admin_inventory.php'
                ];
                
                $notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                               VALUES ('{$notification['user_id']}', '{$notification['title']}',
                               '{$notification['message']}', '{$notification['type']}',
                               '{$notification['category']}', '{$notification['link']}')";
                $db->query($notif_query);
                
                // Log this action
                $log_action = "Updated stock ID: $stock_id from 'Maintenance' to 'Available'";
                log_recent_action($user_id, $log_action);
                
                $status = 'success';
                $message = 'Item marked as fixed and is now available.';
            }
        } else {
            $message = 'Invalid stock item or item is not currently in maintenance.';
        }
    }
}

// Redirect back to the inventory page with appropriate status message
redirect('user_inventory.php?status=' . $status . '&message=' . urlencode($message));
?>