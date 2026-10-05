<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = $_SESSION['user_id'];
    $display_name = trim($_POST['display_name']);
    $bio = trim($_POST['bio'] ?? '');

    // 1. อัปเดตข้อมูลทั่วไป
    $stmt_user = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
    $stmt_user->bind_param("si", $display_name, $uid);
    $stmt_user->execute();

    $check_artist = $conn->query("SELECT user_id FROM artist_profiles WHERE user_id = $uid");
    if ($check_artist->num_rows > 0) {
        $stmt_artist = $conn->prepare("UPDATE artist_profiles SET display_name = ?, bio = ? WHERE user_id = ?");
        $stmt_artist->bind_param("ssi", $display_name, $bio, $uid);
        $stmt_artist->execute();
    }
    
    $_SESSION['display_name'] = $display_name;

    // 2. จัดการรูปโปรไฟล์
    if (!empty($_POST['cropped_image'])) {
        $img_data = $_POST['cropped_image'];
        $image_parts = explode(";base64,", $img_data);
        
        if(count($image_parts) == 2) {
            $image_base64 = base64_decode($image_parts[1]);
            $new_name = "profile_" . $uid . "_" . time() . ".jpg";
            $file_path = "uploads/" . $new_name;
            
            if (file_put_contents($file_path, $image_base64)) {
                $conn->query("UPDATE users SET profile_image = '$new_name' WHERE id = $uid");
            }
        }
    }

    // 3. จัดการอัปโหลดรูปปก (Cover Image)
    if (!empty($_FILES['cover_image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['cover_image']['name'];
        $file_tmp = $_FILES['cover_image']['tmp_name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $new_name = "cover_" . $uid . "_" . time() . "." . $ext;
            if (move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
                $conn->query("UPDATE users SET cover_image = '$new_name' WHERE id = $uid");
            }
        }
    }

    // เปลี่ยนจาก alert() เป็น SweetAlert
    $_SESSION['swal_success'] = 'บันทึกข้อมูลโปรไฟล์เรียบร้อย';
    header("Location: profile.php");
    exit;
} else {
    header("Location: profile.php");
    exit;
}
?>