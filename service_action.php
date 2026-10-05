<?php
session_start(); include 'db.php';
// เช็คว่าเป็น Artist
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['is_artist']) && $_SESSION['is_artist']){
    $uid = $_SESSION['user_id'];
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("INSERT INTO commission_services (artist_id, title, description, base_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $uid, $title, $desc, $price);
    
    if($stmt->execute()){
        $sid = $conn->insert_id;
        if(!empty($_FILES['image']['name'])){
            $img_name = time() . "_" . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img_name);
            $conn->query("INSERT INTO service_images (service_id, image_path) VALUES ($sid, '$img_name')");
        }
    }
    header("Location: profile.php");
} else {
    header("Location: index.php");
}
?>