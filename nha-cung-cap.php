<?php
include("connect.php");

// Tạo mã tự động
function taoMaNCC($conn){
    // Lấy số lớn nhất từ phần số của mã NCC
    $sql = "SELECT MAX(CAST(SUBSTRING(ma_nha_cung_cap, 4) AS UNSIGNED)) as max_id FROM nhacungcap";
    $rs = $conn->query($sql);
    $num = 1;
    if($r = $rs->fetch_assoc()){
        if($r['max_id'] !== null){
            $num = $r['max_id'] + 1;
        }
    }
    return "NCC" . str_pad($num, 3, "0", STR_PAD_LEFT);
}

// thêm
if (isset($_POST['action']) && $_POST['action'] === 'them') {
    $ma = taoMaNCC($conn);
    $stmt = $conn->prepare("INSERT INTO nhacungcap (ma_nha_cung_cap, ten_nha_cung_cap, dia_chi, dien_thoai, ghi_chu) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $ma, $_POST['ten'], $_POST['dia_chi'], $_POST['dien_thoai'], $_POST['ghi_chu']);
    $stmt->execute();
    header("Location: quanly.php?page=nha-cung-cap");
    exit;
}

// Sửa
if (isset($_POST['action']) && $_POST['action'] === 'sua') {
    $stmt = $conn->prepare("UPDATE nhacungcap SET ten_nha_cung_cap=?, dia_chi=?, dien_thoai=?, ghi_chu=? WHERE ma_nha_cung_cap=?");
    $stmt->bind_param("sssss", $_POST['ten'], $_POST['dia_chi'], $_POST['dien_thoai'], $_POST['ghi_chu'], $_POST['ma_nha_cung_cap']);
    $stmt->execute();
    header("Location: quanly.php?page=nha-cung-cap");
    exit;
}

// Xoá
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM nhacungcap WHERE ma_nha_cung_cap=?");
    $stmt->bind_param("s", $_GET['delete']);
    $stmt->execute();
    header("Location: quanly.php?page=nha-cung-cap");
    exit;
}

// Lấy danh sách
$list = $conn->query("SELECT * FROM nhacungcap ORDER BY ma_nha_cung_cap ASC");
?>

<div class="page-header">
    <h1>Nhà cung cấp</h1>
    <p>Quản lý thông tin đối tác cung ứng dược phẩm</p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm theo mã, tên, điện thoại..." onkeyup="timKiemNCC()">
    <button type="button" onclick="openModal('modal-them')">+</button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã NCC</th>
                <th>Tên nhà cung cấp</th>
                <th>Địa chỉ</th>
                <th>Điện thoại</th>
                <th>Ghi chú</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="danh-sach">
        <?php while($r = $list->fetch_assoc()) { ?>
            <tr>
                <td><strong><?= $r['ma_nha_cung_cap'] ?></strong></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['ten_nha_cung_cap']) ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['dia_chi']) ?></td>
                <td><?= htmlspecialchars($r['dien_thoai']) ?></td>
                <td><?= htmlspecialchars($r['ghi_chu'] ?? 'Chưa có ghi chú') ?></td>
                <td>
                    <button class="btn btn-info" onclick="editNCC('<?= $r['ma_nha_cung_cap'] ?>',
                    '<?= htmlspecialchars($r['ten_nha_cung_cap'], ENT_QUOTES) ?>',
                    '<?= htmlspecialchars($r['dia_chi'], ENT_QUOTES) ?>',
                    '<?= $r['dien_thoai'] ?>',
                    '<?= htmlspecialchars($r['ghi_chu'], ENT_QUOTES) ?>')">Sửa</button>
                    <a class="btn btn-danger" href="quanly.php?page=nha-cung-cap&delete=<?= $r['ma_nha_cung_cap'] ?>" onclick="return confirm('Xóa nhà cung cấp này?')">Xóa</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div id="modal-them" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-them')">&times;</span>
        <h2>Thêm nhà cung cấp mới</h2>
        <form method="POST">
            <input type="hidden" name="action" value="them">
            <div class="form-group">
                <label>Tên nhà cung cấp</label>
                <input name="ten" required placeholder="Nhập tên công ty/nhà cung cấp">
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <input name="dia_chi" required placeholder="Số nhà, đường, tỉnh/thành">
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input name="dien_thoai" required placeholder="Ví dụ: 0901234567">
            </div>
            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="ghi_chu" required placeholder="Ví dụ: Kháng sinh, thực phẩm chức năng..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('modal-them')">Hủy</button>
                <button type="submit" class="btn btn-info">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-sua" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-sua')">&times;</span>
        <h2>Cập nhật thông tin</h2>
        <form method="POST">
            <input type="hidden" name="action" value="sua">
            <input type="hidden" name="ma_nha_cung_cap" id="e-ma">
            <div class="form-group">
                <label>Tên nhà cung cấp</label>
                <input name="ten" id="e-ten" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <input name="dia_chi" id="e-diachi" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input name="dien_thoai" id="e-dt" required>
            </div>
            <div class="form-group">
                <label>Ghi chú</label>
                <textarea name="ghi_chu" id="e-note" required></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('modal-sua')">Hủy</button>
                <button type="submit" class="btn btn-info">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display="block"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

function editNCC(ma,ten,dc,dt,gc){
    document.getElementById('e-ma').value = ma;
    document.getElementById('e-ten').value = ten;
    document.getElementById('e-diachi').value = dc;
    document.getElementById('e-dt').value = dt;
    document.getElementById('e-note').value = gc;
    openModal('modal-sua');
}

function timKiemNCC(){
    let k = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#danh-sach tr").forEach(r=>{
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
