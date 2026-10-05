<?php
session_start(); include 'db.php';

// เช็คสิทธิ์ Artist
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_artist']) && $_SESSION['is_artist']){
    $uid = $_SESSION['user_id'];
    $caption = $_POST['caption'];

    // 1. สร้างโพสต์หลัก
    $stmt = $conn->prepare("INSERT INTO artist_posts (artist_id, caption) VALUES (?, ?)");
    $stmt->bind_param("is", $uid, $caption);
    
    if($stmt->execute()){
        $post_id = $conn->insert_id; // ได้ ID โพสต์มา

        // 2. วนลูปเก็บรูปภาพ (รองรับ Multiple Upload)
        if(isset($_FILES['images']['name']) && is_array($_FILES['images']['name'])){
            foreach($_FILES['images']['name'] as $key => $val){
                $file_name = $_FILES['images']['name'][$key];
                $file_tmp = $_FILES['images']['tmp_name'][$key];
                $file_error = $_FILES['images']['error'][$key];
                
                if($file_error === 0){
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_name = time() . "_" . $key . "_" . uniqid() . "." . $ext;
                    
                    if(move_uploaded_file($file_tmp, "uploads/" . $new_name)){
                        $conn->query("INSERT INTO post_images (post_id, image_path) VALUES ($post_id, '$new_name')");
                    }
                }
            }
        }
    }
    header("Location: index.php?tab=gallery");
} else {
    header("Location: index.php");
}
?>