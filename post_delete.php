<?php
session_start();
include 'db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id']) && isset($_SESSION['is_artist'])) {
    $uid = $_SESSION['user_id'];
    $post_id = $_GET['id'];

    // เช็คสิทธิ์ความเป็นเจ้าของโพสต์
    $check = $conn->query("SELECT id FROM artist_posts WHERE id = $post_id AND artist_id = $uid");
    
    if($check->num_rows > 0) {
        // 1. ค้นหารูปภาพทั้งหมดในโพสต์นี้เพื่อลบไฟล์ในโฟลเดอร์ uploads
        $images = $conn->query("SELECT image_path FROM post_images WHERE post_id = $post_id");
        while($img = $images->fetch_assoc()) {
            $file_path = "uploads/" . $img['image_path'];
            if(file_exists($file_path)) {
                unlink($file_path); // สั่งลบไฟล์ภาพจริงๆ ออกจากระบบ
            }
        }
        
        // 2. ลบข้อมูลจากตาราง artist_posts 
        // (post_images, post_comments, post_likes จะถูกลบตามไปด้วยอัตโนมัติจาก ON DELETE CASCADE ในฐานข้อมูล)
        $conn->query("DELETE FROM artist_posts WHERE id = $post_id");
        
        echo "<script>alert('ลบโพสต์เรียบร้อยแล้วครับ'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบได้ คุณไม่ใช่เจ้าของโพสต์ครับ'); window.location='profile.php';</script>";
    }
} else {
    header("Location: index.php");
}
?>