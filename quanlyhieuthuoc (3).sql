-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 07, 2026 lúc 06:28 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlyhieuthuoc`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `ma_danh_muc` varchar(10) NOT NULL,
  `ten_danh_muc` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`ma_danh_muc`, `ten_danh_muc`, `mo_ta`, `created_at`, `updated_at`) VALUES
('DM001', 'Thực phẩm chức năng', 'Sản phẩm bổ sung sức khỏe', '2026-01-05 14:52:48', '2026-01-05 14:52:48'),
('DM002', 'Thuốc Giảm Đau - Hạ Sốt', 'Thuốc bán theo đơn bác sĩ', '2026-01-05 14:52:48', '2026-01-05 16:39:11'),
('DM003', 'Trang thiết bị y tế', 'Các thiết bị y tế như máy móc, dụng cụ y tế, thiết bị hỗ trợ điều trị.', '2026-01-05 16:34:57', '2026-01-05 16:34:57'),
('DM004', 'Thuốc Kháng Sinh', 'Dược phẩm dùng để tiêu diệt hoặc ức chế sự phát triển của vi khuẩn gây bệnh', '2026-01-05 16:40:29', '2026-01-05 16:40:29'),
('DM005', 'Chăm sóc da', '', '2026-01-06 22:51:54', '2026-01-06 22:51:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don`
--

CREATE TABLE `hoa_don` (
  `so_hd` varchar(10) NOT NULL,
  `ngay_ban` date DEFAULT NULL,
  `ma_khach` varchar(10) DEFAULT NULL,
  `phuong_thuc_thanh_toan` enum('Tiền mặt','Chuyển khoản','Thẻ') DEFAULT 'Tiền mặt',
  `tong_tien_cuoi` decimal(15,2) DEFAULT NULL,
  `trang_thai` enum('Chưa thanh toán','Đã thanh toán') DEFAULT 'Đã thanh toán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoa_don`
--

INSERT INTO `hoa_don` (`so_hd`, `ngay_ban`, `ma_khach`, `phuong_thuc_thanh_toan`, `tong_tien_cuoi`, `trang_thai`) VALUES
('HD17677568', '2026-01-07', 'KH001', 'Tiền mặt', 1500.00, 'Đã thanh toán'),
('HD17677597', '2026-01-07', 'KH002', 'Tiền mặt', 7500.00, 'Đã thanh toán');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoa_don_chi_tiet`
--

CREATE TABLE `hoa_don_chi_tiet` (
  `id` int(11) NOT NULL,
  `so_hd` varchar(10) DEFAULT NULL,
  `ma_sp` varchar(10) DEFAULT NULL,
  `so_luong` int(11) DEFAULT NULL,
  `gia_ban` decimal(10,2) DEFAULT NULL,
  `thanh_tien` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoa_don_chi_tiet`
--

INSERT INTO `hoa_don_chi_tiet` (`id`, `so_hd`, `ma_sp`, `so_luong`, `gia_ban`, `thanh_tien`) VALUES
(3, 'HD17677568', 'T001', 1, 1500.00, 1500.00),
(4, 'HD17677597', 'T001', 5, 1500.00, 7500.00);

--
-- Bẫy `hoa_don_chi_tiet`
--
DELIMITER $$
CREATE TRIGGER `trg_update_kho_sau_ban` AFTER INSERT ON `hoa_don_chi_tiet` FOR EACH ROW BEGIN
    DECLARE v_ma_kho VARCHAR(10);
    SELECT ma_kho INTO v_ma_kho FROM sp WHERE ma_sp = NEW.ma_sp;
    UPDATE kho SET sl_giao = sl_giao + NEW.so_luong 
    WHERE ma_kho = v_ma_kho;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `ma_khach` varchar(10) NOT NULL,
  `ten_khach_hang` varchar(255) NOT NULL,
  `dia_chi` text NOT NULL,
  `dien_thoai` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`ma_khach`, `ten_khach_hang`, `dia_chi`, `dien_thoai`) VALUES
