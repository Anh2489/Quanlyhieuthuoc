<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

// Lấy tham số nhập
$tu_ngay  = $_GET['tu_ngay'] ?? '';
$den_ngay = $_GET['den_ngay'] ?? '';

$where = "1=1";
if ($tu_ngay && $den_ngay) {
    $where .= " AND ngay_ban BETWEEN '$tu_ngay' AND '$den_ngay'";
}

// Truy vấn dl
$sql = "
    SELECT so_hd, ngay_ban, ten_khach, tong_tien_cuoi, trang_thai
    FROM hoa_don
    WHERE $where
    ORDER BY ngay_ban DESC
";
$rs = $conn->query($sql);

//Cấu hình header để tải file
$filename = "bao_cao_doanh_thu_" . date("d_m_Y") . ".csv";
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename");

/* Ghi BOM để Excel nhận diện đúng định dạng UTF-8 (không bị lỗi tiếng Việt) */
echo "\xEF\xBB\xBF";

/* MỞ LUỒNG XUẤT DỮ LIỆU */
$output = fopen("php://output", "w");

/* ĐẶT TIÊU ĐỀ CỘT CHO FILE EXCEL */
fputcsv($output, [
    "Số Hóa Đơn",
    "Ngày Bán",
    "Tên Khách Hàng",
    "Tổng Tiền (VNĐ)",
    "Trạng Thái"
]);

// Lấy dữ liệu từ dâtbase vào file
if ($rs) {
    while ($row = $rs->fetch_assoc()) {
        fputcsv($output, [
            $row['so_hd'],
            date('d/m/Y', strtotime($row['ngay_ban'])),
            $row['ten_khach'],
            number_format($row['tong_tien_cuoi'], 0, ',', '.'),
            $row['trang_thai']
        ]);
    }
}

/* ĐÓNG LUỒNG VÀ KẾT THÚC */
fclose($output);
exit;