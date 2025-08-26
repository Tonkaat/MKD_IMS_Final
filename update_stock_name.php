<?php
// This file handles updating the stock name (stock number)
require_once('includes/load.php');
page_require_level(2);

if(empty($_POST['stock_id']) || empty($_POST['stock_number'])) {
    $session->msg('d', 'Missing required parameters.');
    redirect('product.php');
    exit;
}

$stock_id = (int)$_POST['stock_id'];
$stock_number = $db->escape($_POST['stock_number']); 

// First, check if the stock ID exists
$stock = find_by_id('stock', $stock_id);
if(!$stock) {
    $session->msg('d', 'Stock not found.');
    redirect('product.php');
    exit;
}

// Check if the new stock number already exists
$sql_check = "SELECT COUNT(*) as count FROM stock WHERE stock_number = '{$stock_number}' AND id != '{$stock_id}'";
$result_check = $db->query($sql_check);
$row = $db->fetch_assoc($result_check);

if($row['count'] > 0) {
    $session->msg('d', 'Stock ID already exists. Please choose a different one.');
    redirect('product.php');
    exit;
}

// Update the stock number
$sql = "UPDATE stock SET stock_number = '{$stock_number}' WHERE id = '{$stock_id}'";
$result = $db->query($sql);

if($result && $db->affected_rows() === 1) {
    $session->msg('s', 'Stock ID successfully updated.');
} else {
    $session->msg('d', 'Failed to update Stock ID. Error: ' . $db->error);
}

redirect('product.php');