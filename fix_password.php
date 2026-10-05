<?php
// ไฟล์นี้ใช้แก้รหัสผ่านให้เป็น Hash ที่ถูกต้อง
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "art_commission_pj";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// สร้างรหัสผ่าน '1234' แบบเข้ารหัส (Hash)
$new_pass = password_hash("1234", PASSWORD_DEFAULT);

// อัปเดต User ทั้งหมดให้ใช้รหัส '1234'
$sql = "UPDATE users SET password_hash = '$new_pass'";

if ($conn->query($sql) === TRUE) {
    echo "<h1>✅ แก้ไขรหัสผ่านเรียบร้อย!</h1>";
    echo "<h3>ตอนนี้ทุก User (Artist_Kung, Admin_Boss) ใช้รหัสผ่าน: <span style='color:red'>1234</span></h3>";
    echo "<a href='index.php'>กลับไปล็อกอินใหม่</a>";
} else {
    echo "Error updating record: " . $conn->error;
}
?>