<?php
session_start(); include 'db.php';
if(!isset($_SESSION['user_id'])) header("Location: index.php");

$buyer = $_SESSION['user_id'];
$service = (int)$_POST['service_id'];
$artist = (int)$_POST['artist_id'];
$price = (float)$_POST['price'];

$sql = "INSERT INTO commission_orders (service_id, buyer_id, artist_id, agreed_price, status) VALUES (?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiid", $service, $buyer, $artist, $price);
$stmt->execute();
$oid = $conn->insert_id;

$conn->query("INSERT INTO conversations (related_order_id, type) VALUES ($oid, 'order')");
$cid = $conn->insert_id;

$conn->query("INSERT INTO conversation_participants (conversation_id, user_id) VALUES ($cid, $buyer), ($cid, $artist)");

// ระบบแจ้งเตือนศิลปินว่ามีงานเข้า
$msg = "ออเดอร์ใหม่! มีคนจ้างงานคุณครับ (Order #$oid)";
$link = "chat.php?order_id=$oid";
$conn->query("INSERT INTO notifications (user_id, sender_id, type, message, link) VALUES ($artist, $buyer, 'new_order', '$msg', '$link')");

header("Location: chat.php?order_id=$oid");
?>