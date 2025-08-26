<?php
require_once 'includes/load.php';

// Set header before any output to avoid issues
header('Content-Type: application/json');

// Check if the user is logged in or has the proper permission
if (!$session->isUserLoggedIn(true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Make sure there's no whitespace or other output before or after JSON
try {
    // Use your database connection
    global $db;
    
    // Query for consumable products (category 12) with stock counts
    $sql = "SELECT p.*,
                (SELECT COUNT(*) FROM stock WHERE product_id = p.id AND status_id = 1) AS stock_count
            FROM products p
            WHERE p.categorie_id = 12";
    
    $result = $db->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $db->error);
    }
    
    $inventory = [];
    
    while ($row = $result->fetch_assoc()) {
        // Clean data using your existing function
        $row = array_map('remove_junk', $row);
        
        // Convert stock values to integers
        if (isset($row['quantity']) && is_numeric($row['quantity'])) {
            $row['quantity'] = (int)$row['quantity'];
        }
        
        $row['stock_count'] = (int)$row['stock_count'];
        
        // Determine actual stock value
        $actual_stock = isset($row['quantity']) && is_numeric($row['quantity'])
            ? (int)$row['quantity']
            : (int)$row['stock_count'];
        
        $row['actual_stock'] = $actual_stock;
        $inventory[] = $row;
    }
    
    // Output clean JSON with no whitespace before or after
    echo json_encode($inventory);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
// No closing PHP tag to prevent accidental whitespace