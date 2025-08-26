<?php
// This file handles deleting a stock item
require_once('includes/load.php');
page_require_level(2);

if(empty($_POST['stock_id'])) {
    $session->msg('d', 'Missing stock ID.');
    redirect('product.php');
    exit;
}

$stock_id = (int)$_POST['stock_id'];

// First, check if the stock ID exists
$stock = find_by_id('stock', $stock_id);
if(!$stock) {
    $session->msg('d', 'Stock not found.');
    redirect('product.php');
    exit;
}

// Get the product ID before deleting the stock
$product_id = $stock['product_id'];

// Delete the stock
$sql = "DELETE FROM stock WHERE id = '{$stock_id}'";
$result = $db->query($sql);

if($result && $db->affected_rows() === 1) {
    // Update the product quantity (reduce by 1)
    $product = find_by_id('products', $product_id);
    if($product && is_numeric($product['quantity'])) {
        $new_quantity = $product['quantity'] - 1;
        if($new_quantity < 0) $new_quantity = 0;
        
        $update_product = $db->query("UPDATE products SET quantity = '{$new_quantity}' WHERE id = '{$product_id}'");
        if(!$update_product) {
            $session->msg('d', 'Failed to update product quantity after stock deletion.');
        }
    }
    
    $session->msg('s', 'Stock item successfully deleted.');
} else {
    $session->msg('d', 'Failed to delete stock item. Error: ' . $db->error);
}

redirect('product.php');
