<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$item_id = (int)$_GET['id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'product'; 
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

$conn->query("CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    user_id INT, 
    product_id INT NULL, 
    service_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$check_col = $conn->query("SHOW COLUMNS FROM cart_items LIKE 'service_id'");
if($check_col->num_rows == 0){
    $conn->query("ALTER TABLE cart_items ADD COLUMN service_id INT NULL AFTER product_id");
    $conn->query("ALTER TABLE cart_items MODIFY COLUMN product_id INT NULL");
}

if($type == 'product') {
    $check_owner = $conn->query("SELECT artist_id, status FROM products WHERE id = $item_id");
    if($check_owner->num_rows > 0) {
        $prod = $check_owner->fetch_assoc();
        if($prod['artist_id'] == $uid) { $_SESSION['swal_error'] = "ไม่สามารถเพิ่มผลงานของตนเองลงตะกร้าได้"; header("Location: " . $return_url); exit; }
        if($prod['status'] != 'available') { $_SESSION['swal_error'] = "สินค้านี้ไม่พร้อมจำหน่าย"; header("Location: " . $return_url); exit; }
    }
    $check_cart = $conn->query("SELECT id FROM cart_items WHERE user_id = $uid AND product_id = $item_id");
    if ($check_cart && $check_cart->num_rows > 0) { 
        $_SESSION['swal_error'] = "สินค้านี้อยู่ในตะกร้าแล้ว"; 
    } else { 
        $conn->query("INSERT INTO cart_items (user_id, product_id) VALUES ($uid, $item_id)"); 
        $_SESSION['swal_success'] = "เพิ่มลงตะกร้าเรียบร้อย"; 
    }
} else if ($type == 'service') {
    $check_owner = $conn->query("SELECT artist_id, status FROM commission_services WHERE id = $item_id");
    if($check_owner->num_rows > 0) {
        $srv = $check_owner->fetch_assoc();
        if($srv['artist_id'] == $uid) { $_SESSION['swal_error'] = "ไม่สามารถจ้างงานตนเองได้"; header("Location: " . $return_url); exit; }
        if($srv['status'] != 'active') { $_SESSION['swal_error'] = "ประกาศรับงานนี้ปิดรับชั่วคราว"; header("Location: " . $return_url); exit; }
    }
    $check_cart = $conn->query("SELECT id FROM cart_items WHERE user_id = $uid AND service_id = $item_id");
    if ($check_cart && $check_cart->num_rows > 0) { 
        $_SESSION['swal_error'] = "ประกาศนี้อยู่ในตะกร้าแล้ว"; 
    } else { 
        $conn->query("INSERT INTO cart_items (user_id, service_id) VALUES ($uid, $item_id)"); 
        $_SESSION['swal_success'] = "เพิ่มลงตะกร้าเรียบร้อย"; 
    }
}

header("Location: " . $return_url);
exit;
?>