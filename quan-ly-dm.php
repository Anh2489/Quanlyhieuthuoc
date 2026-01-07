<?php
include("connect.php");

/* ====== TẠO MÃ DANH MỤC TỰ ĐỘNG ====== */
function taoMaDM($conn){
    // Sắp xếp theo số đuôi của mã DM001, DM002...
    $sql = "SELECT ma_danh_muc 
            FROM danh_muc 
            ORDER BY CAST(SUBSTRING(ma_danh_muc,3) AS UNSIGNED) DESC 
            LIMIT 1";
    $rs = $conn->query($sql);
    if($row = $rs->fetch_assoc()){
        $num = (int)substr($row['ma_danh_muc'],2) + 1;
    }else{
        $num = 1;
    }
    return "DM".str_pad($num,3,"0",STR_PAD_LEFT);
}

/* ====== XỬ LÝ THÊM ====== */
if(isset($_POST['action']) && $_POST['action']=='them'){
    $ma = taoMaDM($conn);
    $ten = trim($_POST['ten_danh_muc']);
    $mota = trim($_POST['mo_ta']);

    $stmt = $conn->prepare("INSERT INTO danh_muc(ma_danh_muc,ten_danh_muc,mo_ta) VALUES(?,?,?)");
    $stmt->bind_param("sss", $ma, $ten, $mota);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-dm");
    exit;
}

/* ====== XỬ LÝ SỬA ====== */
if(isset($_POST['action']) && $_POST['action']=='sua'){
    $stmt = $conn->prepare("UPDATE danh_muc SET ten_danh_muc=?, mo_ta=? WHERE ma_danh_muc=?");
    $stmt->bind_param("sss", $_POST['ten_danh_muc'], $_POST['mo_ta'], $_POST['ma_danh_muc']);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-dm");
    exit;
}

/* ====== XỬ LÝ XÓA ====== */
if(isset($_GET['delete'])){
    // Lưu ý: SQL có ràng buộc khóa ngoại, nếu danh mục có thuốc thì sẽ không xóa được (tránh lỗi dữ liệu)
    $stmt = $conn->prepare("DELETE FROM danh_muc WHERE ma_danh_muc=?");
    $stmt->bind_param("s", $_GET['delete']);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-dm");
    exit;
}

/* ====== LẤY DANH SÁCH ====== */
$list = $conn->query("SELECT * FROM danh_muc ORDER BY CAST(SUBSTRING(ma_danh_muc,3) AS UNSIGNED) ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quản lý danh mục</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>

<div class="page-header">
    <h1>Danh mục</h1>
    <p>Quản lý các nhóm sản phẩm dược phẩm</p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm theo mã, tên, mô tả..." onkeyup="timKiemDM()">
    <button type="button" onclick="openModal('modal-them')">+</button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="ds-dm">
            <?php while($dm = $list->fetch_assoc()): ?>
            <tr>
                <td><strong><?= $dm['ma_danh_muc'] ?></strong></td>
                <td style="text-align: left;"><?= htmlspecialchars($dm['ten_danh_muc']) ?></td>
                <td style="text-align: left;"><?= htmlspecialchars($dm['mo_ta']) ?></td>
                <td>
                    <button class="btn btn-info" 
                        onclick="editDM('<?= $dm['ma_danh_muc'] ?>', '<?= htmlspecialchars($dm['ten_danh_muc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($dm['mo_ta'], ENT_QUOTES) ?>')">
                        Sửa
                    </button>
                    <a class="btn btn-danger" 
                       href="quanly.php?page=quan-ly-dm&delete=<?= $dm['ma_danh_muc'] ?>" 
                       onclick="return confirm('Xóa danh mục này có thể ảnh hưởng đến sản phẩm liên quan. Bạn chắc chắn chứ?')">
                       Xóa
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="modal-them" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-them')">&times;</span>
        <h2>Thêm danh mục mới</h2>
        <form method="post">
            <input type="hidden" name="action" value="them">
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="ten_danh_muc" placeholder="Ví dụ: Thuốc giảm đau" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="mo_ta" rows="3" placeholder="Nhập mô tả ngắn về danh mục này..."></textarea>
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
        <h2>Cập nhật danh mục</h2>
        <form method="post">
            <input type="hidden" name="action" value="sua">
            <input type="hidden" name="ma_danh_muc" id="e-ma">
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="ten_danh_muc" id="e-ten" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="mo_ta" id="e-mota" rows="3"></textarea>
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

function editDM(ma, ten, mota){
    document.getElementById('e-ma').value = ma;
    document.getElementById('e-ten').value = ten;
    document.getElementById('e-mota').value = mota;
    openModal('modal-sua');
}

function timKiemDM(){
    let k = document.getElementById("searchInput").value.toLowerCase();
    document.querySelectorAll("#ds-dm tr").forEach(r => {
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

</body>
</html>