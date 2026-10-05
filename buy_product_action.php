<?php
session_start(); include 'db.php';
if(!isset($_SESSION['user_id'])) { 
    echo "<script>alert('กรุณาเข้าสู่ระบบก่อนซื้อสินค้าครับ'); window.location='index.php';</script>"; exit; 
}

if(isset($_GET['id'])){
    $pid = (int)$_GET['id'];
    $buyer_id = $_SESSION['user_id'];

    // ดึงข้อมูลสินค้า
    $prod_q = $conn->query("SELECT * FROM products WHERE id = $pid AND status = 'available'");
    if($prod_q->num_rows == 0) {
        echo "<script>alert('สินค้านี้ถูกซื้อไปแล้ว หรือไม่พร้อมขายครับ'); window.location='index.php';</script>"; exit;
    }
    $prod = $prod_q->fetch_assoc();
    $artist_id = $prod['artist_id'];
    $price = $prod['price'];

    // ป้องกันการซื้อของตัวเอง
    if($buyer_id == $artist_id){
        echo "<script>alert('ไม่สามารถซื้อผลงานของตัวเองได้ครับ'); window.location='index.php';</script>"; exit;
    }

    // สร้าง Order
    $stmt = $conn->prepare("INSERT INTO product_sales (product_id, buyer_id, price_paid, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("iid", $pid, $buyer_id, $price);
    $stmt->execute();
    $sale_id = $conn->insert_id;

    // สร้างห้องแชท
    $conn->query("INSERT INTO conversations (related_product_sale_id, type) VALUES ($sale_id, 'product')");
    $cid = $conn->insert_id;

    // เพิ่มคนเข้าแชท (ผู้ซื้อ และ ศิลปิน)
    $conn->query("INSERT INTO conversation_participants (conversation_id, user_id) VALUES ($cid, $buyer_id), ($cid, $artist_id)");

    // แจ้งเตือนศิลปิน
    $msg = "มีลูกค้าสั่งซื้อภาพสำเร็จรูปของคุณ (Sale #$sale_id)";
    $link = "product_chat.php?sale_id=$sale_id";
    $conn->query("INSERT INTO notifications (user_id, sender_id, type, message, link) VALUES ($artist_id, $buyer_id, 'new_order', '$msg', '$link')");

    header("Location: product_chat.php?sale_id=$sale_id"); exit;
}
header("Location: index.php");
?>