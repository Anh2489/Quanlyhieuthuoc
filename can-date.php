<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

// Lấy danh sách thuốc sắp hết hạn (<= 30 ngày tới) kèm tồn kho
$sql_expire = "SELECT t.ma_thuoc, t.ten_thuoc, t.han_su_dung, k.ton_kho
    FROM thuoc t
    LEFT JOIN kho k ON t.ma_kho = k.ma_kho
    WHERE t.han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY t.han_su_dung ASC";
    
$result_expire = $conn->query($sql_expire);
$sap_het_han = $result_expire ? $result_expire->num_rows : 0;


?>

<div class="table-box">
    <h2 style="text-align:center">Danh sách thuốc sắp hết hạn (dưới 30 ngày)</h2>
    <table border="0" width="100%" cellspacing="0" cellpadding="8">
        <thead>
            <tr>
                <th>Mã thuốc</th>
                <th>Tên thuốc</th>
                <th>Hạn sử dụng</th>
                <th>Số lượng tồn</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($sap_het_han > 0): ?>
                <?php while ($thuoc = $result_expire->fetch_assoc()): ?>
                    <tr>
                        <td><?= $thuoc['ma_thuoc'] ?></td>
                        <td><?= $thuoc['ten_thuoc'] ?></td>
                        <td style="color:red;">
                            <?= date("d-m-Y", strtotime($thuoc['han_su_dung'])) ?>
                        </td>
                        <td><?= $thuoc['ton_kho'] ?? 0 ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Không có thuốc nào sắp hết hạn
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
