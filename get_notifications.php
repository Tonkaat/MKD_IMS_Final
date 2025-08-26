<?php
/**
 * Get Notifications Endpoint
 * Retrieves all notifications for the current user
 */
require_once('includes/load.php');
page_require_level(3);

// Set content type to JSON
header('Content-Type: application/json');

// Get current user ID
$current_user = current_user();
$user_id = $current_user['id'];

// Get all notifications
$notifications = [];
$notificationQuery = $db->query("
    SELECT n.*, COALESCE(u.username, 'System') as source_name
    FROM notifications n 
    LEFT JOIN users u ON n.source_id = u.id
    WHERE n.user_id = '{$user_id}' OR n.user_id = 0
    ORDER BY n.timestamp DESC 
    LIMIT 20
");

if ($db->num_rows($notificationQuery) > 0) {
    while ($notification = $db->fetch_assoc($notificationQuery)) {
        // Convert database fields to expected format for frontend
        $notifications[] = [
            'id' => $notification['id'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'icon' => get_notification_icon($notification['type'], $notification['category']),
            'read' => $notification['is_read'] == 1,
            'timestamp' => $notification['timestamp'],
            'link' => $notification['link'],
            'category' => $notification['category'],
            'source' => $notification['source_name']
        ];
    }
}

// Get pending item requests
$requests = [];
$requestQuery = $db->query("
    SELECT r.*, u.name AS user_name, u.username
    FROM item_requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    ORDER BY r.request_date DESC
    LIMIT 10
");

if ($db->num_rows($requestQuery) > 0) {
    while ($request = $db->fetch_assoc($requestQuery)) {
        $requests[] = [
            'id' => $request['id'],
            'item_name' => $request['item_name'],
            'quantity' => $request['quantity'],
            'status' => $request['status'],
            'request_date' => $request['request_date'],
            'user_name' => $request['user_name'] ?? $request['username']
        ];
    }
}

// Get low stock items
$lowStock = [];
$lowStockQuery = $db->query("
    SELECT p.*, c.name as category_name, l.name as location_name
    FROM products p 
    LEFT JOIN categories c ON p.categorie_id = c.id
    LEFT JOIN location l ON p.location_id = l.id
    WHERE p.quantity <= 5 AND p.quantity > 0
    ORDER BY p.quantity ASC
    LIMIT 10
");

if ($db->num_rows($lowStockQuery) > 0) {
    while ($item = $db->fetch_assoc($lowStockQuery)) {
        $lowStock[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'category' => $item['category_name'],
            'location' => $item['location_name']
        ];
    }
}

// Get missing/lost items from stock table
$missingItems = [];
$missingQuery = $db->query("
    SELECT s.*, p.name, p.categorie_id, c.name as category_name, l.name as location_name, st.name as status_name
    FROM stock s
    LEFT JOIN products p ON s.product_id = p.id
    LEFT JOIN categories c ON p.categorie_id = c.id
    LEFT JOIN location l ON s.location_id = l.id
    LEFT JOIN status st ON s.status_id = st.id
    WHERE s.status_id IN (3, 4, 5) -- Assuming 3=missing, 4=lost (adjust as needed)
    LIMIT 10
");
if ($db->num_rows($missingQuery) > 0) {
    while ($item = $db->fetch_assoc($missingQuery)) {
        $missingItems[] = [
            'id' => $item['product_id'],
            'name' => $item['name'],
            'stock_number' => $item['stock_number'],
            'category' => $item['category_name'],
            'location' => $item['location_name'],
            'status' => $item['status_name'],
        ];
    }
}

// Helper function to get appropriate icon for notification type
function get_notification_icon($type, $category) {
    switch ($category) {
        case 'inventory':
            return 'bi-box-seam';
        case 'request':
            return 'bi-send';
        case 'user':
            return 'bi-person';
        case 'system':
            return 'bi-gear';
        case 'alert':
            return 'bi-exclamation-triangle';
        default:
            // Default icons based on type
            switch ($type) {
                case 'success':
                    return 'bi-check-circle';
                case 'warning':
                    return 'bi-exclamation-triangle';
                case 'danger':
                    return 'bi-exclamation-octagon';
                case 'info':
                default:
                    return 'bi-info-circle';
            }
    }
}

// Return data as JSON
echo json_encode([
    'items' => $notifications,
    'requests' => $requests,
    'lowStock' => $lowStock,
    'missingItems' => $missingItems
]);