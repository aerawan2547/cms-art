<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $post_id = (int)$_POST['post_id'];
    $comment_text = trim($_POST['comment_text'] ?? ''); 
    $image_path = NULL;

    if (!empty($_FILES['comment_image']['name'])) {
        $file_name = $_FILES['comment_image']['name'];
        $file_tmp = $_FILES['comment_image']['tmp_name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array(strtolower($ext), $allowed)) {
            $new_name = "cmt_" . time() . "_" . uniqid() . "." . $ext;
            if (move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
                $image_path = $new_name;
            }
        }
    }

    if ($comment_text !== '' || $image_path !== NULL) {
        $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment_text, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $post_id, $uid, $comment_text, $image_path);
        
        if($stmt->execute()) {
            // ระบบแจ้งเตือน
            $post_info = $conn->query("SELECT artist_id FROM artist_posts WHERE id = $post_id")->fetch_assoc();
            $owner_id = $post_info['artist_id'];
            
            if($owner_id != $uid) { 
                $msg = "มีคอมเมนต์ใหม่";
                $link = "index.php?tab=gallery#post-$post_id";
                $conn->query("INSERT INTO notifications (user_id, sender_id, type, message, link) VALUES ($owner_id, $uid, 'comment', '$msg', '$link')");
            }
        }
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>