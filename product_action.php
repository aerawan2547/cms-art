<?php
session_start(); include 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_artist']) && $_SESSION['is_artist']){
    $uid = $_SESSION['user_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['desc']);
    $price = (float)$_POST['price'];

    $full_name = ""; // ไม่บังคับอัปโหลดไฟล์เต็มตอนสร้างแล้ว
    $first_preview = "";
    
    if(!empty($_FILES['preview_images']['name'][0])){
        $stmt = $conn->prepare("INSERT INTO products (artist_id, title, description, price, preview_image, file_path, status) VALUES (?, ?, ?, ?, '', ?, 'available')");
        $stmt->bind_param("issds", $uid, $title, $desc, $price, $full_name);
        
        if($stmt->execute()){
            $product_id = $conn->insert_id;
            foreach($_FILES['preview_images']['name'] as $key => $val){
                $file_error = $_FILES['preview_images']['error'][$key];
                if($file_error === 0){
                    $ext = pathinfo($_FILES['preview_images']['name'][$key], PATHINFO_EXTENSION);
                    $new_name = "prod_prev_" . time() . "_" . $key . "_" . uniqid() . "." . $ext;
                    if(move_uploaded_file($_FILES['preview_images']['tmp_name'][$key], "uploads/" . $new_name)){
                        if($first_preview == "") $first_preview = $new_name; 
                        $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ($product_id, '$new_name')");
                    }
                }
            }
            if($first_preview != ""){
                $conn->query("UPDATE products SET preview_image = '$first_preview' WHERE id = $product_id");
            }
            $_SESSION['swal_success'] = 'วางจำหน่ายภาพสำเร็จรูปเรียบร้อยแล้ว!';
        }
    }
    header("Location: profile.php"); exit;
} else {
    header("Location: index.php"); exit;
}
?>