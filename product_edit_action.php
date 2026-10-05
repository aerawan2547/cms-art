<?php
session_start(); include 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_artist']) && $_SESSION['is_artist']){
    $uid = $_SESSION['user_id'];
    $pid = (int)$_POST['product_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['desc']);
    $price = (float)$_POST['price'];

    $check = $conn->query("SELECT id, preview_image FROM products WHERE id = $pid AND artist_id = $uid LIMIT 1");
    if($check->num_rows == 0){ $_SESSION['swal_error'] = 'สิทธิ์ไม่ถูกต้อง'; header("Location: profile.php"); exit; }
    $current_prod = $check->fetch_assoc();

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $title, $desc, $price, $pid);
        $stmt->execute();

        if(!empty($_POST['delete_previews'])){
            foreach($_POST['delete_previews'] as $img_id){
                $img_id = (int)$img_id;
                $file_q = $conn->query("SELECT image_path FROM product_images WHERE id = $img_id AND product_id = $pid");
                if($f = $file_q->fetch_assoc()){
                    @unlink("uploads/" . $f['image_path']);
                    $conn->query("DELETE FROM product_images WHERE id = $img_id"); 
                }
            }
        }

        if(!empty($_FILES['new_preview_images']['name'][0])){
            foreach($_FILES['new_preview_images']['name'] as $key => $val){
                if($_FILES['new_preview_images']['error'][$key] === 0){
                    $ext = pathinfo($_FILES['new_preview_images']['name'][$key], PATHINFO_EXTENSION);
                    $new_name = "prod_prev_" . time() . "_" . $key . "_" . uniqid() . "." . $ext;
                    if(move_uploaded_file($_FILES['new_preview_images']['tmp_name'][$key], "uploads/" . $new_name)){
                        $conn->query("INSERT INTO product_images (product_id, image_path) VALUES ($pid, '$new_name')");
                    }
                }
            }
        }

        $img_check = $conn->query("SELECT image_path FROM product_images WHERE product_id = $pid LIMIT 1");
        if($img_check->num_rows > 0){
            $new_cover = $img_check->fetch_assoc()['image_path'];
            if($current_prod['preview_image'] != $new_cover){
                $is_still_sub = $conn->query("SELECT id FROM product_images WHERE product_id = $pid AND image_path = '{$current_prod['preview_image']}'")->num_rows > 0;
                if(!$is_still_sub && !empty($current_prod['preview_image'])){
                    @unlink("uploads/" . $current_prod['preview_image']); 
                }
                $conn->query("UPDATE products SET preview_image = '$new_cover' WHERE id = $pid"); 
            }
        } else {
            if(!empty($current_prod['preview_image'])){ @unlink("uploads/" . $current_prod['preview_image']); }
            $conn->query("UPDATE products SET preview_image = '' WHERE id = $pid");
        }

        $conn->commit(); 
        $_SESSION['swal_success'] = 'แก้ไขข้อมูลเรียบร้อยแล้ว!';
    } catch (Exception $e) {
        $conn->rollback(); 
        $_SESSION['swal_error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
    header("Location: profile.php"); exit;
} else {
    header("Location: index.php"); exit;
}
?>