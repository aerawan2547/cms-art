<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

if (isset($_POST['keyword'])) {
    $keyword = $conn->real_escape_string($_POST['keyword']);
    $suggestions = [];

    // 1. ค้นหาจากชื่อบริการ (Services)
    $sql_service = "SELECT title FROM commission_services WHERE title LIKE '%$keyword%' LIMIT 5";
    $res_service = $conn->query($sql_service);
    while ($row = $res_service->fetch_assoc()) {
        $suggestions[] = ['label' => $row['title'], 'type' => 'service'];
    }

    // 2. ค้นหาจากชื่อศิลปิน (Artists)
    $sql_artist = "SELECT display_name FROM artist_profiles WHERE display_name LIKE '%$keyword%' LIMIT 5";
    $res_artist = $conn->query($sql_artist);
    while ($row = $res_artist->fetch_assoc()) {
        $suggestions[] = ['label' => $row['display_name'], 'type' => 'artist'];
    }

    // 3. ค้นหาจากชื่อผู้ใช้ (Users)
    $sql_user = "SELECT username FROM users WHERE username LIKE '%$keyword%' LIMIT 5";
    $res_user = $conn->query($sql_user);
    while ($row = $res_user->fetch_assoc()) {
        $suggestions[] = ['label' => $row['username'], 'type' => 'user'];
    }

    // ส่งข้อมูลกลับเป็น JSON
    echo json_encode($suggestions);
}
?>