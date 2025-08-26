<?php include_once('includes/load.php'); ?>
<?php
// Check if form was submitted
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $req_fields = array('username_email');
    validate_fields($req_fields);
    
    $username_email = remove_junk($_POST['username_email']);
    
    if(empty($errors)){
        // Check if user exists by username or email
        $user = find_user_by_username_or_email($username_email);
        
        if($user){
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
            
            // Store reset token in database
            $result = store_reset_token($user['id'], $reset_token, $expiry_time);
            
            if($result){
                // Send reset email (you'll need to implement this)
                $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;
                
                // For now, we'll just show a success message
                $session->msg("s", "If an account with that username/email exists, a password reset link has been sent.");
                redirect('index.php', false);
            } else {
                $session->msg("d", "Something went wrong. Please try again.");
                redirect('index.php', false);
            }
        } else {
            // Don't reveal if user exists or not for security
            $session->msg("s", "If an account with that username/email exists, a password reset link has been sent.");
            redirect('index.php', false);
        }
    } else {
        $session->msg("d", $errors);
        redirect('index.php', false);
    }
} else {
    // If accessed directly without POST, redirect to login
    redirect('index.php', false);
}

// Helper function to find user by username or email
function find_user_by_username_or_email($username_email) {
    global $db;
    $username_email = $db->escape($username_email);
    $sql = "SELECT * FROM users WHERE username = '{$username_email}' OR email = '{$username_email}' LIMIT 1";
    $result = $db->query($sql);
    return($db->num_rows($result) === 1 ? $db->fetch_assoc($result) : false);
}

// Helper function to store reset token
function store_reset_token($user_id, $token, $expiry) {
    global $db;
    $user_id = (int)$user_id;
    $token = $db->escape($token);
    $expiry = $db->escape($expiry);
    
    // First, delete any existing tokens for this user
    $delete_sql = "DELETE FROM password_resets WHERE user_id = {$user_id}";
    $db->query($delete_sql);
    
    // Insert new token
    $sql = "INSERT INTO password_resets (user_id, token, expires_at, created_at) ";
    $sql .= "VALUES ({$user_id}, '{$token}', '{$expiry}', NOW())";
    
    return $db->query($sql);
}
?>