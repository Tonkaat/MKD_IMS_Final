<?php
/**
 * MKD Inventory System - Scheduled Notifications Task
 * 
 * This script is designed to be run as a scheduled task (cron job)
 * to handle automated notifications such as:
 * - Upcoming due dates for checked out items
 * - Overdue items
 * - Low stock alerts
 * - Cleaning old notifications
 * 
 * Recommended cron schedule: Daily at midnight
 * Example crontab entry:
 * 0 0 * * * php /path/to/scheduled_notifications.php > /dev/null 2>&1
 */

// Force command line execution only
if(php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

// Setup the environment without session
define('SITE_ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once(SITE_ROOT.DS.'includes'.DS.'config.php');
require_once(SITE_ROOT.DS.'includes'.DS.'database.php');
require_once(SITE_ROOT.DS.'includes'.DS.'functions.php');
require_once(SITE_ROOT.DS.'notification_helper.php');

// Initialize the database connection
$db = new Database();

echo "==== MKD Inventory System - Scheduled Notifications Task ====\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

// Process upcoming due dates (items due in the next 2 days)
process_upcoming_due_dates();

// Process overdue items
process_overdue_items();

// Process low stock alerts (in case any were missed by real-time checks)
process_low_stock_alerts();

// Delete notifications older than 30 days
cleanup_old_notifications();

echo "\nTask completed at: " . date('Y-m-d H:i:s') . "\n";
echo "==== End of Process ====\n";

/**
 * Process upcoming due dates
 * Notifies users about items due in the next 2 days
 */
function process_upcoming_due_dates() {
    global $db;
    
    echo "Processing upcoming due dates...\n";
    
    // Find items due in the next 2 days
    $sql = "SELECT c.id, c.product_id, c.qty, c.due_date, c.user_id, 
            p.name as product_name, u.name as user_name
            FROM checkouts c
            JOIN products p ON c.product_id = p.id
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'Open' 
            AND c.due_date BETWEEN NOW() 
            AND DATE_ADD(NOW(), INTERVAL 2 DAY)
            AND c.reminder_sent = 0";
    
    $result = $db->query($sql);
    
    if($result && $db->num_rows($result) > 0) {
        $count = 0;
        
        while($checkout = $db->fetch_assoc($result)) {
            // Create notification for the user
            notify_upcoming_due_date(
                $checkout['id'],
                $checkout['product_name'],
                $checkout['user_id'],
                date('Y-m-d', strtotime($checkout['due_date']))
            );
            
            // Mark reminder as sent
            $db->query("UPDATE checkouts SET reminder_sent = 1 WHERE id = {$checkout['id']}");
            
            $count++;
        }
        
        echo "Sent {$count} upcoming due date notifications\n";
    } else {
        echo "No upcoming due dates to process\n";
    }
}

/**
 * Process overdue items
 * Notifies users and admins about items that are overdue
 */
function process_overdue_items() {
    global $db;
    
    echo "Processing overdue items...\n";
    
    // Find overdue items that haven't had an overdue notification sent
    $sql = "SELECT c.id, c.product_id, c.qty, c.due_date, c.user_id, 
            p.name as product_name, u.name as user_name
            FROM checkouts c
            JOIN products p ON c.product_id = p.id
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'Open' 
            AND c.due_date < NOW()
            AND c.overdue_notified = 0";
    
    $result = $db->query($sql);
    
    if($result && $db->num_rows($result) > 0) {
        $count = 0;
        
        while($checkout = $db->fetch_assoc($result)) {
            // Create notifications for both user and admins
            notify_overdue_item(
                $checkout['id'],
                $checkout['product_name'],
                $checkout['user_id'],
                date('Y-m-d', strtotime($checkout['due_date']))
            );
            
            // Mark overdue notification as sent
            $db->query("UPDATE checkouts SET overdue_notified = 1 WHERE id = {$checkout['id']}");
            
            $count++;
        }
        
        echo "Sent {$count} overdue item notifications\n";
    } else {
        echo "No overdue items to process\n";
    }
}

/**
 * Process low stock alerts
 * Checks for items below threshold and sends notifications if needed
 */
function process_low_stock_alerts() {
    global $db;
    
    echo "Processing low stock alerts...\n";
    
    // Define threshold
    $threshold = 5;
    
    // Find items with low stock that haven't had a low stock notification recently
    $sql = "SELECT p.id, p.name, p.quantity 
            FROM products p
            LEFT JOIN (
                SELECT n.id, n.message, n.timestamp
                FROM notifications n
                WHERE n.category = 'inventory'
                AND n.type = 'warning'
                AND n.message LIKE '%low on stock%'
                AND n.timestamp > DATE_SUB(NOW(), INTERVAL 1 DAY)
            ) as recent_notif ON recent_notif.message LIKE CONCAT('%', p.name, '%')
            WHERE p.quantity <= {$threshold}
            AND p.quantity > 0
            AND p.status = 'active'
            AND recent_notif.id IS NULL";
    
    $result = $db->query($sql);
    
    if($result && $db->num_rows($result) > 0) {
        $count = 0;
        
        while($item = $db->fetch_assoc($result)) {
            // Create low stock notification
            notify_low_stock(
                $item['id'],
                $item['name'],
                $item['quantity'],
                $threshold
            );
            
            $count++;
        }
        
        echo "Sent {$count} low stock alerts\n";
    } else {
        echo "No new low stock items to report\n";
    }
}

/**
 * Cleanup old notifications
 * Deletes notifications older than 30 days
 */
function cleanup_old_notifications() {
    echo "Cleaning up old notifications...\n";
    
    $days = 30;
    $deleted = clean_old_notifications($days);
    
    echo "Deleted {$deleted} notifications older than {$days} days\n";
}