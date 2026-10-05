<?php
session_start(); include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id']) && isset($_SESSION['user_id'])){
    $del_id = (int)$_POST['delete_id'];
    $uid = $_SESSION['user_id'];
    
    // เช็คสิทธิ์ว่าเป็นศิลปินเจ้าของภาพจริงๆ หรือเป็นแอดมิน
    $check = $conn->query("SELECT id, preview_image, file_path FROM products WHERE id = $del_id AND (artist_id = $uid OR '{$_SESSION['role']}' = 'admin')");
    if($check->num_rows > 0){
        $prod = $check->fetch_assoc();
        @unlink("uploads/" . $prod['preview_image']);
        @unlink("uploads/" . $prod['file_path']);
        $conn->query("DELETE FROM products WHERE id = $del_id");
        $_SESSION['swal_success'] = 'ลบภาพสำเร็จรูปเรียบร้อยแล้ว!';
    }
    
    // ถ้าแอดมินลบ ให้กลับไปหน้าแอดมิน ถ้าศิลปินลบ ให้กลับหน้าโปรไฟล์
    if($_SESSION['role'] == 'admin') header("Location: admin.php?p=products");
    else header("Location: profile.php");
    exit;
}
header("Location: index.php");
?>