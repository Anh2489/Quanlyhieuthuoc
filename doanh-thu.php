<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

date_default_timezone_set('Asia/Ho_Chi_Minh');
// Lọc theo ngày
$tu_ngay  = $_GET['tu_ngay']  ?? date('Y-m-d');
$den_ngay = $_GET['den_ngay'] ?? date('Y-m-d');

$where = "DATE(hd.ngay_ban) BETWEEN '$tu_ngay' AND '$den_ngay'";

// Tổng doanh thu
$sqlTong = "SELECT SUM(hd.tong_tien_cuoi) AS tong FROM hoa_don hd WHERE $where";
$rsTong  = $conn->query($sqlTong);
$tongDoanhThu = 0;
if ($rsTong) {
    $rowTong = $rsTong->fetch_assoc();
    $tongDoanhThu = $rowTong['tong'] ?? 0;
}

//Danh sách hoá đơn
$sql = "
    SELECT hd.so_hd, hd.ngay_ban, kh.ten_khach_hang, hd.tong_tien_cuoi, hd.trang_thai
    FROM hoa_don hd
    LEFT JOIN khachhang kh ON hd.ma_khach = kh.ma_khach
    WHERE $where
    ORDER BY hd.ngay_ban DESC
";
$list = $conn->query($sql);
?>

<div class="page-header">
    <h1>Doanh thu</h1>
    <p>Thống kê doanh thu bán hàng</p>
</div>

<div class="table-box">

    <div class="table-header">
        <form method="get" action="quanly.php" class="search-filters">
            <input type="hidden" name="page" value="doanh-thu">

            <input type="date" name="tu_ngay" class="search-input"
                   value="<?= htmlspecialchars($tu_ngay) ?>">

            <input type="date" name="den_ngay" class="search-input"
                   value="<?= htmlspecialchars($den_ngay) ?>">

            <button class="btn btn-primary">Lọc</button>

            <a href="doanh-thu-export.php?tu_ngay=<?= $tu_ngay ?>&den_ngay=<?= $den_ngay ?>"
            class="btn btn-info">Xuất Excel
            </a>
        </form>
    </div>

    <div style="padding:12px 16px; font-size:17px;">
        <strong>Tổng doanh thu:</strong>
        <span style="color:#16a34a; font-weight:700;">
            <?= number_format($tongDoanhThu, 0, ',', '.') ?> đ
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Số HĐ</th>
                <th>Ngày bán</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$list || $list->num_rows == 0): ?>
            <tr>
                <td colspan="5" class="text-center text-muted">
                    Chưa có dữ liệu doanh thu
                </td>
            </tr>
        <?php else: ?>
            <?php while ($row = $list->fetch_assoc()): ?>
            <tr>
                <td><?= $row['so_hd'] ?></td>
                <td><?= date('d/m/Y', strtotime($row['ngay_ban'])) ?></td>
                <td><?= htmlspecialchars($row['ten_khach_hang']) ?></td>
                <td><?= number_format($row['tong_tien_cuoi'], 0, ',', '.') ?> đ</td>
                <td>
                    <?php if ($row['trang_thai'] == 'Đã thanh toán'): ?>
                        <span class="status-success">Đã thanh toán</span>
                    <?php else: ?>
                        <span class="status-danger">Chưa thanh toán</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>

</div>