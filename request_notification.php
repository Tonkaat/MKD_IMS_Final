<?php
/**
 * Request Notification Handler
 * Creates notifications when request statuses change
 */
require_once('includes/load.php');

/**
 * Create notification for request status changes
 *
 * @param int $user_id - The user to notify
 * @param int $request_id - The request ID
 * @param string $item_name - Name of the requested item
 * @param string $status - New status (Approved, Added, Denied)
 * @param int $source_id - The user who made the change (usually an admin)
 * @return bool Whether notification was created successfully
 */
function create_request_status_notification($user_id, $request_id, $item_name, $status, $source_id = 0) {
    global $db;
   
    // Define notification parameters based on status
    $notification = [
        'title' => '',
        'message' => '',
        'type' => '',
        'link' => '',
    ];
   
    switch ($status) {
        case 'Approved':
            $notification['title'] = 'Request Approved';
            $notification['message'] = "Your request for {$item_name} has been approved.";
            $notification['type'] = 'success';
            $notification['link'] = 'home.php';
            break;
           
        case 'Added':
            $notification['title'] = 'Item Added to Inventory';
            $notification['message'] = "Your requested item {$item_name} has been added to inventory.";
            $notification['type'] = 'info';
            $notification['link'] = 'home.php';
            break;
           
        case 'Denied':
            $notification['title'] = 'Request Denied';
            $notification['message'] = "Your request for {$item_name} has been denied.";
            $notification['type'] = 'danger';
            $notification['link'] = 'home.php';
            break;
           
        default:
            error_log("Invalid status '{$status}' for request notification");
            return false;
    }
   
    // Insert notification into database using prepared statement
    $sql = "INSERT INTO notifications (user_id, title, message, type, link, category, source_id, is_read) 
            VALUES (?, ?, ?, ?, ?, 'request', ?, 0)";
    
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("issssi", 
            $user_id, 
            $notification['title'], 
            $notification['message'], 
            $notification['type'], 
            $notification['link'], 
            $source_id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        if ($result) {
            error_log("Notification created successfully: User {$user_id}, Status {$status}, Request {$request_id}");
        } else {
            error_log("Failed to create notification: User {$user_id}, Status {$status}, Request {$request_id}, Error: " . $db->error);
        }
        
        return $result;
    } else {
        error_log("Failed to prepare notification statement: " . $db->error);
        return false;
    }
}

/**
 * Create a system notification visible to all admins
 *
 * @param int $request_id - The request ID
 * @param string $item_name - Name of the requested item
 * @param string $status - New status (Approved, Added, Denied)
 * @param string $admin_name - Name of the admin who made the change
 * @param int $source_id - The admin user ID
 * @return bool Whether notification was created successfully
 */
function create_admin_request_notification($request_id, $item_name, $status, $admin_name, $source_id) {
    global $db;
    
    $type_map = [
        'Approved' => 'info',
        'Denied' => 'warning',
        'Added' => 'success'
    ];
    
    $type = isset($type_map[$status]) ? $type_map[$status] : 'info';
    $title = "Item Request {$status}";
    $message = "Request #{$request_id} for {$item_name} has been {$status} by {$admin_name}";
    
    $sql = "INSERT INTO notifications (user_id, title, message, type, link, category, source_id, is_read) 
            VALUES (0, ?, ?, ?, 'admin.php', 'request', ?, 0)";
    
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("sssi", $title, $message, $type, $source_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    return false;
}
?>