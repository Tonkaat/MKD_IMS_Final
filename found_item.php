<?php
require_once('includes/load.php');

// Check if the stock_id and status are set in the URL
if (isset($_GET['stock_id']) && isset($_GET['status'])) {
    $stock_id = (int)$_GET['stock_id'];
    $status = (int)$_GET['status'];
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

    // Update the item status to available (assuming status '1' means available)
    if ($status == 6) {
        // Get previous status for better notification
        $prev_status_query = "SELECT status.name 
                             FROM stock 
                             LEFT JOIN status ON stock.status_id = status.id
                             WHERE stock.id = {$stock_id}";
        $prev_status = $db->query($prev_status_query)->fetch_assoc();
        $prev_status_name = $prev_status ? $prev_status['name'] : 'Unknown';
        
        $query = "UPDATE stock SET status_id = 6 WHERE id = {$stock_id}";
        
        if ($db->query($query)) {
            // Create notification for admins
            $notification = [
                'user_id' => 1, // For admins
                'title' => 'Item Found',
                'message' => "Item {$stock_item['stock_number']} ({$stock_item['product_name']}) previously marked as {$prev_status_name} has been found and is now available. Reported by {$current_user['name']}.",
                'type' => 'success',
                'category' => 'inventory',
                'link' => 'admin_inventory.php'
            ];
            
            $notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                           VALUES ('{$notification['user_id']}', '{$notification['title']}',
                           '{$notification['message']}', '{$notification['type']}',
                           '{$notification['category']}', '{$notification['link']}')";
            $db->query($notif_query);
            
            // Log the action
            log_recent_action($user_id, "Marked item #{$stock_id} as found and available");
            
            // Redirect back with success message
            header("Location: user_inventory.php?status=success&message=Item has been marked as available again.");
            exit();
        } else {
            // Redirect back with error message
            header("Location: user_inventory.php?status=error&message=Failed to update item status.");
            exit();
        }
    } else {
        // Invalid status
        header("Location: user_inventory.php?status=error&message=Invalid status.");
        exit();
    }
} else {
    // Missing parameters
    header("Location: user_inventory.php?status=error&message=Missing parameters.");
    exit();
}
?>