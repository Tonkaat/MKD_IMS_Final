<?php
// This file handles updating the stock status
require_once('includes/load.php');
page_require_level(2);

if(empty($_POST['stock_id']) || empty($_POST['status'])) {
    $session->msg('d', 'Missing required parameters.');
    redirect('product.php');
    exit;
}

$stock_id = (int)$_POST['stock_id'];
$status_id = (int)$_POST['status']; 

// First, check if the stock ID exists
$stock = find_by_id('stock', $stock_id);
if(!$stock) {
    $session->msg('d', 'Stock not found.');
    redirect('product.php');
    exit;
}

// Update the stock status
$sql = "UPDATE stock SET status_id = '{$status_id}' WHERE id = '{$stock_id}'";
$result = $db->query($sql);

if($result && $db->affected_rows() === 1) {
    $session->msg('s', 'Stock status successfully updated.');
} else {
    $session->msg('d', 'Failed to update stock status. Error: ' . $db->error);
}

redirect('product.php');