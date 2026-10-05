<?php
session_start();
include 'db.php';

$step = 1; 
$error_msg = "";

// ขั้นตอน 1: เช็คอีเมล
if (isset($_POST['check_email'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['reset_user_id'] = $row['id'];
        $step = 2; 
    } else {
        $error_msg = "ไม่พบอีเมลนี้ในระบบครับ กรุณาตรวจสอบอีกครั้ง";
    }
}

// ขั้นตอน 2: เซฟรหัสผ่านใหม่
if (isset($_POST['save_new_password']) && isset($_SESSION['reset_user_id'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_new_password'];
    
    if ($new_pass === $confirm_pass) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $uid = $_SESSION['reset_user_id'];
        
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $uid);
        if($stmt->execute()){
            unset($_SESSION['reset_user_id']); 
            echo "<script>alert('เปลี่ยนรหัสผ่านสำเร็จแล้ว! กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่ครับ'); window.location='index.php';</script>";
            exit;
        }
    } else {
        $error_msg = "รหัสผ่านไม่ตรงกันครับ กรุณาลองใหม่";
        $step = 2; 
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลืมรหัสผ่าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: #f0f4f8;">
    
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white" style="max-width: 450px; width: 100%;">
        
        <?php if($step == 1): ?>
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-3">Forgot Password</h2>
            <p class="text-muted small">กรุณากรอกอีเมลที่ใช้สมัครสมาชิกเพื่อตั้งรหัสผ่านใหม่</p>
        </div>
        
        <?php if(!empty($error_msg)) echo "<div class='alert alert-danger small fw-bold rounded-3'>$error_msg</div>"; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted mb-1">Email</label>
                <div class="input-group">
                    <input type="email" name="email" class="form-control bg-light border-0 py-2" required style="background-color: #f0f4f8 !important;">
                    <span class="input-group-text border-0" style="background-color: #f0f4f8 !important;"><i class="fas fa-envelope text-secondary"></i></span>
                </div>
            </div>
            <button type="submit" name="check_email" class="btn w-100 rounded-3 shadow-sm py-2 mb-4 text-white fw-bold" style="background-color: #1a202c; font-size: 1.1rem;">ถัดไป</button>
            <div class="text-center">
                <a href="index.php" class="small text-muted text-decoration-none fw-bold"><i class="fas fa-arrow-left me-1"></i> กลับไปหน้าเข้าสู่ระบบ</a>
            </div>
        </form>

        <?php elseif($step == 2): ?>
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark mb-3">ตั้งรหัสผ่านใหม่</h2>
            <p class="text-muted small">กรุณากำหนดรหัสผ่านใหม่สำหรับบัญชีของคุณ</p>
        </div>
        
        <?php if(!empty($error_msg)) echo "<div class='alert alert-danger small fw-bold rounded-3'>$error_msg</div>"; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted mb-1">รหัสผ่านใหม่</label>
                <div class="input-group">
                    <input type="password" name="new_password" class="form-control bg-light border-0 py-2" required minlength="4" style="background-color: #f0f4f8 !important;">
                    <span class="input-group-text border-0" style="background-color: #f0f4f8 !important;"><i class="fas fa-lock text-secondary"></i></span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold text-muted mb-1">ยืนยันรหัสผ่านใหม่</label>
                <div class="input-group">
                    <input type="password" name="confirm_new_password" class="form-control bg-light border-0 py-2" required minlength="4" style="background-color: #f0f4f8 !important;">
                    <span class="input-group-text border-0" style="background-color: #f0f4f8 !important;"><i class="fas fa-lock text-secondary"></i></span>
                </div>
            </div>
            <button type="submit" name="save_new_password" class="btn w-100 rounded-3 shadow-sm py-2 mb-4 text-white fw-bold" style="background-color: #1a202c; font-size: 1.1rem;">บันทึกรหัสผ่านใหม่</button>
            <div class="text-center">
                <a href="index.php" class="small text-muted text-decoration-none fw-bold">ยกเลิกและกลับหน้าหลัก</a>
            </div>
        </form>
        <?php endif; ?>
        
    </div>

</body>
</html>