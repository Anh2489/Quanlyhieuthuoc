<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý phiếu nhập</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<?php
include("connect.php");

// 1. Hàm tạo mã PN tự động khớp với kiểu varchar(10)
function taoSoPN($conn){
    $q = $conn->query("SELECT so_pn FROM phieunhap ORDER BY so_pn DESC LIMIT 1");
    if($q && $r = $q->fetch_assoc()){
        $num = intval(substr($r['so_pn'], 2)) + 1;
    } else {
        $num = 1;
    }
    return "PN" . str_pad($num, 3, "0", STR_PAD_LEFT);
}

// 2. Xử lý Thêm phiếu nhập
if(isset($_POST['action']) && $_POST['action'] === 'them'){
    $so_pn = taoSoPN($conn);
    $ma_sp = $_POST['ma_sp'];
    $ma_ncc = $_POST['ma_nha_cung_cap'];
    $sl = (int)$_POST['so_luong_nhap'];
    $gia = (float)$_POST['gia_nhap'];
    $tt = (int)($sl * $gia); // Kiểu int(11) khớp với CSDL

    // Lấy ma_kho từ bảng sản phẩm
    $res_sp = $conn->query("SELECT ma_kho FROM sp WHERE ma_sp = '$ma_sp'");
    $row_sp = $res_sp->fetch_assoc();
    $ma_kho = $row_sp['ma_kho']; 

    $sql = "INSERT INTO phieunhap (so_pn, ngay_nhap, ma_kho, ma_sp, so_luong_nhap, gia_nhap, thanh_tien_nhap, ma_nha_cung_cap) 
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssidis", $so_pn, $ma_kho, $ma_sp, $sl, $gia, $tt, $ma_ncc);
        $stmt->execute();
        echo "<script>window.location.href='quanly.php?page=phieu-nhap';</script>";
        exit;
    }
}

// 3. Xử lý Cập nhật (Sửa)
if(isset($_POST['action']) && $_POST['action'] === 'sua'){
    $so_pn = $_POST['so_pn'];
    $ma_sp = $_POST['ma_sp'];
    $ma_ncc = $_POST['ma_nha_cung_cap'];
    $sl = (int)$_POST['so_luong_nhap'];
    $gia = (float)$_POST['gia_nhap'];
    $tt = (int)($sl * $gia);

    $res_sp = $conn->query("SELECT ma_kho FROM sp WHERE ma_sp = '$ma_sp'");
    $row_sp = $res_sp->fetch_assoc();
    $ma_kho = $row_sp['ma_kho'];

    $sql = "UPDATE phieunhap SET ma_sp=?, ma_nha_cung_cap=?, ma_kho=?, so_luong_nhap=?, gia_nhap=?, thanh_tien_nhap=? WHERE so_pn=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssidis", $ma_sp, $ma_ncc, $ma_kho, $sl, $gia, $tt, $so_pn);
        $stmt->execute();
        echo "<script>window.location.href='quanly.php?page=phieu-nhap';</script>";
        exit;
    }
}

