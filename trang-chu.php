<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
<?php
include("connect.php");

// Đếm tổng số thuốc
$sql_thuoc = "SELECT COUNT(*) AS tong_thuoc FROM thuoc";
$result_thuoc = $conn->query($sql_thuoc);
$tong_thuoc = $result_thuoc->fetch_assoc()['tong_thuoc'] ?? 0;

// Đếm tổng tồn kho 
$sql = "SELECT SUM(ton_kho) AS tong_ton FROM kho";
$result =$conn->query($sql);
$row = $result->fetch_assoc();
$tongTon = $row['tong_ton'] ?? 0;  // Nếu NULL thì đặt là 0
   
// Hóa đơn hôm nay
$today = date("Y-m-d");
$sql_hoadon = "SELECT COUNT(so_hd) AS hoa_don_hom_nay FROM hoadon WHERE DATE(ngay_ban) = '$today'";
$result_hd = $conn->query($sql_hoadon);
$hoa_don_hom_nay = $result_hd->fetch_assoc()['hoa_don_hom_nay'] ?? 0;
//danh sách cận date
$sql_expire = "SELECT * 
               FROM thuoc 
               WHERE han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
               ORDER BY han_su_dung ASC";
$result_expire = $conn->query($sql_expire);
$sap_het_han = $result_expire->num_rows;
// TOP 5 THUỐC BÁN CHẠY
$sql_top = "
    SELECT 
        t.ma_thuoc,
        t.ten_thuoc,
        SUM(ct.so_luong) AS tong_so_luong,
        SUM(ct.so_luong * ct.don_gia) AS tong_doanh_thu
    FROM chitiethoadon ct
    JOIN thuoc t ON ct.ma_thuoc = t.ma_thuoc
    GROUP BY t.ma_thuoc, t.ten_thuoc
    ORDER BY tong_so_luong DESC
    LIMIT 5
";
$result_top = $conn->query($sql_top);

?>

<div class="page-header">
    <h1>Trang chủ</h1>
    <p>Tổng quan hoạt động hiệu thuốc</p>
</div>

<div class="stats">
    <div class="card">
        <h3>Tổng số thuốc</h3>
        <p><?= $tong_thuoc ?></p>
    </div>
    <div class="card">
        <h3>Tồn kho</h3>
        <p><?= $tongTon ?></p>
    </div>
    <div class="card">
        <h3>Hóa đơn hôm nay</h3>
        <p><?= $hoa_don_hom_nay ?></p>
    </div>
    <div class="card" onclick="openModal()"> 
        <h3>Sắp hết hạn</h3> 
        <p><?= $sap_het_han ?></p> 
    </div> 
    <!-- Modal --> 
    <div id="expireModal" class="modal"> 
        <div class="modal-content"> 
            <span class="close" onclick="closeModal()">&times;</span> 
            <?php include("can-date.php"); ?> 
        </div>
    </div>
</div>
<div class="quick-actions">
    <h2>Thao tác nhanh</h2>
    <div class="action-grid">
        <a href="thuoc_add.php" class="action-btn">+ Thêm thuốc</a>
        <a href="hoadon_add.php" class="action-btn">📝Tạo hóa đơn</a>
        <a href="phieunhap_add.php" class="action-btn">📦 Nhập hàng</a>
    </div>
</div>

<div class="table-box">
    <h2>Top 5 thuốc bán chạy</h2>
    <table width="100%" cellspacing="0" cellpadding="8">
        <thead>
            <tr>
                <th>Mã thuốc</th>
                <th>Tên thuốc</th>
                <th>Số lượng bán</th>
                <th>Doanh thu</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_top && $result_top->num_rows > 0): ?>
                <?php while ($row = $result_top->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ma_thuoc'] ?></td>
                        <td><?= $row['ten_thuoc'] ?></td>
                        <td><?= $row['tong_so_luong'] ?></td>
                        <td><?= number_format($row['tong_doanh_thu'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Chưa có dữ liệu bán hàng
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function openModal() {
  document.getElementById("expireModal").style.display = "block";
}
function closeModal() {
  document.getElementById("expireModal").style.display = "none";
}
</script>

</body>
</html>
