<?php
session_start();
include("connect.php");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ĐĂNG NHẬP
    if ($action === 'login') {
        $username = trim($_POST['username']);
        $password = md5($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $_SESSION['username'] = $username;
            header("Location: quanly.php");
            exit;
        } else {
            $error = "Sai tài khoản hoặc mật khẩu!";
        }
    }

    // ĐĂNG KÝ
    if ($action === 'register') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm  = trim($_POST['confirm']);

        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không hợp lệ!";
        } elseif (strlen($password) < 8) {
            $error = "Mật khẩu phải ít nhất 8 ký tự!";
        } elseif ($password !== $confirm) {
            $error = "Mật khẩu nhập lại không khớp!";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $rs = $stmt->get_result();
            if ($rs && $rs->num_rows > 0) {
                $error = "Email đã được đăng ký!";
            } else {
                $hash = md5($password);
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hash);
                if ($stmt->execute()) {
                    // Thông báo thành công, chuyển form về đăng nhập
                    $success = "Đăng ký thành công! Vui lòng đăng nhập.";
                } else {
                    $error = "Có lỗi xảy ra, vui lòng thử lại!";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập / Đăng ký</title>
<style>
body {font-family: 'Segoe UI', sans-serif; margin:0; padding:0; background:#f5f5f5;}
.container {
    width: 350px; margin: 60px auto;
    background:#fff; border:1px solid #ccc;
    border-radius:10px; padding:25px;
    box-shadow:0 0 12px rgba(0,0,0,0.1);
}
h2 {text-align:center; margin-bottom:20px;}
.form-group {margin-bottom:15px;}
.form-label {display:block; margin-bottom:5px; font-weight:bold;}
.form-input {width:100%; padding:8px; border:1px solid #aaa; border-radius:5px; box-sizing:border-box;}
.btn-primary {
    padding:10px 20px; border:none;
    background:#007BFF; color:#fff;
    border-radius:5px; cursor:pointer;
}
.btn-primary:hover {background:#0056b3;}
.switch-text {text-align:center; margin-top:10px;}
.switch-text a {color:#007BFF; cursor:pointer; text-decoration:none;}
.switch-text a:hover {text-decoration:underline;}
.error {color:red; text-align:center; margin-bottom:10px;}
.success {color:green; text-align:center; margin-bottom:10px;}
</style>
<script>
function toggleForm(type) {
    document.getElementById('login-form').style.display   = (type==='login')?'block':'none';
    document.getElementById('register-form').style.display= (type==='register')?'block':'none';
    document.getElementById('errorBox').innerHTML = '';
}
// Nếu đăng ký thành công thì tự động về form đăng nhập sau 3 giây
function autoBackToLogin(){
    toggleForm('login');
}
</script>
</head>
<body onload="toggleForm('<?php echo $success ? 'login' : 'login'; ?>')">
<div class="container">
    <div id="errorBox" class="error"><?php if($error) echo $error; ?></div>
    <?php if($success) echo "<div class='success'>$success</div><script>setTimeout(autoBackToLogin,3000);</script>"; ?>

    <!-- Đăng nhập -->
    <form id="login-form" method="post">
        <h2>Đăng nhập hệ thống</h2>
        <input type="hidden" name="action" value="login">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="username" required class="form-input" autocomplete="off">
        </div>
        <div class="form-group">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" required minlength="8" class="form-input">
        </div>
        <div style="text-align:center;">
            <button type="submit" class="btn-primary">Đăng nhập</button>
        </div>
        <div class="switch-text">
            Bạn chưa có tài khoản?
            <a onclick="toggleForm('register')">Đăng ký ngay</a>
        </div>
    </form>

    <!-- Đăng ký -->
    <form id="register-form" method="post" style="display:none;">
        <h2>Đăng ký tài khoản</h2>
        <input type="hidden" name="action" value="register">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="username" required class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" required minlength="8" class="form-input">
        </div>
        <div class="form-group">
            <label class="form-label">Nhập lại mật khẩu</label>
            <input type="password" name="confirm" required minlength="8" class="form-input">
        </div>
        <div style="text-align:center;">
            <button type="submit" class="btn-primary">Đăng ký</button>
        </div>
        <div class="switch-text">
            Đã có tài khoản?
            <a onclick="toggleForm('login')">Đăng nhập</a>
        </div>
    </form>
</div>
</body>
</html>
