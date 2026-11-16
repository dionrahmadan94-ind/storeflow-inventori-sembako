-- ==============================================================================
-- DATABASE INVENTORI SEMBAKO (db_inventori)
-- Sistem Inventori Barang - Tugas Kelompok AB
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `db_inventori` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_inventori`;

-- 1. TABEL USERS
DROP TABLE IF EXISTS `barang`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id_user` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','petugas') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL KATEGORI
CREATE TABLE `kategori` (
  `id_kategori` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL SUPPLIER
CREATE TABLE `supplier` (
  `id_supplier` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_supplier` VARCHAR(100) NOT NULL,
  `kontak` VARCHAR(20) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL BARANG
CREATE TABLE `barang` (
  `id_barang` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_barang` VARCHAR(150) NOT NULL,
  `id_kategori` INT NOT NULL,
  `id_supplier` INT NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `harga` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `gambar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) 
    REFERENCES `kategori` (`id_kategori`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_barang_supplier` FOREIGN KEY (`id_supplier`) 
    REFERENCES `supplier` (`id_supplier`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- DATA DUMMY AWAL
-- ==============================================================================

-- User default (password: admin123 untuk admin, petugas123 untuk petugas)
INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Sarah Michelle', 'admin', '$2y$10$MsrbQO1MVnfs2obMxLC5weEzYF91hqlpSbQFK3hu8s5t0T3IL1cMq', 'admin'),
(2, 'Budi Santoso', 'petugas', '$2y$10$8iwKDxY1tC.ujfymkIeMveR/ff1se9/Ar88Aa4X.TzzZivu.NBqMG', 'petugas');

-- 5 Kategori Sembako
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Beras & Biji-bijian'),
(2, 'Minyak Goreng & Margarin'),
(3, 'Gula & Pemanis'),
(4, 'Tepung Terigu & Pati'),
(5, 'Bumbu Dapur & Penyedap');

-- 4 Supplier Mitra
INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `kontak`, `alamat`) VALUES
(1, 'PT Sumber Pangan Sejahtera', '0812-3456-7890', 'Kawasan Pergudangan Daan Mogot Blok A No. 12, Jakarta Barat'),
(2, 'PT Salim Ivomas Pratama', '0813-9876-5432', 'Sudirman Plaza, Indofood Tower Lt. 11, Jakarta Selatan'),
(3, 'PT Sugar Group Companies', '0811-2233-4455', 'Wisma Mulia Lt. 29, Jl. Gatot Subroto Kav. 42, Jakarta Selatan'),
(4, 'PT Bogasari Flour Mills', '0815-6677-8899', 'Jl. Raya Cilincing No. 1, Tanjung Priok, Jakarta Utara');

-- 16 Data Barang Sembako (Dengan beberapa stok < 5 untuk menguji fitur stok menipis/kritis)
INSERT INTO `barang` (`id_barang`, `nama_barang`, `id_kategori`, `id_supplier`, `stok`, `harga`, `gambar`) VALUES
(1, 'Beras Ramos Premium 5kg', 1, 1, 140, 74000.00, NULL),
(2, 'Beras Pandan Wangi Super 5kg', 1, 1, 85, 82500.00, NULL),
(3, 'Beras Ketan Putih 1kg', 1, 1, 45, 19000.00, NULL),
(4, 'Kacang Hijau Kupas 1kg', 1, 1, 30, 26000.00, NULL),
(5, 'Minyak Goreng Bimoli Spesial 2L', 2, 2, 115, 38500.00, NULL),
(6, 'Minyak Goreng Sania Pouch 2L', 2, 2, 2, 37500.00, NULL), -- Kritis (< 5)
(7, 'Minyak Goreng Filma Botol 1L', 2, 2, 50, 19500.00, NULL),
(8, 'Margarin Blue Band Serbaguna 200g', 2, 2, 4, 9500.00, NULL), -- Menipis (< 5)
(9, 'Gula Pasir Kristal Putih Gulaku 1kg', 3, 3, 160, 17500.00, NULL),
(10, 'Gula Pasir Kuning Rose Brand 1kg', 3, 3, 90, 17000.00, NULL),
(11, 'Gula Merah Batok Asli 500g', 3, 3, 3, 14000.00, NULL), -- Kritis (< 5)
(12, 'Tepung Terigu Segitiga Biru 1kg', 4, 4, 120, 12000.00, NULL),
(13, 'Tepung Terigu Cakra Kembar 1kg', 4, 4, 65, 13500.00, NULL),
(14, 'Tepung Tapioka Cap Pak Tani 500g', 4, 4, 4, 7500.00, NULL), -- Menipis (< 5)
(15, 'Garam Beriodium Cap Kapal 250g', 5, 1, 210, 3500.00, NULL),
(16, 'Kecap Manis Bango Botol 550ml', 5, 2, 4, 24500.00, NULL); -- Menipis (< 5)

