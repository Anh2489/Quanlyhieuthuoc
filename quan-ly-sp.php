<?php
include("connect.php");

// Hàm tạo mã thuốc tự động (Dựa trên SQL mới)
function taoMaSp($conn){
    $sql = "SELECT ma_sp FROM sp ORDER BY ma_sp DESC LIMIT 1";
    $result = $conn->query($sql);
    if($row = $result->fetch_assoc()){
        $last_id = intval(substr($row['ma_sp'], 1)) + 1;
    } else {
        $last_id = 1;
    }
    return "T" . str_pad($last_id, 3, "0", STR_PAD_LEFT);
}

// Lấy danh mục 
$dm_list = $conn->query("SELECT * FROM danh_muc ORDER BY ma_danh_muc ASC");

// Lấy nhà cung cấp
$ncc_list = $conn->query("SELECT * FROM nhacungcap ORDER BY ma_nha_cung_cap ASC");

// Lấy danh sách sản phẩm kèm Giá nhập và NCC gần nhất
$sp_list = $conn->query("
    SELECT sp.*, dm.ten_danh_muc, ncc.ten_nha_cung_cap as ncc_goc,
           pn.gia_nhap as gia_nhap_gan_nhat,
           ncc_pn.ten_nha_cung_cap as ncc_nhap_gan_nhat
    FROM sp
    LEFT JOIN danh_muc dm ON sp.ma_danh_muc = dm.ma_danh_muc
    LEFT JOIN nhacungcap ncc ON sp.nha_san_xuat = ncc.ma_nha_cung_cap
    LEFT JOIN phieunhap pn ON pn.so_pn = (
        SELECT so_pn FROM phieunhap 
        WHERE ma_sp = sp.ma_sp 
        ORDER BY ngay_nhap DESC LIMIT 1
    )
    LEFT JOIN nhacungcap ncc_pn ON pn.ma_nha_cung_cap = ncc_pn.ma_nha_cung_cap
    ORDER BY sp.ma_sp ASC
");

// Kiểm tra nếu SQL bị lỗi thì dừng lại và hiện thông báo lỗi để debug
if (!$sp_list) {
    die("Lỗi SQL: " . $conn->error);
}

// Xử lý thêm 
if (isset($_POST['action']) && $_POST['action'] === 'them') {
    $ma_sp = taoMaSp($conn);
    $stmt = $conn->prepare("INSERT INTO sp (ma_sp, ten_sp, ma_danh_muc, nha_san_xuat, gia_ban, han_su_dung, hoat_chat, don_vi_tinh) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsss", $ma_sp, $_POST['ten_sp'], $_POST['ma_danh_muc'], 
                      $_POST['nha_san_xuat'], $_POST['gia_ban'], $_POST['han_su_dung'], 
                      $_POST['hoat_chat'], $_POST['don_vi_tinh']);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-sp");
    exit;
}

// Xử lý sửa 
if (isset($_POST['action']) && $_POST['action'] === 'sua') {
    $stmt = $conn->prepare("UPDATE sp 
                               SET ten_sp=?, ma_danh_muc=?, nha_san_xuat=?, gia_ban=?, han_su_dung=?, hoat_chat=?, don_vi_tinh=? 
                             WHERE ma_sp=?");
    $stmt->bind_param("sssdssss", $_POST['ten_sp'], $_POST['ma_danh_muc'], $_POST['nha_san_xuat'], 
                      $_POST['gia_ban'], $_POST['han_su_dung'], $_POST['hoat_chat'], $_POST['don_vi_tinh'], $_POST['ma_sp']);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-sp");
    exit;
}

//Xử lý xóa 
if (isset($_GET['delete'])) {
    $ma_sp = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM sp WHERE ma_sp=?");
    $stmt->bind_param("s", $ma_sp);
    $stmt->execute();
    header("Location: quanly.php?page=quan-ly-sp");
    exit;
}
?>

<div class="page-header">
    <h1>Sản phẩm</h1>
    <p>Quản lý danh sách thuốc và dược phẩm</p>
</div>

<div class="toolbar">
    <input type="text" id="searchInput" placeholder="Tìm kiếm theo tên, mã sản phẩm..." onkeyup="timKiemsp()">
    <button type="button" onclick="openModal('modal-them')">+</button>
</div>

<div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Mã SP</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Nhà cung cấp</th>
                <th>Đơn vị</th>
                <th>Giá nhập</th>
                <th>Giá bán</th>
                <th>Hạn dùng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody id="danh-sach">
            <?php while($sp = $sp_list->fetch_assoc()) { ?>
            <tr>
                <td><strong><?= $sp['ma_sp'] ?></strong></td>
                <td><?= $sp['ten_sp'] ?></td>
                <td><?= $sp['ten_danh_muc'] ?></td>
                <td><?= $sp['ncc_goc'] ?></td>
                <td><?= $sp['don_vi_tinh'] ?></td>
                <td>
                    <?php if($sp['gia_nhap_gan_nhat']): ?>
                    <?= number_format($sp['gia_nhap_gan_nhat'],0,',','.') ?> đ 
                    <br><small>(<?= $sp['ncc_nhap_gan_nhat'] ?>)</small>
                    <?php else: ?>
                    <i>Chưa nhập hàng</i>
                    <?php endif; ?>
                </td>
                <td><?= number_format($sp['gia_ban'],0,',','.') ?> đ</td>
                <td><?= date("d/m/Y", strtotime($sp['han_su_dung'])) ?></td>
                <td>
                    <button class="btn btn-info" 
                        onclick="editSp('<?= $sp['ma_sp'] ?>','<?= addslashes($sp['ten_sp']) ?>',
                        '<?= $sp['ma_danh_muc'] ?>','<?= $sp['nha_san_xuat'] ?>',
                        '<?= $sp['don_vi_tinh'] ?>','<?= $sp['gia_ban'] ?>',
                        '<?= $sp['han_su_dung'] ?>','<?= $sp['hoat_chat'] ?>',
                        '<?= $sp['gia_nhap_gan_nhat'] ?>','<?= addslashes($sp['ncc_nhap_gan_nhat'])?>')">Sửa
                    </button>
                    <a class="btn btn-danger" href="quanly.php?page=quan-ly-sp&delete=<?= $sp['ma_sp'] ?>" onclick="return confirm('Xóa sản phẩm này?')">Xóa</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div id="modal-them" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-them')">&times;</span>
        <h2>Thêm sản phẩm mới</h2>
        <form method="POST">
            <input type="hidden" name="action" value="them">
            <div class="form-group">
                <label>Tên sản phẩm</label>
                <input name="ten_sp" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc" required>
                        <?php 
                        $dm_list->data_seek(0);
                        while($dm = $dm_list->fetch_assoc()){ echo "<option value='{$dm['ma_danh_muc']}'>{$dm['ten_danh_muc']}</option>"; } 
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nhà cung cấp</label>
                    <select name="nha_san_xuat" required>
                        <?php 
                        $ncc_list->data_seek(0);
                        while($ncc = $ncc_list->fetch_assoc()){ echo "<option value='{$ncc['ma_nha_cung_cap']}'>{$ncc['ten_nha_cung_cap']}</option>"; } 
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Đơn vị tính</label>
                    <input name="don_vi_tinh" placeholder="Viên, Chai, Vỉ..." required>
                </div>
                <div class="form-group">
                    <label>Giá bán</label>
                    <input type="number" name="gia_ban" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Hạn sử dụng</label>
                    <input type="date" name="han_su_dung" required>
                </div>
                <div class="form-group">
                    <label>Hoạt chất</label>
                    <input name="hoat_chat" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn" onclick="closeModal('modal-them')">Hủy</button>
                <button type="submit" class="btn btn-info">Lưu sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-sua" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('modal-sua')">&times;</span>
        <h2>Cập nhật sản phẩm</h2>
        <form method="POST">
            <input type="hidden" name="action" value="sua">
            <input type="hidden" name="ma_sp" id="e-ma">
            <div class="form-group">
                <label>Tên sản phẩm</label>
                <input name="ten_sp" id="e-ten" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc" id="e-dm">
                        <?php 
                        $dm_list->data_seek(0);
                        while($dm = $dm_list->fetch_assoc()){ echo "<option value='{$dm['ma_danh_muc']}'>{$dm['ten_danh_muc']}</option>"; } 
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nhà cung cấp</label>
                    <select name="nha_san_xuat" id="e-nsx">
                        <?php 
                        $ncc_list->data_seek(0);
                        while($ncc = $ncc_list->fetch_assoc()){ echo "<option value='{$ncc['ma_nha_cung_cap']}'>{$ncc['ten_nha_cung_cap']}</option>"; } 
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Đơn vị</label>
                    <input name="don_vi_tinh" id="e-dvt">
                </div>
                <div class="form-group">
                    <label>Giá bán</label>
                    <div id="info-gia-nhap-cu" style="font-size: 12px; color: #d32f2f; margin-bottom: 5px; font-weight: bold;"></div>
                    <input type="number" name="gia_ban" id="e-gia">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Hạn sử dụng</label>
                    <input type="date" name="han_su_dung" id="e-hsd">
                </div>
                <div class="form-group">
                    <label>Hoạt chất</label>
                    <input name="hoat_chat" id="e-hc">
                </div>
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

function editSp(ma,ten,dm,nsx,dvt,gia,hsd,hc,gia_nhap,ncc_nhap){
    document.getElementById('e-ma').value=ma; 
    document.getElementById('e-ten').value=ten; 
    document.getElementById('e-dm').value=dm;
    document.getElementById('e-nsx').value=nsx; 
    document.getElementById('e-dvt').value=dvt;
    document.getElementById('e-gia').value=gia; 
    document.getElementById('e-hsd').value=hsd; 
    document.getElementById('e-hc').value=hc;
    // Hiển thị giá nhập để tham khảo khi set giá bán
    let infoDiv = document.getElementById('info-gia-nhap-cu');
    if (gia_nhap && gia_nhap !== '') {
        // Định dạng tiền tệ VNĐ
        let formatGia = new Intl.NumberFormat('vi-VN').format(gia_nhap);
        infoDiv.innerHTML = `Nhập gần nhất: ${formatGia} đ (${ncc_nhap})`;
    } else {
        infoDiv.innerHTML = "Chưa có lịch sử nhập hàng";
    }
    openModal('modal-sua');
}

function timKiemsp(){
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