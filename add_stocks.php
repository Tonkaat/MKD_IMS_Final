<?php
$page_title = 'Add Stocks';
require_once('includes/load.php');
page_require_level(2);

// Function to generate next sequential stock number
function getNextStockNumber($db, $product_name) {
    // Clean product name for use in stock number
    $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $product_name));
    
    // Find the highest existing number for this product
    $pattern = $clean_name . '-r%';
    $query = "SELECT stock_number FROM stock 
              WHERE stock_number LIKE '{$pattern}' 
              ORDER BY stock_number DESC 
              LIMIT 1";
    
    $result = $db->query($query);
    
    if ($db->num_rows($result) > 0) {
        $last_stock = $db->fetch_assoc($result);
        $last_number = $last_stock['stock_number'];
        
        // Extract number from pattern like "pencil-r001"
        if (preg_match('/' . $clean_name . '-r(\d+)$/', $last_number, $matches)) {
            $next_number = intval($matches[1]) + 1;
        } else {
            $next_number = 1;
        }
    } else {
        $next_number = 1;
    }
    
    return $next_number;
}

// Check if POST request and required data is available
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate product ID
    if (empty($_POST['product_id'])) {
        $session->msg('d', 'Missing product ID');
        redirect('product.php', false);
    }
   
    // Validate quantity
    if (empty($_POST['quantity']) || !is_numeric($_POST['quantity']) || $_POST['quantity'] < 1) {
        $session->msg('d', 'Invalid quantity. Please enter a number greater than 0.');
        redirect('product.php', false);
    }
   
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
   
    // Verify product exists and get product details
    $product_query = $db->query("SELECT * FROM products WHERE id = '{$product_id}'");
    if ($db->num_rows($product_query) === 0) {
        $session->msg('d', 'Product not found');
        redirect('product.php', false);
    }
   
    $product = $db->fetch_assoc($product_query);
    $current_quantity = $product['quantity'];
    $product_name = $product['name']; // Get product name for stock numbering
   
    // Start transaction
    $db->query("START TRANSACTION");
    $success = true;
    $added_count = 0;
    
    // Get starting number for this batch
    $starting_number = getNextStockNumber($db, $product_name);
   
    // Generate and add stock items
    for ($i = 0; $i < $quantity; $i++) {
        // Generate stock number with incremental numbering
        $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $product_name));
        $current_number = $starting_number + $i;
        $stock_number = $clean_name . '-r' . str_pad($current_number, 3, '0', STR_PAD_LEFT);
        
        // Check if this stock number already exists (additional safety check)
        $check_query = "SELECT id FROM stock WHERE stock_number = '{$stock_number}'";
        $check_result = $db->query($check_query);
        
        // If duplicate found, keep incrementing until we find a unique number
        while ($db->num_rows($check_result) > 0) {
            $current_number++;
            $stock_number = $clean_name . '-r' . str_pad($current_number, 3, '0', STR_PAD_LEFT);
            $check_result = $db->query("SELECT id FROM stock WHERE stock_number = '{$stock_number}'");
        }
        
        // Update starting number for next iteration
        $starting_number = $current_number + 1;
       
        // Set default values for status_id and location_id
        $status_id = 1; // Default status (Available)
        $location_id = 'NULL'; // No location assigned
       
        // Insert stock record
        $sql = "INSERT INTO stock (product_id, stock_number, status_id, location_id)
                VALUES ('{$product_id}', '{$stock_number}', '{$status_id}', {$location_id})";
       
        if ($db->query($sql)) {
            $added_count++;
        } else {
            $success = false;
            break;
        }
    }
   
    // Update product quantity
    if ($success) {
        $new_quantity = $current_quantity + $added_count;
        $update_sql = "UPDATE products SET quantity = '{$new_quantity}' WHERE id = '{$product_id}'";
       
        if (!$db->query($update_sql)) {
            $success = false;
        }
    }
   
    // Commit or rollback based on success
    if ($success) {
        $db->query("COMMIT");
        $session->msg('s', "Successfully added {$added_count} stock(s) to product");
        redirect('product.php?status=success&message=' . urlencode("Successfully added {$added_count} stock(s) to product"));
    } else {
        $db->query("ROLLBACK");
        $session->msg('d', 'Failed to add stocks');
        redirect('product.php?status=error&message=' . urlencode('Failed to add stocks'));
    }
} else {
    // Not a POST request
    $session->msg('d', 'Invalid request method');
    redirect('product.php', false);
}
?>