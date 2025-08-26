<?php
/**
 * Mark All Notifications as Read
 */
require_once('includes/load.php');
page_require_level(3);

// Set content type to JSON
header('Content-Type: application/json');

// Get current user ID
$current_user = current_user();
$user_id = $current_user['id'];

// Update all notifications as read for current user
$query = "UPDATE notifications 
          SET is_read = 1 
          WHERE (user_id = {$user_id} OR user_id = 0) 
          AND is_read = 0";

if ($db->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update notifications']);
}
?>