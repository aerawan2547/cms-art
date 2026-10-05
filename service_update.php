<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $sid = (int)$_POST['service_id'];
    
    // ตรวจสอบความเป็นเจ้าของก่อน
    $check = $conn->query("SELECT id FROM commission_services WHERE id=$sid AND artist_id=$uid");
    if ($check->num_rows == 0) {
        die("Unauthorized Access");
    }

    // 1. อัปเดตข้อมูลทั่วไป
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['desc']);
    $price = (float)$_POST['price'];
    
    $conn->query("UPDATE commission_services SET title='$title', description='$desc', base_price='$price' WHERE id=$sid");

    // 2. ลบรูปภาพที่เลือก (Delete Selected Images)
    if (isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
        foreach ($_POST['delete_ids'] as $del_id) {
            $del_id = (int)$del_id;
            // ดึงชื่อไฟล์เพื่อลบจากโฟลเดอร์
            $q = $conn->query("SELECT image_path FROM service_images WHERE id=$del_id AND service_id=$sid");
            if ($q->num_rows > 0) {
                $file = $q->fetch_assoc()['image_path'];
                $file_path = "uploads/" . $file;
                if (file_exists($file_path)) {
                    unlink($file_path); // ลบไฟล์
                }
                // ลบจากฐานข้อมูล
                $conn->query("DELETE FROM service_images WHERE id=$del_id");
            }
        }
    }

    // 3. เพิ่มรูปภาพใหม่ (Add New Images)
    if (!empty($_FILES['images']['name'][0])) {
        $total = count($_FILES['images']['name']);
        for ($i = 0; $i < $total; $i++) {
            $tmpFilePath = $_FILES['images']['tmp_name'][$i];
            if ($tmpFilePath != "") {
                $fileName = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $newFileName = time() . "_" . uniqid() . "." . $ext;
                    $uploadPath = "uploads/" . $newFileName;
                    
                    if (move_uploaded_file($tmpFilePath, $uploadPath)) {
                        $conn->query("INSERT INTO service_images (service_id, image_path) VALUES ($sid, '$newFileName')");
                    }
                }
            }
        }
    }

    // กลับไปหน้าเดิม
    header("Location: view_service.php?id=$sid");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>