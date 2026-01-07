<?php
include("connect.php");
mysqli_set_charset($conn, "utf8");

// lây danh sách thuốc
$thuoc = [];
$rs = $conn->query("
    SELECT sp.ma_sp, sp.ten_sp, sp.gia_ban, k.ton_kho
    FROM sp
    JOIN kho k ON sp.ma_kho = k.ma_kho
    WHERE k.ton_kho > 0
    ORDER BY sp.ten_sp
");
while($r = $rs->fetch_assoc()){
    $thuoc[] = $r;
}

// Lấy kh 
$ds_khach = [];
$rs_kh = $conn->query("SELECT ma_khach, ten_khach_hang FROM khachhang");
while($row_kh = $rs_kh->fetch_assoc()){
    $ds_khach[] = $row_kh;
}

// Lưu hoá đơn
if(isset($_POST['action']) && $_POST['action']=='luu'){
    $so_hd = 'HD'.time();
    $pttt = $_POST['pttt'];
    $tong = $_POST['tong_tien']; 
    $ma_khach = $_POST['ma_khach'];

    $conn->begin_transaction(); 

    try {
        $sql_hd = "INSERT INTO hoa_don(so_hd, ngay_ban, ma_khach, phuong_thuc_thanh_toan, tong_tien_cuoi, trang_thai)
                   VALUES('$so_hd', CURDATE(), '$ma_khach', '$pttt', $tong, 'Đã thanh toán')";
        
        if(!$conn->query($sql_hd)) throw new Exception("Lỗi lưu hóa đơn: " . $conn->error);

        foreach($_POST['items'] as $it){
            $ma = $it['ma']; 
            $sl = (int)$it['sl']; 
            $gia = (float)$it['gia']; 
            $tt = $sl * $gia;

            $sql_ct = "INSERT INTO hoa_don_chi_tiet(so_hd, ma_sp, so_luong, gia_ban, thanh_tien)
                       VALUES('$so_hd', '$ma', $sl, $gia, $tt)";
            
            if(!$conn->query($sql_ct)) throw new Exception("Lỗi lưu chi tiết: " . $conn->error);
        }

        $conn->commit();
        echo "<script>alert('Đã lưu hóa đơn thành công!'); window.location.href='quanly.php?page=ban-hang';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Có lỗi xảy ra: " . $e->getMessage() . "');</script>";
    }
}


?>

<div class="page-header" style="margin-bottom: 0;">
    <h1>Bán hàng</h1>
</div>

<div class="pos-container">
    <div class="bh-left">
        <div class="search-box">
            <input type="text" id="search" placeholder="Tìm tên thuốc hoặc mã sản phẩm...">
        </div>

        <div class="product-grid" id="thuocList">
            <?php foreach($thuoc as $t): ?>
            <div class="p-card" onclick="addItem('<?= $t['ma_sp'] ?>', '<?= addslashes($t['ten_sp']) ?>', <?= $t['gia_ban'] ?>, <?= $t['ton_kho'] ?>)">
                <div class="p-name"><?= $t['ten_sp'] ?></div>
                <div class="p-price"><?= number_format($t['gia_ban'], 0, ',', '.') ?> đ</div>
                <div class="p-stock">Tồn kho: <b><?= $t['ton_kho'] ?></b></div>
            </div>
            <?php endforeach ?>
        </div>
    </div>

    <div class="bh-right">
        <div class="cart-header">
            <strong>Giỏ hàng</strong>
            <select name="ma_khach" form="formBan" class="cart-select" style="margin-right: 5px;">
               <?php foreach($ds_khach as $kh): ?>
                   <option value="<?= $kh['ma_khach'] ?>"><?= $kh['ten_khach_hang'] ?></option>
               <?php endforeach; ?>
            </select>
    
            <select name="pttt" form="formBan" class="cart-select">
                <option>Tiền mặt</option>
                <option>Chuyển khoản</option>
                <option>Thẻ (POS)</option>
            </select>
        </div>

        <div class="cart-body">
            <form method="post" id="formBan">
                <input type="hidden" name="action" value="luu">
                <input type="hidden" name="tong_tien" id="tongTienInput">

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th width="50">SL</th>
                            <th width="80" style="text-align:right">Tổng</th>
                            <th width="30"></th>
                        </tr>
                    </thead>
                    <tbody id="hoaDon">
                        </tbody>
                </table>
            </form>
        </div>

        <div class="cart-footer">
            <div class="total-row">
                <span>Tổng cộng:</span>
                <span id="tongTien" style="color: #2563eb;">0 đ</span>
            </div>
            <button type="button" onclick="confirmSave()" class="btn-pay">
                THANH TOÁN
            </button>
        </div>
    </div>
</div>

<script>
let items = {};

function addItem(ma, ten, gia, ton){
    if(items[ma]){
        if(items[ma].sl >= ton){
            alert('Số lượng trong kho không đủ!');
            return;
        }
        items[ma].sl++;
    }else{
        items[ma] = {ma, ten, gia, sl: 1};
    }
    render();
}

function render(){
    let html = '';
    let tong = 0;
    let i = 0;

    for(let k in items){
        let it = items[k];
        let tt = it.sl * it.gia;
        tong += tt;

        html += `
        <tr>
            <td>
                <div style="font-weight:600;">${it.ten}</div>
                <div style="font-size:12px; color:#94a3b8;">${it.gia.toLocaleString()} đ</div>
            </td>
            <td>
                <input type="number" class="qty-input" min="1" value="${it.sl}"
                    onchange="changeSL('${k}', this.value)">
            </td>
            <td align="right" style="font-weight:600;">${tt.toLocaleString()} đ</td>
            <td align="right">
                <button type="button" class="btn-del" onclick="del('${k}')">&times;</button>
            </td>

            <input type="hidden" name="items[${i}][ma]" value="${it.ma}">
            <input type="hidden" name="items[${i}][ten]" value="${it.ten}">
            <input type="hidden" name="items[${i}][sl]" value="${it.sl}">
            <input type="hidden" name="items[${i}][gia]" value="${it.gia}">
        </tr>`;
        i++;
    }

    document.getElementById("hoaDon").innerHTML = html || '<tr><td colspan="4" align="center" style="padding:30px; color:#94a3b8;">Giỏ hàng trống</td></tr>';
    document.getElementById("tongTien").innerText = tong.toLocaleString() + ' đ';
    document.getElementById("tongTienInput").value = tong;
}

function changeSL(ma, sl){
    if(sl < 1) sl = 1;
    items[ma].sl = parseInt(sl);
    render();
}

function del(ma){
    delete items[ma];
    render();
}

function confirmSave(){
    if(Object.keys(items).length === 0){
        alert('Vui lòng chọn ít nhất một sản phẩm!');
        return;
    }
    if(confirm('Xác nhận thanh toán và lưu hóa đơn?')){
        document.getElementById('formBan').submit();
    }
}

document.getElementById("search").onkeyup = function(){
    let k = this.value.toLowerCase();
    document.querySelectorAll(".p-card").forEach(i=>{
        i.style.display = i.innerText.toLowerCase().includes(k) ? '' : 'none';
    });
}

</script>