('KH001', 'Khách lẻ', 'Mua tại quầy', '0000000000'),
('KH002', 'Nguyễn Văn A', '123 Cách Mạng Tháng 8, HCM', '0901234567');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kho`
--

CREATE TABLE `kho` (
  `ma_kho` varchar(10) NOT NULL,
  `sl_nhap` int(11) NOT NULL DEFAULT 0,
  `sl_giao` int(11) NOT NULL DEFAULT 0,
  `ton_kho` int(11) GENERATED ALWAYS AS (`sl_nhap` - `sl_giao`) STORED,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `kho`
--

INSERT INTO `kho` (`ma_kho`, `sl_nhap`, `sl_giao`, `updated_at`) VALUES
('K001', 500, 6, '2026-01-07 04:23:13'),
('K002', 0, 0, '2026-01-06 22:49:03'),
('K003', 0, 0, '2026-01-06 22:49:47'),
('K004', 0, 0, '2026-01-06 22:51:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `ma_nha_cung_cap` varchar(10) NOT NULL,
  `ten_nha_cung_cap` varchar(255) NOT NULL,
  `dia_chi` text NOT NULL,
  `dien_thoai` varchar(15) NOT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhacungcap`
--

INSERT INTO `nhacungcap` (`ma_nha_cung_cap`, `ten_nha_cung_cap`, `dia_chi`, `dien_thoai`, `ghi_chu`) VALUES
('NCC001', 'Công ty Cổ phần Dược Hậu Giang (DHG)', '288 Bis Nguyễn Văn Cừ, P. An Hòa, Cần Thơ', '02923891433', 'Kháng sinh, Hapacol, Vitamin'),
('NCC002', 'Công ty Cổ phần Traphaco', '75 Yên Ninh, Ba Đình, Hà Nội', '18006612', 'Hoạt huyết dưỡng não, Boganic'),
('NCC003', 'Công ty Dược phẩm Imexpharm', '04 Đường 30/4, TP. Cao Lãnh, Đồng Tháp', '02773851941', 'Thuốc tiêm, kháng sinh cao cấp'),
('NCC004', 'Công ty Dược phẩm Nam Hà', '415 Hàn Thuyên, TP. Nam Định', '02283649408', 'Bổ phế Nam Hà, thuốc đông dược'),
('NCC005', 'Dược phẩm Boston Việt Nam', '43 đường số 8, KCN Việt Nam - Singapore, Bình Dương', '02838112966', 'Thuốc generic, thực phẩm chức năng');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phieunhap`
--

CREATE TABLE `phieunhap` (
  `so_pn` varchar(10) NOT NULL,
  `ngay_nhap` date NOT NULL,
  `ma_kho` varchar(10) NOT NULL,
  `ma_sp` varchar(10) NOT NULL,
  `so_luong_nhap` int(11) NOT NULL,
  `gia_nhap` decimal(10,2) NOT NULL,
  `thanh_tien_nhap` int(11) DEFAULT NULL,
  `ma_nha_cung_cap` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phieunhap`
--

INSERT INTO `phieunhap` (`so_pn`, `ngay_nhap`, `ma_kho`, `ma_sp`, `so_luong_nhap`, `gia_nhap`, `thanh_tien_nhap`, `ma_nha_cung_cap`) VALUES
('PN001', '2026-01-07', 'K001', 'T001', 500, 1000.00, 500000, 'NCC001');

--
-- Bẫy `phieunhap`
--
DELIMITER $$
CREATE TRIGGER `trg_update_kho_sau_nhap` AFTER INSERT ON `phieunhap` FOR EACH ROW BEGIN
    UPDATE kho SET sl_nhap = sl_nhap + NEW.so_luong_nhap 
    WHERE ma_kho = NEW.ma_kho;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sp`
--

CREATE TABLE `sp` (
  `ma_sp` varchar(10) NOT NULL,
  `ten_sp` varchar(255) NOT NULL,
  `ma_danh_muc` varchar(10) NOT NULL,
  `nha_san_xuat` varchar(255) NOT NULL,
  `gia_ban` decimal(10,2) NOT NULL,
  `han_su_dung` date NOT NULL,
  `hoat_chat` varchar(255) NOT NULL,
  `don_vi_tinh` varchar(50) DEFAULT 'Viên',
  `ma_kho` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sp`
--

