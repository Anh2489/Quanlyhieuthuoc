<?php
include("connect.php");

// Lấy danh sách sản phẩm (sp) sắp hết hạn (<= 30 ngày tới) kèm tồn kho
$sql_expire = "
    SELECT 
        s.ma_sp, 
        s.ten_sp, 
        s.han_su_dung, 
        k.ton_kho,
        DATEDIFF(s.han_su_dung, CURDATE()) as so_ngay
    FROM sp s
    LEFT JOIN kho k ON s.ma_kho = k.ma_kho
    WHERE s.han_su_dung <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY s.han_su_dung ASC
";
    
$result_expire = $conn->query($sql_expire);
?>

<div class="table-box">
    <h2 style="text-align:center">Cảnh báo thuốc sắp hết hạn (30 ngày)</h2>
    <table width="100%" cellspacing="0" cellpadding="8">
        <thead>
            <tr>
                <th>Mã thuốc</th>
                <th>Tên thuốc</th>
                <th>Hạn sử dụng</th>
                <th>Còn lại</th>
                <th>Tồn</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_expire && $result_expire->num_rows > 0): ?>
                <?php while ($r = $result_expire->fetch_assoc()): ?>
                    <tr>
                        <td><?= $r['ma_sp'] ?></td>
                        <td><?= $r['ten_sp'] ?></td>
                        <td style="color:red; font-weight:bold;">
                            <?= date("d/m/Y", strtotime($r['han_su_dung'])) ?>
                        </td>
                        <td><?= $r['so_ngay'] ?> ngày</td>
                        <td><b><?= $r['ton_kho'] ?? 0 ?></b></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Không có thuốc nào sắp hết hạn</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
