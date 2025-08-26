<?php
require_once('includes/load.php');
if(isset($_POST['request_id'])) {
  $request_id = (int)$_POST['request_id'];
 
  // Get request details for the notification
  $req_query = "SELECT r.*, u.id as user_id, u.name AS user_name, u.username
                FROM item_requests r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.id = {$request_id}";
  $request = $db->query($req_query)->fetch_assoc();
 
  // Update the request status in the database
  $query = "UPDATE item_requests SET status = 'Denied' WHERE id = {$request_id}";
  if($db->query($query)) {
    // Create a notification for the user who made the request
    if(isset($request['user_id'])) {
      $notification = [
        'user_id' => $request['user_id'],
        'title' => 'Request Denied',
        'message' => "Your request for {$request['item_name']} has been denied.",
        'type' => 'danger',
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
      'title' => 'Item Request Denied',
      'message' => "Request #{$request_id} for {$request['item_name']} has been denied",
      'type' => 'info',
      'category' => 'request',
      'link' => 'admin.php'
    ];
   
    $admin_notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                         VALUES ('{$admin_notif['user_id']}', '{$admin_notif['title']}',
                         '{$admin_notif['message']}', '{$admin_notif['type']}',
                         '{$admin_notif['category']}', '{$admin_notif['link']}')";
    $db->query($admin_notif_query);
   
    // Add success message to session
    $session->msg('s', "Request #{$request_id} has been denied successfully.");
   
    // Create a toast notification (client-side notification will be handled by the JS)
  } else {
    $session->msg('d', "Failed to deny request: " . $db->error());
  }
 
  // Log the action
  $user_id = $_SESSION['user_id'];
  log_recent_action($user_id, "Denied request #$request_id");
 
  redirect('admin.php', false);
} else {
  $session->msg('d', "No request ID provided.");
  redirect('admin.php', false);
}
?>