INSERT INTO `sp` (`ma_sp`, `ten_sp`, `ma_danh_muc`, `nha_san_xuat`, `gia_ban`, `han_su_dung`, `hoat_chat`, `don_vi_tinh`, `ma_kho`) VALUES
('T001', 'Hapacol 650', 'DM002', 'NCC001', 1500.00, '2027-05-07', 'Paracetamol', 'Viên', 'K001'),
('T002', 'Klamentin 625', 'DM004', 'NCC001', 75000.00, '2028-09-01', 'Amoxicillin + Acid clavulanic', 'Vỉ', 'K002'),
('T003', 'Hoạt huyết dưỡng não', 'DM001', 'NCC002', 95000.00, '2026-01-17', 'Cao đinh lăng, cao bạch quả', 'Hộp', 'K003'),
('T004', 'Imexime 200', 'DM004', 'NCC003', 12000.00, '2028-06-02', 'Cefixim', 'Viên', 'K004');

--
-- Bẫy `sp`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_set_kho_sp` BEFORE INSERT ON `sp` FOR EACH ROW BEGIN
    DECLARE v_ma_kho VARCHAR(10);
    SET v_ma_kho = CONCAT('K', SUBSTRING(NEW.ma_sp, 2));
    IF NEW.ma_kho IS NULL OR NEW.ma_kho = '' THEN
        SET NEW.ma_kho = v_ma_kho;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM kho WHERE ma_kho = NEW.ma_kho) THEN
        INSERT INTO kho(ma_kho) VALUES (NEW.ma_kho);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin@gmail.com', '781e5e245d69b566979b86e28d23f2c7');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`ma_danh_muc`);

--
-- Chỉ mục cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`so_hd`),
  ADD KEY `fk_hd_khach` (`ma_khach`);

--
-- Chỉ mục cho bảng `hoa_don_chi_tiet`
--
ALTER TABLE `hoa_don_chi_tiet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cthd_hd` (`so_hd`),
  ADD KEY `fk_cthd_sp` (`ma_sp`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`ma_khach`);

--
-- Chỉ mục cho bảng `kho`
--
ALTER TABLE `kho`
  ADD PRIMARY KEY (`ma_kho`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`ma_nha_cung_cap`);

--
-- Chỉ mục cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD PRIMARY KEY (`so_pn`),
  ADD KEY `fk_pn_kho` (`ma_kho`),
  ADD KEY `fk_pn_sp` (`ma_sp`),
  ADD KEY `fk_pn_ncc` (`ma_nha_cung_cap`);

--
-- Chỉ mục cho bảng `sp`
--
ALTER TABLE `sp`
  ADD PRIMARY KEY (`ma_sp`),
  ADD KEY `fk_sp_danhmuc` (`ma_danh_muc`),
  ADD KEY `fk_sp_kho` (`ma_kho`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `hoa_don_chi_tiet`
--
ALTER TABLE `hoa_don_chi_tiet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `fk_hd_khach` FOREIGN KEY (`ma_khach`) REFERENCES `khachhang` (`ma_khach`);

--
-- Các ràng buộc cho bảng `hoa_don_chi_tiet`
--
ALTER TABLE `hoa_don_chi_tiet`
  ADD CONSTRAINT `fk_cthd_hd` FOREIGN KEY (`so_hd`) REFERENCES `hoa_don` (`so_hd`),
  ADD CONSTRAINT `fk_cthd_sp` FOREIGN KEY (`ma_sp`) REFERENCES `sp` (`ma_sp`);

--
-- Các ràng buộc cho bảng `phieunhap`
--
ALTER TABLE `phieunhap`
  ADD CONSTRAINT `fk_pn_kho` FOREIGN KEY (`ma_kho`) REFERENCES `kho` (`ma_kho`),
  ADD CONSTRAINT `fk_pn_ncc` FOREIGN KEY (`ma_nha_cung_cap`) REFERENCES `nhacungcap` (`ma_nha_cung_cap`),
  ADD CONSTRAINT `fk_pn_sp` FOREIGN KEY (`ma_sp`) REFERENCES `sp` (`ma_sp`);

--
-- Các ràng buộc cho bảng `sp`
--
ALTER TABLE `sp`
  ADD CONSTRAINT `fk_sp_danhmuc` FOREIGN KEY (`ma_danh_muc`) REFERENCES `danh_muc` (`ma_danh_muc`),
  ADD CONSTRAINT `fk_sp_kho` FOREIGN KEY (`ma_kho`) REFERENCES `kho` (`ma_kho`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
