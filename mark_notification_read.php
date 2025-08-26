<?php
/**
 * Mark Single Notification as Read
 */
require_once('includes/load.php');
page_require_level(3);

// Set content type to JSON
header('Content-Type: application/json');

// Get current user ID
$current_user = current_user();
$user_id = $current_user['id'];

// Get the request body
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id'])) {
    $notification_id = (int)$input['id'];
    
    // Update notification as read for current user
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE id = {$notification_id} 
              AND (user_id = {$user_id} OR user_id = 0)";
    
    if ($db->query($query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notification']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>