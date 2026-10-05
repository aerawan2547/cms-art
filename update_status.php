<?php
session_start(); include 'db.php';
if(!isset($_SESSION['user_id'])) exit;

$oid = $_GET['id'];
$status = $_GET['s']; // 'working' หรือ 'completed'
$uid = $_SESSION['user_id'];

// เช็คว่าเป็น Artist ของออเดอร์นี้จริงไหม
$chk = $conn->query("SELECT artist_id FROM commission_orders WHERE id=$oid");
$ord = $chk->fetch_assoc();

if($ord['artist_id'] == $uid){
    $conn->query("UPDATE commission_orders SET status='$status' WHERE id=$oid");
}
header("Location: profile.php");
?>