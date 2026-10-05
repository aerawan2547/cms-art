<?php
session_start(); include 'db.php';
if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $conn->query("INSERT INTO login_logs (user_id, action) VALUES ($uid, 'logout')");
}
session_unset(); session_destroy();
header("Location: index.php"); exit;
?>