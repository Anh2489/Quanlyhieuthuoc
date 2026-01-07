<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
<?php
include("connect.php");

// Tạo mã tự động
function taoMaKH($conn){
    $rs = $conn->query("SELECT ma_khach FROM khachhang ORDER BY ma_khach DESC LIMIT 1");
    if($r = $rs->fetch_assoc()){
        $num = intval(substr($r['ma_khach'], 2)) + 1;
    } else {
        $num = 1;
    }
    return "KH" . str_pad($num, 3, "0", STR_PAD_LEFT);
}

// Thêm
if (isset($_POST['action']) && $_POST['action'] === 'them') {
    $ma = taoMaKH($conn);
    $stmt = $conn->prepare("INSERT INTO khachhang (ma_khach, ten_khach_hang, dia_chi, dien_thoai) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $ma, $_POST['ten'], $_POST['dia_chi'], $_POST['dien_thoai']);
    $stmt->execute();
    header("Location: quanly.php?page=khach-hang");
    exit;
}

// Sửa
if (isset($_POST['action']) && $_POST['action'] === 'sua') {
    $stmt = $conn->prepare("UPDATE khachhang SET ten_khach_hang=?, dia_chi=?, dien_thoai=? WHERE ma_khach=?");
    $stmt->bind_param("ssss", $_POST['ten'], $_POST['dia_chi'], $_POST['dien_thoai'], $_POST['ma_khach']);
    $stmt->execute();
    header("Location: quanly.php?page=khach-hang");
    exit;
}

// Xoá
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM khachhang WHERE ma_khach=?");
    $stmt->bind_param("s", $_GET['delete']);
    $stmt->execute();
    header("Location: quanly.php?page=khach-hang");
    exit;
}

// Danh sách
$list = $conn->query("SELECT * FROM khachhang ORDER BY ma_khach ASC");
?>

<div class="page-header">
    <h1>Khách hàng</h1>
    <p>Quản lý thông tin khách hàng thân thiết</p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm theo mã, tên, điện thoại..." onkeyup="timKiemKH()">
    <button type="button" onclick="openModal('modal-them')">+</button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã KH</th>
                <th>Tên khách hàng</th>
                <th>Địa chỉ</th>
                <th>Điện thoại</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="danh-sach">
        <?php while($r = $list->fetch_assoc()) { ?>
            <tr>
                <td><strong><?= $r['ma_khach'] ?></strong></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['ten_khach_hang']) ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($r['dia_chi']) ?></td>
                <td><?= htmlspecialchars($r['dien_thoai']) ?></td>
                <td>
                    <button class="btn btn-info" onclick="editKH('<?= $r['ma_khach'] ?>','<?= htmlspecialchars($r['ten_khach_hang'], ENT_QUOTES) ?>','<?= htmlspecialchars($r['dia_chi'], ENT_QUOTES) ?>','<?= $r['dien_thoai'] ?>')">Sửa</button>
                    <a class="btn btn-danger" href="quanly.php?page=khach-hang&delete=<?= $r['ma_khach'] ?>" onclick="return confirm('Xóa khách hàng này?')">Xóa</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div id="modal-them" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-them')">&times;</span>
        <h2>Thêm khách hàng mới</h2>
        <form method="POST">
            <input type="hidden" name="action" value="them">
            <div class="form-group">
                <label>Tên khách hàng</label>
                <input name="ten" required placeholder="Nhập họ tên khách hàng">
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="dia_chi" rows="2" required placeholder="Địa chỉ thường trú"></textarea>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input name="dien_thoai" required placeholder="Số điện thoại liên hệ">
            </div>
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('modal-them')">Hủy</button>
                <button type="submit" class="btn btn-info">Lưu khách hàng</button>
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
            <input type="hidden" name="ma_khach" id="e-ma">
            <div class="form-group">
                <label>Tên khách hàng</label>
                <input name="ten" id="e-ten" required>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="dia_chi" id="e-diachi" rows="2" required></textarea>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input name="dien_thoai" id="e-dt" required>
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

function editKH(ma, ten, dc, dt){
    document.getElementById('e-ma').value = ma;
    document.getElementById('e-ten').value = ten;
    document.getElementById('e-diachi').value = dc;
    document.getElementById('e-dt').value = dt;
    openModal('modal-sua');
}

function timKiemKH(){
    let k = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#danh-sach tr").forEach(r => {
        r.style.display = r.innerText.toLowerCase().includes(k) ? "" : "none";
    });
}

window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = "none";
    }
}
</script>
</body>
</html>