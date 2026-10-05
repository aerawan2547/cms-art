<?php
include 'db.php';
// ตั้งรหัสผ่าน 1234 สำหรับทุกคน
$pass = password_hash("1234", PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password_hash = '$pass'");
echo "<h1>✅ รีเซ็ตรหัสผ่านทุก User เป็น '1234' เรียบร้อย!</h1>";
echo "<a href='index.php'>กลับไปล็อกอิน</a>";
?>