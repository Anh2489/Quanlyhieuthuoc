<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Quản lý hiệu thuốc</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<?php
include("connect.php");

$today = date("Y-m-d");

// Doanh thu trong ngày
$sql_dt = "SELECT SUM(ct.so_luong * ct.gia_ban) AS tong_doanh_thu 
           FROM hoa_don h
           JOIN hoa_don_chi_tiet ct ON h.so_hd = ct.so_hd
           WHERE DATE(h.ngay_ban) = '$today' 
           AND h.trang_thai = 'Đã thanh toán'";

$res_dt = $conn->query($sql_dt);
$tong_doanh_thu_hom_nay = 0;
if ($res_dt) {
    $row_dt = $res_dt->fetch_assoc();
    $tong_doanh_thu_hom_nay = $row_dt['tong_doanh_thu'] ?? 0;
}

// Hoá đơn trong ngày
$sql_hd = "SELECT COUNT(so_hd) AS total_hd 
           FROM hoa_don 
           WHERE DATE(ngay_ban) = '$today'";
$res_hd = $conn->query($sql_hd);
$hoa_don_hom_nay = 0;
if ($res_hd) {
    $row_hd = $res_hd->fetch_assoc();
    $hoa_don_hom_nay = $row_hd['total_hd'] ?? 0;
}

// Sản phẩm cạn date
$sql_expire_count = "SELECT COUNT(*) AS total_expire 
                     FROM sp 
                     WHERE han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$res_expire_count = $conn->query($sql_expire_count);
$sap_het_han = 0;
if ($res_expire_count) {
    $row_expire_count = $res_expire_count->fetch_assoc();
    $sap_het_han = $row_expire_count['total_expire'] ?? 0;
}

// Top 5 bán chạy
$sql_top = "SELECT s.ma_sp, s.ten_sp, 
                   SUM(ct.so_luong) AS tong_ban, 
                   SUM(ct.so_luong * ct.gia_ban) AS doanh_thu_sp
            FROM hoa_don_chi_tiet ct
            JOIN sp s ON ct.ma_sp = s.ma_sp
            GROUP BY s.ma_sp, s.ten_sp
            ORDER BY tong_ban DESC
            LIMIT 5";
$result_top = $conn->query($sql_top);
?>

<div class="page-header">
    <h1>Trang chủ</h1>
    <p>Tổng quan hoạt động</p>
</div>

<div class="stats">
    <div class="card">
        <h3>DOANH THU HÔM NAY</h3>
        <p><?= number_format($tong_doanh_thu_hom_nay, 0, ',', '.') ?> đ</p>
    </div>
    
    <div class="card">
        <h3>HÓA ĐƠN HÔM NAY</h3>
        <p><?= $hoa_don_hom_nay ?></p>
    </div>

    <div class="card" onclick="openModal()" style="cursor: pointer;">
        <h3>SẢN PHẨM CẬN DATE</h3>
        <p><?= $sap_het_han ?></p>
    </div>
</div>

<div class="table-box">
    <h2>Top 5 Sản Phẩm Bán Chạy Nhất</h2>
    <table>
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
                <?php while($row = $result_top->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['ma_sp'] ?></td>
                        <td style="text-align: left;"><?= $row['ten_sp'] ?></td>
                        <td><?= $row['tong_ban'] ?></td>
                        <td><?= number_format($row['doanh_thu_sp'], 0, ',', '.') ?> đ</td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Chưa có dữ liệu bán hàng</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="expireModal" class="modal"> 
    <div class="modal-content"> 
        <span class="close" onclick="closeModal()">&times;</span> 
        <div style="margin-top:20px">
            <?php include("can-date.php")?>
        </div>
    </div>
</div>

<script>
function openModal() { document.getElementById("expireModal").style.display = "block"; }
function closeModal() { document.getElementById("expireModal").style.display = "none"; }
window.onclick = function(event) {
    if (event.target == document.getElementById("expireModal")) closeModal();
}
</script>

</body>
</html>
