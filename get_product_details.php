<?php
session_start(); include 'db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || !isset($_GET['id'])){ echo json_encode(['error' => 'Unauthorized or missing ID']); exit; }

$uid = $_SESSION['user_id'];
$pid = (int)$_GET['id'];

$prod_q = $conn->query("SELECT * FROM products WHERE id = $pid AND artist_id = $uid LIMIT 1");
if($prod_q->num_rows == 0){ echo json_encode(['error' => 'Product not found or access denied']); exit; }
$prod = $prod_q->fetch_assoc();

$imgs = [];
$imgs_q = $conn->query("SELECT id, image_path FROM product_images WHERE product_id = $pid");
while($img = $imgs_q->fetch_assoc()){ $imgs[] = ['id' => $img['id'], 'path' => $img['image_path']]; }
$prod['images'] = $imgs;

echo json_encode($prod);
?>