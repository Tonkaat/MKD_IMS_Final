<?php
// This file handles updating the stock location
require_once('includes/load.php');
page_require_level(2);

if(empty($_POST['stock_id']) || empty($_POST['location'])) {
    $session->msg('d', 'Missing required parameters.');
    redirect('product.php');
    exit;
}

$stock_id = (int)$_POST['stock_id'];
$location_id = (int)$_POST['location']; 

// First, check if the stock ID exists
$stock = find_by_id('stock', $stock_id);
if(!$stock) {
    $session->msg('d', 'Stock not found.');
    redirect('product.php');
    exit;
}

// Update the stock location and set status to "Placed" (6)
$sql = "UPDATE stock SET location_id = '{$location_id}', status_id = 6 WHERE id = '{$stock_id}'";
$result = $db->query($sql);

if($result && $db->affected_rows() === 1) {
    $session->msg('s', 'Stock location successfully updated and status set to Placed.');
} else {
    $session->msg('d', 'Failed to update stock location. Error: ' . $db->error);
}

redirect('product.php');