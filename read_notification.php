<?php
session_start();
include 'db.php';

if(isset($_GET['id']) && isset($_SESSION['user_id'])){
    $notif_id = (int)$_GET['id'];
    $uid = $_SESSION['user_id'];
    
    // อัปเดตว่าอ่านแจ้งเตือนนี้แล้ว
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $uid");
    
    // ดึงลิงก์เพื่อเด้งไปยังหน้าโพสต์นั้น
    $check_col = $conn->query("SHOW COLUMNS FROM notifications LIKE 'link'");
    if($check_col && $check_col->num_rows > 0) {
        $q = $conn->query("SELECT link FROM notifications WHERE id = $notif_id AND user_id = $uid");
        if($q && $q->num_rows > 0) {
            $row = $q->fetch_assoc();
            if(!empty($row['link']) && $row['link'] != '#') {
                header("Location: " . $row['link']);
                exit;
            }
        }
    }
}

// ถ้าหาลิงก์ไม่เจอ หรือไม่มีลิงก์ ให้กลับไปหน้าโปรไฟล์
header("Location: profile.php");
exit;
?>