<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hóa đơn</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

$sql = "
    SELECT 
        hd.so_hd, 
        hd.ngay_ban, 
        kh.ten_khach_hang,
        sp.ten_sp,
        ct.so_luong, 
        ct.gia_ban, 
        ct.thanh_tien,
        sp.ma_kho,
        hd.phuong_thuc_thanh_toan,
        hd.trang_thai
    FROM hoa_don hd
    JOIN khachhang kh ON hd.ma_khach = kh.ma_khach
    JOIN hoa_don_chi_tiet ct ON hd.so_hd = ct.so_hd
    JOIN sp ON ct.ma_sp = sp.ma_sp
    ORDER BY hd.ngay_ban DESC
";
$result = $conn->query($sql);
?>

<div class="page-header">
    <h1>Hóa đơn bán hàng</h1>
    <p>Theo dõi lịch sử giao dịch </p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm theo số HĐ, khách, thuốc..." onkeyup="timKiem()">
</div>

<div class="table-box">
    <table id="hoaDonTable">
        <thead>
            <tr>
                <th>Số HĐ</th>
                <th>Ngày bán</th>
                <th>Khách hàng</th>
                <th>Thuốc / Sản phẩm</th>
                <th>SL</th>
                <th>Giá bán</th>
                <th>Thành tiền</th>
                <th>Kho</th>
                <th>PTTT</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['so_hd']) ?></strong></td>
                    <td><?= date("d/m/Y", strtotime($row['ngay_ban'])) ?></td>
                    <td style="text-align: left;"><?= htmlspecialchars($row['ten_khach_hang']) ?></td>
                    <td style="text-align: left;"><?= htmlspecialchars($row['ten_sp']) ?></td>
                    <td><?= number_format($row['so_luong']) ?></td>
                    <td><?= number_format($row['gia_ban']) ?> đ</td>
                    <td><?= number_format($row['thanh_tien']) ?> đ</td>
                    <td><?= htmlspecialchars($row['ma_kho']) ?></td>
                    <td><?= htmlspecialchars($row['phuong_thuc_thanh_toan']) ?></td>
                    <td>
                        <span class="badge <?= ($row['trang_thai'] === 'Đã thanh toán') ? 'paid' : 'unpaid' ?>">
                            <?= htmlspecialchars($row['trang_thai']) ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10" style="text-align:center; padding: 20px; color: gray;">Chưa có dữ liệu hóa đơn nào hoặc lỗi truy vấn.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function timKiem(){
    let kw = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#hoaDonTable tbody tr").forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(kw) ? "" : "none";
    });
}
</script>

</body>
</html>