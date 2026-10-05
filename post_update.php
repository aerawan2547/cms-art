<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && isset($_SESSION['is_artist'])) {
    $uid = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $caption = $_POST['caption'];

    // เช็คสิทธิ์ความเป็นเจ้าของโพสต์ (กันคนอื่นแอบแก้)
    $check = $conn->query("SELECT id FROM artist_posts WHERE id = $post_id AND artist_id = $uid");
    
    if($check->num_rows > 0) {
        
        // 1. อัปเดตคำอธิบาย (Caption)
        $stmt = $conn->prepare("UPDATE artist_posts SET caption = ? WHERE id = ?");
        $stmt->bind_param("si", $caption, $post_id);
        $stmt->execute();

        // 2. เช็คว่ามีการเลือกรูปภาพใหม่เข้ามาด้วยหรือไม่
        if(isset($_FILES['new_images']['name']) && !empty($_FILES['new_images']['name'][0])) {
            
            // 2.1 ดึงรายชื่อรูปภาพเก่าออกมาลบไฟล์ในโฟลเดอร์ (ประหยัดพื้นที่เซิร์ฟเวอร์)
            $old_images = $conn->query("SELECT image_path FROM post_images WHERE post_id = $post_id");
            while($img = $old_images->fetch_assoc()) {
                $file_path = "uploads/" . $img['image_path'];
                if(file_exists($file_path)) {
                    unlink($file_path); 
                }
            }
            
            // 2.2 ลบข้อมูลรูปภาพเก่าในฐานข้อมูล
            $conn->query("DELETE FROM post_images WHERE post_id = $post_id");

            // 2.3 วนลูปอัปโหลดรูปภาพใหม่ที่เลือกมา
            foreach($_FILES['new_images']['name'] as $key => $val) {
                $file_name = $_FILES['new_images']['name'][$key];
                $file_tmp = $_FILES['new_images']['tmp_name'][$key];
                $file_error = $_FILES['new_images']['error'][$key];
                
                if($file_error === 0) {
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_name = time() . "_" . $key . "_" . uniqid() . "." . $ext;
                    
                    if(move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
                        $conn->query("INSERT INTO post_images (post_id, image_path) VALUES ($post_id, '$new_name')");
                    }
                }
            }
        }

        echo "<script>alert('อัปเดตโพสต์เรียบร้อยแล้วครับ!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด คุณไม่มีสิทธิ์แก้ไขโพสต์นี้ครับ'); window.location='profile.php';</script>";
    }
} else {
    header("Location: index.php");
}
?>