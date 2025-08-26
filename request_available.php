<?php
require_once('includes/load.php');
page_require_level(1);
if(isset($_POST['make_available']) && isset($_POST['request_id'])) {
  $request_id = (int)$_POST['request_id'];
  
  // Get the full request details including consumable status and requester's location
  $request_query = "SELECT r.*, 
                    u.location_id as user_location_id,
                    c.id as category_id
                    FROM item_requests r 
                    LEFT JOIN users u ON r.user_id = u.id
                    LEFT JOIN categories c ON r.categorie_id = c.id
                    WHERE r.id = {$request_id}";
  $request_result = $db->query($request_query);
  $request_details = $db->fetch_assoc($request_result);
  
  // Define consumable categories
  $consumable_categories = [3, 4, 12, 8]; // Update with your consumable category IDs
  $is_consumable = in_array($request_details['category_id'], $consumable_categories);
 
  // Get the product information from the form
  $p_name = remove_junk($db->escape($_POST['product-title']));
  $p_cat  = (int)$_POST['product-categorie'];
  $p_qty  = (int)$_POST['product-quantity'];
  
  // Determine location based on request details
  if ($is_consumable) {
    // For consumable items, location is not required
    $p_loc = !empty($_POST['product-location']) ? (int)$_POST['product-location'] : NULL;
  } else {
    // For non-consumable items, check if user location should be used
    if ($request_details['use_user_location'] == 1 && !empty($request_details['user_location_id'])) {
      $p_loc = (int)$request_details['user_location_id'];
    } else {
      $p_loc = !empty($_POST['product-location']) ? (int)$_POST['product-location'] : NULL;
    }
  }

  // Check if product with same name already exists
  $check_query = "SELECT id, quantity FROM products WHERE name = '{$p_name}' LIMIT 1";
  $check_result = $db->query($check_query);
  
  if($check_result && $check_result->num_rows > 0) {
    // Product exists, update quantity instead of creating new entry
    $existing_product = $db->fetch_assoc($check_result);
    $product_id = $existing_product['id'];
    $new_qty = $existing_product['quantity'] + $p_qty;
    
    // Update existing product quantity
    $update_query = "UPDATE products SET quantity = {$new_qty} WHERE id = {$product_id}";
    
    if($db->query($update_query)) {
      // Successfully updated quantity
      $was_inserted = false;
    } else {
      $session->msg('d', "Failed to update existing item: " . $db->error());
      redirect('admin.php');
      return;
    }
  } else {
    // Insert into products table as new product
    $query  = "INSERT INTO products (";
    $query .= " name,quantity,categorie_id,location_id,date";
    $query .= ") VALUES (";
    $query .= " '{$p_name}', {$p_qty}, {$p_cat}, ";
    $query .= $p_loc ? "{$p_loc}" : "NULL";
    $query .= ", NOW()";
    $query .= ")";
   
    if($db->query($query)){
      // Get the newly inserted product ID
      $product_id = $db->insert_id();
      $was_inserted = true;
    } else {
      $session->msg('d', "Failed to add item to inventory: " . $db->error());
      redirect('admin.php');
      return;
    }
  }
  
  // Default status ID for new stock (assuming 1 is 'Available' or similar)
  $status_id = 1;
  
  // Add individual stock entries based on quantity - only for newly added products or increment
  if($was_inserted || true) { // We'll create the stock entries regardless
    // Get the highest existing stock number for this product
    $max_number_query = "SELECT MAX(SUBSTRING_INDEX(stock_number, '-', -1)) as max_num 
                        FROM stock 
                        WHERE product_id = {$product_id} 
                        AND stock_number LIKE '{$p_name}-%'";
    $max_result = $db->query($max_number_query);
    $start_num = 1;
    
    if($max_result && $max_result->num_rows > 0) {
      $max_row = $db->fetch_assoc($max_result);
      if(!empty($max_row['max_num'])) {
        $start_num = (int)$max_row['max_num'] + 1;
      }
    }
    
    // Add individual stock entries based on quantity
    for($i = 0; $i < $p_qty; $i++) {
      // Create stock number with leading zeros (e.g., 001, 002, etc.)
      $stock_number = $p_name . '-' . str_pad($start_num + $i, 3, '0', STR_PAD_LEFT);
      
      // For non-consumable items that use user location, set all stocks to that location
      $stock_location = $p_loc;
      
      // Insert into stock table
      $stock_query = "INSERT INTO stock (
        product_id, 
        stock_number, 
        location_id, 
        status_id
      ) VALUES (
        {$product_id},
        '{$db->escape($stock_number)}',
        " . ($stock_location ? "{$stock_location}" : "NULL") . ",
        {$status_id}
      )";
      
      $db->query($stock_query);
    }
  }
  
  // Update the request status to "Added" regardless of whether we inserted or updated
  $update_query = "UPDATE item_requests SET status = 'Added', added_to_inventory = 1 WHERE id = {$request_id}";
  $db->query($update_query);
 
  // Get request details for the notification
  $req_query = "SELECT r.*, u.id as user_id, u.name AS user_name, u.username
              FROM item_requests r
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.id = {$request_id}";
  $request = $db->query($req_query)->fetch_assoc();
 
  // Create a notification for the user who made the request
  if(isset($request['user_id'])) {
    // Customize message based on consumable status
    $item_message = $is_consumable ? 
      "Your requested consumable item '{$p_name}' is now available in inventory." :
      "Your requested item '{$p_name}' is now available" . ($p_loc ? " at your location." : " in inventory.");
    
    $notification = [
      'user_id' => $request['user_id'],
      'title' => 'Item Added to Inventory',
      'message' => $item_message,
      'type' => 'success',
      'category' => 'inventory',
      'link' => 'home.php'
    ];
   
    // Insert notification
    $notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
    VALUES (
      '{$db->escape($notification['user_id'])}',
      '{$db->escape($notification['title'])}',
      '{$db->escape($notification['message'])}',
      '{$db->escape($notification['type'])}',
      '{$db->escape($notification['category'])}',
      '{$db->escape($notification['link'])}'
    )";
    $db->query($notif_query);
  }
 
  // Create a system notification for all users
  $location_info = "";
  if (!$is_consumable && $p_loc) {
    $loc_query = "SELECT name FROM location WHERE id = {$p_loc}";
    $loc_result = $db->query($loc_query);
    $location = $db->fetch_assoc($loc_result);
    if ($location) {
      $location_info = " at {$location['name']}";
    }
  }
  
  $system_notif = [
    'user_id' => 1, // System notification
    'title' => 'New Item Available',
    'message' => "'{$p_name}' has been added to inventory ({$p_qty} units available{$location_info})",
    'type' => 'info',
    'category' => 'inventory',
    'link' => 'admin.php'
  ];
 
  $system_notif_query = "INSERT INTO notifications (user_id, title, message, type, category, link)
  VALUES (
    '{$db->escape($system_notif['user_id'])}',
    '{$db->escape($system_notif['title'])}',
    '{$db->escape($system_notif['message'])}',
    '{$db->escape($system_notif['type'])}',
    '{$db->escape($system_notif['category'])}',
    '{$db->escape($system_notif['link'])}'
  )";
  $db->query($system_notif_query);
 
  // Success message with location info if applicable
  $success_message = isset($was_inserted) && $was_inserted ? 
    "Item added to inventory successfully with {$p_qty} individual stock entries" : 
    "Quantity updated for existing item '{$p_name}' with {$p_qty} additional stock entries";
    
  if (!$is_consumable && $p_loc) {
    $success_message .= $location_info;
  }
  $session->msg('s', $success_message);
 
  redirect('admin.php');
} else {
  $session->msg('d', "Missing required information");
  redirect('admin.php');
}
?>