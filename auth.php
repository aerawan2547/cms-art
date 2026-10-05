<?php
session_start(); include 'db.php';

// --- สมัครสมาชิก ---
if (isset($_POST['register'])) {
    $full_name = trim($_POST['full_name']);
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $role_type = $_POST['role_type'] ?? 'member';

    if ($pass !== $confirm_pass) {
        echo "<script>alert('รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกันครับ'); window.location='index.php';</script>"; exit;
    }

    $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

    // เช็คว่า Username หรือ Email มีแล้วหรือยัง
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $stmt_check->bind_param("ss", $user, $email);
    $stmt_check->execute();
    if($stmt_check->get_result()->num_rows > 0){
        echo "<script>alert('Username หรือ Email นี้มีคนใช้แล้วครับ'); window.location='index.php';</script>"; exit;
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, 'member')");
    $stmt->bind_param("ssss", $full_name, $user, $email, $pass_hash);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        if ($role_type == 'artist') {
            $stmt_art = $conn->prepare("INSERT INTO artist_profiles (user_id, display_name) VALUES (?, ?)");
            $stmt_art->bind_param("is", $new_id, $full_name);
            $stmt_art->execute();
            $conn->query("UPDATE users SET role = 'artist' WHERE id = $new_id");
        }
        echo "<script>alert('ลงทะเบียนสำเร็จ! กรุณาเข้าสู่ระบบครับ'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการสมัครครับ'); window.location='index.php';</script>";
    }
}

// --- เข้าสู่ระบบ ---
if (isset($_POST['login'])) {
    $login_user = trim($_POST['username']); // รับค่าแค่ username
    $pass = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password_hash'])) {
            
            // ระบบ Remember me
            if ($remember) {
                setcookie("remember_user", $login_user, time() + (86400 * 30), "/"); 
            } else {
                setcookie("remember_user", "", time() - 3600, "/"); 
            }

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            $conn->query("INSERT INTO login_logs (user_id, action) VALUES ({$row['id']}, 'login')");

            if ($row['role'] == 'admin') {
                $_SESSION['is_admin'] = true;
                header("Location: admin.php"); exit;
            }

            $check_artist = $conn->query("SELECT display_name FROM artist_profiles WHERE user_id = " . $row['id']);
            if ($check_artist->num_rows > 0) {
                $_SESSION['is_artist'] = true;
                $_SESSION['display_name'] = $check_artist->fetch_assoc()['display_name'];
            } else {
                $_SESSION['is_artist'] = false;
                $_SESSION['display_name'] = $row['full_name'] ?? $row['username'];
            }
            header("Location: index.php"); 
        } else { echo "<script>alert('รหัสผ่านไม่ถูกต้องครับ'); window.location='index.php';</script>"; }
    } else { echo "<script>alert('ไม่พบ Username นี้ครับ'); window.location='index.php';</script>"; }
}
?>