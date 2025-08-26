<?php
require_once('includes/load.php');
page_require_level(3); // Ensure it's a logged-in user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $item_name = $db->escape($_POST['item_name']);
    $categorie_id = (int)$_POST['categorie_id'];
    $quantity = (int)$_POST['quantity'];
   
    // Handle the new fields
    $description = $db->escape($_POST['description'] ?? '');
    
    // Define consumable categories
    $consumable_categories = [3, 4, 12, 8]; // Update with your consumable category IDs
    $is_consumable = in_array($categorie_id, $consumable_categories);
    
    // If it's a consumable item, don't use location
    $use_user_location = !$is_consumable && isset($_POST['use_user_location']) ? 1 : 0;
   
    // Get user's current location_id if checkbox is checked and not a consumable
    $location_id = null;
    if ($use_user_location && !$is_consumable) {
        $user_query = $db->query("SELECT location_id FROM users WHERE id = '{$user_id}'");
        if ($db->num_rows($user_query) > 0) {
            $user_data = $db->fetch_assoc($user_query);
            $location_id = (int)$user_data['location_id'];
        }
    }
    
    // Store whether this is a consumable item for later reference
    $sql = "INSERT INTO item_requests (user_id, item_name, categorie_id, quantity, description, use_user_location, location_id, is_consumable)
            VALUES ('{$user_id}', '{$item_name}', '{$categorie_id}', '{$quantity}', '{$description}', '{$use_user_location}', " .
            ($location_id ? "'{$location_id}'" : "NULL") . ", " . ($is_consumable ? "1" : "0") . ")";
    
    if ($db->query($sql)) {
        // Get the ID of the newly created request
        $request_id = $db->insert_id();
        
        // Get user details for the notification
        $user_query = $db->query("SELECT name, username FROM users WHERE id = '{$user_id}'");
        $user = $db->fetch_assoc($user_query);
        
        // Create a notification for the user
        $notification = [
            'user_id' => $user_id,
            'title' => 'Request Submitted',
            'message' => "Your request for {$item_name} has been submitted and is pending review.",
            'type' => 'info',
            'category' => 'request',
            'link' => 'home.php'
        ];
        
        // Insert notification
        $notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                        VALUES ('{$notification['user_id']}', '{$notification['title']}',
                        '{$notification['message']}', '{$notification['type']}',
                        '{$notification['category']}', '{$notification['link']}')";
        $db->query($notif_query);
        
        // Create a system notification for admins
        $admin_notif = [
            'user_id' => 1, // Admin user ID
            'title' => 'New Item Request',
            'message' => "User {$user['name']} has submitted a new request for {$item_name}.",
            'type' => 'info',
            'category' => 'request',
            'link' => 'admin.php'
        ];
        
        $admin_notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
                             VALUES ('{$admin_notif['user_id']}', '{$admin_notif['title']}',
                             '{$admin_notif['message']}', '{$admin_notif['type']}',
                             '{$admin_notif['category']}', '{$admin_notif['link']}')";
        $db->query($admin_notif_query);
        
        // Log the action
        log_recent_action($user_id, "Submitted new request for {$item_name}");
        
        // Set session message
        $session->msg('s', "Your request for {$item_name} has been submitted successfully.");
        
        header("Location: home.php");
    } else {
        $session->msg('d', "Failed to submit request: " . $db->error());
        header("Location: home.php");
    }
    exit;
}
?>