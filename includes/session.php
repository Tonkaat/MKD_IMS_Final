<?php
// Make sure this file doesn't output anything before session_start
session_start();

class Session {
 public $msg;
 private $user_is_logged_in = false;
 
 function __construct(){
   $this->flash_msg();
   $this->userLoginSetup();
 }
 
 public function isUserLoggedIn(){
   return $this->user_is_logged_in;
 }
 
 public function login($user_id){
   $_SESSION['user_id'] = $user_id;
 }
 
 private function userLoginSetup()
 {
   if(isset($_SESSION['user_id']))
   {
     $this->user_is_logged_in = true;
   } else {
     $this->user_is_logged_in = false;
   }
 }
 
 public function logout(){
   unset($_SESSION['user_id']);
 }
 
 public function msg($type ='', $msg =''){
   if(!empty($msg)){
      if(strlen(trim($type)) == 1){
        $type = str_replace( array('d', 'i', 'w','s'), array('danger', 'info', 'warning','success'), $type );
      }
      $_SESSION['msg'][$type] = $msg;
   } else {
     return $this->msg;
   }
 }
 
 private function flash_msg(){
   if(isset($_SESSION['msg'])) {
     $this->msg = $_SESSION['msg'];
     unset($_SESSION['msg']);
   } else {
     $this->msg = null; // Fix: ensure property is initialized
   }
 }
 
 public function has_msg() {
   return isset($_SESSION['msg']) && !empty($_SESSION['msg']);
 }
 
 // Add this method to check if user is admin
 public function isAdmin() {
   if(!$this->isUserLoggedIn()) return false;
   
   // Assuming you have a user_level stored in session or can be retrieved from DB
   // You may need to adapt this based on your actual implementation
   return isset($_SESSION['user_level']) && $_SESSION['user_level'] === 1;
 }
}

$session = new Session();
$msg = $session->msg();
?>