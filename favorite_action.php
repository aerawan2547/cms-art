<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$service_id = (int)$_GET['id'];
// ให้เด้งกลับไปหน้าที่กดส่งมา
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

// สร้างตารางเก็บรายการโปรดอัตโนมัติ (ถ้ายังไม่มี)
$conn->query("CREATE TABLE IF NOT EXISTS service_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT, 
    service_id INT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// เช็คว่าเคยกดหัวใจโพสต์นี้ไปหรือยัง
$check = $conn->query("SELECT id FROM service_favorites WHERE user_id = $uid AND service_id = $service_id");

if ($check && $check->num_rows > 0) {
    // ถ้าเคยมีแล้ว = ให้เอาออกจากรายการโปรด
    $conn->query("DELETE FROM service_favorites WHERE user_id = $uid AND service_id = $service_id");
    $_SESSION['swal_success'] = "ลบออกจากรายการโปรดแล้ว 💔";
} else {
    // ถ้ายังไม่มี = ให้เพิ่มลงรายการโปรด
    $conn->query("INSERT INTO service_favorites (user_id, service_id) VALUES ($uid, $service_id)");
    $_SESSION['swal_success'] = "เพิ่มลงในรายการโปรดแล้ว ❤️";
}

header("Location: " . $return_url);
exit;
?>