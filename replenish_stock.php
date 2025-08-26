<?php
require_once('includes/load.php');

// Check if the request is POST and user is logged in
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in and has admin privileges
page_require_level(2); // Adjust level as needed

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input data
if (!isset($data['product_id']) || !isset($data['quantity_to_add']) || !isset($data['new_total_quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$product_id = (int)$data['product_id'];
$quantity_to_add = (int)$data['quantity_to_add'];
$new_total_quantity = (int)$data['new_total_quantity'];

// Validate that quantity to add is positive
if ($quantity_to_add <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Quantity to add must be positive']);
    exit;
}

try {
    // Start transaction
    $db->autocommit(false);
    
    // Get current product information
    $product_sql = "SELECT * FROM products WHERE id = {$product_id}";
    $product_result = $db->query($product_sql);
    
    if (!$product_result || $product_result->num_rows === 0) {
        throw new Exception('Product not found');
    }
    
    $product = $product_result->fetch_assoc();
    $current_quantity = (int)$product['quantity'];
    
    // Verify the calculation
    if ($current_quantity + $quantity_to_add !== $new_total_quantity) {
        throw new Exception('Quantity calculation mismatch');
    }
    
    // Update product quantity in products table
    $update_product_sql = "UPDATE products SET quantity = {$new_total_quantity} WHERE id = {$product_id}";
    
    if (!$db->query($update_product_sql)) {
        throw new Exception('Failed to update product quantity: ' . $db->error);
    }
    
    // Add stock entries to stock table
    // Generate unique stock numbers for each item added
    for ($i = 0; $i < $quantity_to_add; $i++) {
        // Generate a unique stock number (you can customize this format)
        $stock_number = 'STK' . str_pad($product_id, 4, '0', STR_PAD_LEFT) . '-' . date('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . time();
        
        // Insert new stock entry
        $stock_sql = "INSERT INTO stock (product_id, stock_number, location_id, status_id) VALUES (
            {$product_id}, 
            '{$stock_number}', 
            " . ($product['location_id'] ? "'{$product['location_id']}'" : "NULL") . ", 
            1
        )";
        
        if (!$db->query($stock_sql)) {
            throw new Exception('Failed to add stock entry: ' . $db->error);
        }
    }
    
    // Log the replenishment action (optional - create activity log table if needed)
    // You can add activity logging here if you have an activity log system
    
    // Commit transaction
    $db->commit();
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Stock replenished successfully',
        'data' => [
            'product_id' => $product_id,
            'quantity_added' => $quantity_to_add,
            'old_quantity' => $current_quantity,
            'new_quantity' => $new_total_quantity
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();
    
    error_log('Stock replenishment error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    // Reset autocommit
    $db->autocommit(true);
}
?>