// 4. Lấy dữ liệu hiển thị (Dùng LEFT JOIN để luôn hiện dữ liệu)
$sp_list  = $conn->query("SELECT ma_sp, ten_sp FROM sp");
$ncc_list = $conn->query("SELECT ma_nha_cung_cap, ten_nha_cung_cap FROM nhacungcap");
$list = $conn->query("
    SELECT pn.*, sp.ten_sp, ncc.ten_nha_cung_cap
    FROM phieunhap pn
    LEFT JOIN sp ON pn.ma_sp = sp.ma_sp
    LEFT JOIN nhacungcap ncc ON pn.ma_nha_cung_cap = ncc.ma_nha_cung_cap
    ORDER BY pn.so_pn DESC
");
?>

<div class="page-header">
    <h1>Quản lý phiếu nhập hàng</h1>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm kiếm thuốc hoặc mã phiếu..." onkeyup="timKiem()">
    <button type="button" class="btn-add" onclick="openModal('modal-them')">+</button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã phiếu</th>
                <th>Sản phẩm</th>
                <th>Nhà cung cấp</th>
                <th>Số lượng</th>
                <th>Giá nhập</th>
                <th>Thành tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="danh-sach">
            <?php if($list && $list->num_rows > 0): ?>
                <?php while($r = $list->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= $r['so_pn'] ?></strong></td>
                    <td style="text-align: left;"><?= $r['ten_sp'] ?></td>
                    <td style="text-align: left;"><?= $r['ten_nha_cung_cap'] ?></td>
                    <td><?= number_format($r['so_luong_nhap']) ?></td>
                    <td><?= number_format($r['gia_nhap']) ?> đ</td>
                    <td><?= number_format($r['thanh_tien_nhap']) ?> đ</td>
                    <td>
                        <button class="btn btn-info" onclick="editPN('<?= $r['so_pn'] ?>', '<?= $r['ma_sp'] ?>', '<?= $r['ma_nha_cung_cap'] ?>', '<?= $r['so_luong_nhap'] ?>', '<?= $r['gia_nhap'] ?>')">Sửa</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; padding: 20px;">Không có dữ liệu hiển thị.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="modal-them" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-them')">&times;</span>
        <h2>Tạo phiếu nhập mới</h2>
        <form method="post">
            <input type="hidden" name="action" value="them">
            <div class="form-group">
                <label>Sản phẩm</label>
                <select name="ma_sp" required>
                    <?php $sp_list->data_seek(0); while($s = $sp_list->fetch_assoc()): ?>
                        <option value="<?= $s['ma_sp'] ?>"><?= $s['ten_sp'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nhà cung cấp</label>
                <select name="ma_nha_cung_cap" required>
                    <?php $ncc_list->data_seek(0); while($n = $ncc_list->fetch_assoc()): ?>
                        <option value="<?= $n['ma_nha_cung_cap'] ?>"><?= $n['ten_nha_cung_cap'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Số lượng</label><input type="number" name="so_luong_nhap" required></div>
                <div class="form-group"><label>Giá nhập</label><input type="number" name="gia_nhap" required></div>
            </div>
            <button type="submit" class="btn btn-info">Lưu phiếu</button>
        </form>
    </div>
</div>

<div id="modal-sua" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-sua')">&times;</span>
        <h2>Sửa phiếu: <span id="txt-so"></span></h2>
        <form method="post">
            <input type="hidden" name="action" value="sua">
            <input type="hidden" name="so_pn" id="e-so">
            <div class="form-group">
                <label>Sản phẩm</label>
                <select name="ma_sp" id="e-sp">
                    <?php $sp_list->data_seek(0); while($s = $sp_list->fetch_assoc()): ?>
                        <option value="<?= $s['ma_sp'] ?>"><?= $s['ten_sp'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nhà cung cấp</label>
                <select name="ma_nha_cung_cap" id="e-ncc">
                    <?php $ncc_list->data_seek(0); while($n = $ncc_list->fetch_assoc()): ?>
                        <option value="<?= $n['ma_nha_cung_cap'] ?>"><?= $n['ten_nha_cung_cap'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Số lượng</label><input type="number" name="so_luong_nhap" id="e-sl"></div>
                <div class="form-group"><label>Giá nhập</label><input type="number" name="gia_nhap" id="e-gia"></div>
            </div>
            <button type="submit" class="btn btn-info">Cập nhật</button>
        </form>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).style.display="block"; }
function closeModal(id){ document.getElementById(id).style.display="none"; }

// Fix lỗi tìm kiếm: lọc dữ liệu trên toàn bộ bảng
function timKiem() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toLowerCase();
    var rows = document.querySelectorAll("#danh-sach tr");
    rows.forEach(row => {
        var text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
}

// Điền dữ liệu vào Modal Sửa
function editPN(so, sp, ncc, sl, gia){
    document.getElementById('e-so').value = so;
    document.getElementById('txt-so').innerText = so;
    document.getElementById('e-sp').value = sp;
    document.getElementById('e-ncc').value = ncc;
    document.getElementById('e-sl').value = sl;
    document.getElementById('e-gia').value = gia;
    openModal('modal-sua');
}
</script>
</body>
</html>