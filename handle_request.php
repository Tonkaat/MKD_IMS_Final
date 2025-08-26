<?php
require_once('includes/load.php');
page_require_level(1);

if (isset($_GET['id']) && isset($_GET['action'])) {
  $id = (int)$_GET['id'];
  $action = $_GET['action'];
  $status = ($action === 'approve') ? 'Approved' : 'Denied';
  
  // Get request details for the notification
  $req_query = "SELECT r.*, u.id as user_id, u.name AS user_name, u.username 
                FROM item_requests r 
                LEFT JOIN users u ON r.user_id = u.id 
                WHERE r.id = {$id}";
  $request = $db->query($req_query)->fetch_assoc();

  $query = "UPDATE item_requests SET status = '{$status}' WHERE id = {$id}";
  if ($db->query($query)) {
    // Create a notification for the user who made the request
    if(isset($request['user_id'])) {
      $notification = [
        'user_id' => $request['user_id'],
        'title' => "Request {$status}",
        'message' => "Your request for {$request['item_name']} has been {$status}.",
        'type' => ($action === 'approve') ? 'success' : 'danger',
        'category' => 'request',
        'link' => 'home.php'
      ];
      
      // Insert notification
      $notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link) 
                     VALUES ('{$notification['user_id']}', '{$notification['title']}', 
                     '{$notification['message']}', '{$notification['type']}', 
                     '{$notification['category']}', '{$notification['link']}')";
      $db->query($notif_query);
    }
    
    // Create a system notification for admins
    $admin_notif = [
      'user_id' => 1, // 0 means visible to all admins
      'title' => "Item Request {$status}",
      'message' => "Request #{$id} for {$request['item_name']} has been {$status}",
      'type' => ($action === 'approve') ? 'info' : 'warning',
      'category' => 'request',
      'link' => 'admin.php'
    ];
    
    $admin_notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link) 
                         VALUES ('{$admin_notif['user_id']}', '{$admin_notif['title']}', 
                         '{$admin_notif['message']}', '{$admin_notif['type']}', 
                         '{$admin_notif['category']}', '{$admin_notif['link']}')";
    $db->query($admin_notif_query);
    
    $session->msg("s", "Request has been {$status}.");
  } else {
    $session->msg("d", "Failed to update the request.");
  }
} else {
  $session->msg("d", "Invalid request.");
}
redirect('admin.php');
?>
