<?php
include("connect.php");

// Xử lý xoá kho
if(isset($_GET['delete'])){
    $ma_kho = $_GET['delete'];
    
    // Cập nhật các sản phẩm đang trỏ về kho này thành NULL trước khi xóa kho
    $stmt1 = $conn->prepare("UPDATE sp SET ma_kho = NULL WHERE ma_kho = ?");
    $stmt1->bind_param("s", $ma_kho);
    $stmt1->execute();

    $stmt2 = $conn->prepare("DELETE FROM kho WHERE ma_kho = ?");
    $stmt2->bind_param("s", $ma_kho);
    $stmt2->execute();

    header("Location: quanly.php?page=quan-ly-kho");
    exit;
}
// Sửa kho
if(isset($_POST['action']) && $_POST['action'] == 'sua'){
    $ma = $_POST['ma_kho'];
    $slNhap = (int)$_POST['sl_nhap'];
    $slGiao = (int)$_POST['sl_giao'];
    $stmt = $conn->prepare("UPDATE kho SET sl_nhap = ?, sl_giao = ? WHERE ma_kho = ?");
    $stmt->bind_param("iis", $slNhap, $slGiao, $ma);
    $stmt->execute();

    header("Location: quanly.php?page=quan-ly-kho");
    exit;
}
// Lấy danh sách tồn kho theo từng Sản phẩm
$list = $conn->query("
    SELECT 
        sp.ma_sp, 
        sp.ten_sp, 
        k.ma_kho, 
        IFNULL(k.sl_nhap, 0) as sl_nhap, 
        IFNULL(k.sl_giao, 0) as sl_giao, 
        IFNULL(k.ton_kho, 0) as ton_kho
    FROM sp
    LEFT JOIN kho k ON sp.ma_kho = k.ma_kho
    ORDER BY sp.ma_sp ASC
");
?>

<div class="page-header">
    <h1>Quản lý kho</h1>
    <p>Theo dõi số lượng nhập, xuất và tồn kho thực tế</p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm theo mã kho, tên sản phẩm..." onkeyup="timKiem()">
    </div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã Sản phẩm</th>
                <th>Tên sản phẩm</th>
                <th>Tổng nhập</th>
                <th>Tổng xuất</th>
                <th>Tồn kho</th>
                <th>Cảnh báo</th>
            </tr>
        </thead>
        <tbody id="danh-sach">
            <?php while($r = $list->fetch_assoc()): ?>
            <tr>
                <td><?= $r['ma_sp'] ?></td>
                <td style="text-align: left; font-weight: bold;"><?= $r['ten_sp'] ?></td>
                <td><?= number_format($r['sl_nhap']) ?></td>
                <td><?= number_format($r['sl_giao']) ?></td>
                <td>
                    <b style="color: <?= $r['ton_kho'] <= 5 ? '#ef4444' : '#10b981' ?>;">
                       <?= number_format($r['ton_kho']) ?>
                    </b>
                </td>
                <td>
                    <?php if($r['ton_kho'] <= 5): ?>
                        <span style="color: #ef4444; font-size: 12px;">Sắp hết hàng</span>
                    <?php else: ?>
                        <span style="color: #10b981; font-size: 12px;">An toàn</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<script>
function openModal(id){ document.getElementById(id).style.display="block"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

function editKho(ma, nhap, giao){
    document.getElementById('e-ma').value = ma;
    document.getElementById('e-nhap').value = nhap;
    document.getElementById('e-giao').value = giao;
    document.getElementById('e-ton').value = parseInt(nhap) - parseInt(giao);
    openModal('modal-sua');
}

// Lắng nghe sự kiện nhập liệu để tính tồn kho ngay lập tức trên giao diện
['e-nhap','e-giao'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        let n = parseInt(document.getElementById('e-nhap').value) || 0;
        let g = parseInt(document.getElementById('e-giao').value) || 0;
        document.getElementById('e-ton').value = n - g;
    });
});

function timKiem(){
    let k = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#danh-sach tr").forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(k) ? "" : "none";
    });
}

// Đóng modal khi click ra ngoài
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = "none";
    }
}
</script>
