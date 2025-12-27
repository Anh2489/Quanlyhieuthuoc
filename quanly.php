<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý hiệu thuốc</title>
<link rel="stylesheet" href="form.css">
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$currentPage = $_GET['page'] ?? 'trang-chu';
?>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <span>Hệ Thống Quản Lý Hiệu Thuốc</span>
        </div>
        <div class="setting">
            <button class="setting-btn">⚙️ Cài đặt</button>
            <div class="setting-menu">
                <a href="quanly.php?page=thong-tin-cn">Thông tin cá nhân</a>
                <a href="quanly.php?page=doi-mat-khau">Đổi mật khẩu</a>
                <a href="logout.php" class="logout-btn">Đăng xuất</a>
             </div>
        </div>
    </div>
</header>

<div class="container">
    <nav class="sidebar">
        <div class="menu-title">TỔNG QUAN</div>
        <div class="menu-group">
            <a class="<?php echo $currentPage=='trang-chu'?'active':''?>" href="quanly.php?page=trang-chu">Trang chủ</a>
        </div>
        <div class="menu-title">DANH MỤC</div>
        <div class="menu-group">
            <a class="<?php echo $currentPage=='quan-ly-dm'?'active':''?>" href="quanly.php?page=quan-ly-dm">Danh mục sản phẩm</a>
            <a class="<?php echo $currentPage=='nha-cung-cap'?'active':''?>" href="quanly.php?page=nha-cung-cap">Nhà cung cấp</a>
            <a class="<?php echo $currentPage=='khach-hang'?'active':''?>" href="quanly.php?page=khach-hang">Khách hàng</a>
        </div>
        <div class="menu-title">NHẬP - KHO</div>
        <div class="menu-group">
            <a class="<?php echo $currentPage=='phieu-nhap'?'active':''?>" href="quanly.php?page=phieu-nhap">Nhập hàng</a>
            <a class="<?php echo $currentPage=='quan-ly-kho'?'active':''?>" href="quanly.php?page=quan-ly-kho">Quản lý kho</a>
        </div>
        <div class="menu-title">BÁN HÀNG</div>
        <div class="menu-group">
            <a class="<?php echo $currentPage=='ban-hang'?'active':''?>" href="quanly.php?page=ban-hang">Hoá đơn</a>
            <a class="<?php echo $currentPage=='giao-dich'?'active':''?>" href="quanly.php?page=giao-dich">Tra cứu giao dịch</a>
        </div>
        <div class="menu-title">THỐNG KÊ</div>
        <div class="menu-group">
            <a class="<?php echo $currentPage=='doanh-thu'?'active':''?>" href="quanly.php?page=doanh-thu">Doanh thu</a>
            <a class="<?php echo $currentPage=='thu-chi'?'active':''?>" href="quanly.php?page=thu-chi">Thu chi</a>
            <a class="<?php echo $currentPage=='hang-ton'?'active':''?>" href="quanly.php?page=hang-ton">Tồn kho</a>

        </div>
    </nav>
    <main class="main-content">
        <?php
        if (!isset($_GET['page'])) {
            include("trang-chu.php");
        } else {
            switch ($_GET['page']) {
                case "trang-chu": include("trang-chu.php"); break;
                case "quan-ly-dm": include("quan-ly-dm.php"); break;
                case "nha-cung-cap": include("nha-cung-cap.php"); break;
                case "khach-hang": include("khach-hang.php"); break;
                case "phieu-nhap": include("phieu-nhap.php"); break; 
                case "quan-ly-kho": include("quan-ly-kho.php"); break;
                case "hoa-don": include("hoa-don.php"); break;
                case "giao-dich": include("giao-dich.php"); break;
                case "doanh-thu": include("doanh-thu.php"); break;
                case "thu-chi": include("thu-chi.php"); break;
                case "hang-ton": include("hang-ton.php"); break;
                case "thong-tin-cn": include("thong-tin-cn.php");break;
                case "doi-mat-khau": include("doi-mat-khau.php");break;
                case "logout":
                    session_destroy();
                    session_unset();
                    header('Location: login.php');
                    exit();
                    break;
                default: echo "<h2>Trang không tồn tại!</h2>";
            }
        }
        ?>
    </main>
</div>

<script>
const setting = document.querySelector('.setting');
const settingBtn = document.querySelector('.setting-btn');
const settingMenu = document.querySelector('.setting-menu');

settingBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    settingMenu.classList.toggle('show');
});

// Click ra ngoài setting thì đóng
document.addEventListener('click', function (e) {
    if (!setting.contains(e.target)) {
        settingMenu.classList.remove('show');
    }
});
</script>
</body>
</html>
