<?php
session_start(); include 'db.php';

if(!isset($_SESSION['user_id'])) { 
    // ถ้ายังไม่ล็อกอิน ให้กลับไปหน้าแรก (หรือเด้ง Modal Login ก็ได้)
    header("Location: index.php"); exit; 
}

$pid = $_GET['id'];
$uid = $_SESSION['user_id'];

// เช็คว่าเคยกดไลค์ยัง
$check = $conn->query("SELECT id FROM post_likes WHERE post_id=$pid AND user_id=$uid");

if($check->num_rows > 0){
    // ถ้ามีแล้ว -> ลบออก (Unlike)
    $conn->query("DELETE FROM post_likes WHERE post_id=$pid AND user_id=$uid");
} else {
    // ถ้ายังไม่มี -> เพิ่มไลค์ (Like)
    $conn->query("INSERT INTO post_likes (post_id, user_id) VALUES ($pid, $uid)");
}

// กลับไปหน้าเดิม พร้อม Scroll ไปที่โพสต์นั้น
header("Location: index.php?tab=gallery#post-$pid");
?>