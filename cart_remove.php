<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['cart_id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['user_id'];
$cart_id = (int)$_GET['cart_id'];
$return_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

$conn->query("DELETE FROM cart_items WHERE id = $cart_id AND user_id = $uid");

$_SESSION['swal_success'] = "ลบสินค้าออกจากตะกร้าแล้วครับ 🗑️";
header("Location: " . $return_url);
exit;
?>