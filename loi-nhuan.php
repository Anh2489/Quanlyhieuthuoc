<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

// hiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy ngày từ GET. Nếu không có, mặc định là ngày hôm nay.
$tu_ngay  = (isset($_GET['tu_ngay']) && !empty($_GET['tu_ngay'])) ? $_GET['tu_ngay'] : date('Y-m-d');
$den_ngay = (isset($_GET['den_ngay']) && !empty($_GET['den_ngay'])) ? $_GET['den_ngay'] : date('Y-m-d');

//  Câu SQL tính toán Lợi nhuận
$sql = "
    SELECT 
        SUM(ct.so_luong * ct.gia_ban) AS doanh_thu,
        SUM(ct.so_luong * IFNULL(pn.gia_nhap, 0)) AS tong_gia_von
    FROM hoa_don hd
    INNER JOIN hoa_don_chi_tiet ct ON hd.so_hd = ct.so_hd
    LEFT JOIN (
        -- Lấy giá nhập gần nhất của mỗi sản phẩm
        SELECT p1.ma_sp, p1.gia_nhap
        FROM phieunhap p1
        WHERE p1.so_pn = (SELECT MAX(p2.so_pn) FROM phieunhap p2 WHERE p2.ma_sp = p1.ma_sp)
    ) pn ON ct.ma_sp = pn.ma_sp
    WHERE hd.trang_thai = 'Đã thanh toán'
    AND DATE(hd.ngay_ban) >= '$tu_ngay' 
    AND DATE(hd.ngay_ban) <= '$den_ngay'
";

$rs = $conn->query($sql);
$data = $rs->fetch_assoc();

$doanhThu = $data['doanh_thu'] ?? 0;
$chiPhi   = $data['tong_gia_von'] ?? 0; 
$loiNhuan = $doanhThu - $chiPhi;
?>

<!-- Giao diện -->
<div class="page-header">
    <h1>Lợi nhuận</h1>
    <p>Thống kê lãi lỗ bán thuốc</p>
</div>

<div class="table-box">

    <div class="table-header">
        <form method="get" action="quanly.php" class="search-filters">
            <input type="hidden" name="page" value="loi-nhuan">

            <input type="date" name="tu_ngay" class="search-input"
                   value="<?= htmlspecialchars($tu_ngay) ?>">

            <input type="date" name="den_ngay" class="search-input"
                   value="<?= htmlspecialchars($den_ngay) ?>">

            <button class="btn btn-primary">Lọc</button>
        </form>
    </div>

    <div style="padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:20px;">
        <div class="card">
            <h3>Doanh thu</h3>
            <p style="color:#16a34a;">
                <?= number_format($doanhThu, 0, ',', '.') ?> đ
            </p>
        </div>

        <div class="card">
            <h3>Chi phí nhập</h3>
            <p style="color:#dc2626;">
                <?= number_format($chiPhi, 0, ',', '.') ?> đ
            </p>
        </div>

        <div class="card">
            <h3>Lợi nhuận</h3>
            <p style="color:<?= $loiNhuan >= 0 ? '#2563eb' : '#dc2626' ?>">
                <?= number_format($loiNhuan, 0, ',', '.') ?> đ
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Chỉ tiêu</th>
                <th>Số tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tổng doanh thu</td>
                <td><?= number_format($doanhThu, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <td>Tổng chi phí nhập</td>
                <td><?= number_format($chiPhi, 0, ',', '.') ?> đ</td>
            </tr>
            <tr>
                <td><strong>Lợi nhuận</strong></td>
                <td>
                    <strong style="color:<?= $loiNhuan >= 0 ? '#16a34a' : '#dc2626' ?>">
                        <?= number_format($loiNhuan, 0, ',', '.') ?> đ
                    </strong>
                </td>
            </tr>
        </tbody>
    </table>

</div>