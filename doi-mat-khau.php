<?php
include("connect.php");

$username = $_SESSION['username'];
$thongbao = "";

if (isset($_POST['doiMatKhau'])) {
    $mat_khau_cu = $_POST['mat_khau_cu'];
    $mat_khau_moi = $_POST['mat_khau_moi'];
    $xac_nhan = $_POST['xac_nhan'];

    $result = $conn->query("SELECT password FROM users WHERE username='$username'");
    $row = $result->fetch_assoc();

    if (!password_verify($mat_khau_cu, $row['password'])) {
        $thongbao = "Mật khẩu cũ không đúng!";
    } elseif ($mat_khau_moi !== $xac_nhan) {
        $thongbao = "Mật khẩu xác nhận không khớp!";
    } else {
        $hash = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hash' WHERE username='$username'");
        $thongbao = "Đổi mật khẩu thành công!";
    }
}
?>

<div class="page-header">
    <h1>Đổi mật khẩu</h1>
    <p></p>
</div>

<div class="form-container">
    <form method="post" class="form-card">
        <?php if ($thongbao): ?>
            <div class="alert"><?= $thongbao ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label">Mật khẩu cũ</label>
            <input type="password" class="form-input" name="mat_khau_cu" required>
        </div>

        <div class="form-group">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" class="form-input" name="mat_khau_moi" required>
        </div>

        <div class="form-group">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" class="form-input" name="xac_nhan" required>
        </div>

        <div class="form-actions">
            <button type="submit" name="doiMatKhau" class="btn btn-primary">Đổi mật khẩu</button>
        </div>
    </form>
</div>
