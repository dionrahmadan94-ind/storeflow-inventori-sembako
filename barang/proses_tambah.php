<?php
/**
 * File: barang/proses_tambah.php
 * Memproses penyimpanan data barang baru & upload gambar
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'barang/index.php');
    exit;
}

$nama_barang = trim($_POST['nama_barang'] ?? '');
$id_kategori = (int) ($_POST['id_kategori'] ?? 0);
$id_supplier = (int) ($_POST['id_supplier'] ?? 0);
$stok        = (int) ($_POST['stok'] ?? 0);
$harga       = (float) ($_POST['harga'] ?? 0);

// Validasi Form Wajib Terisi & Nilai Non-Negatif (FR-08)
if (empty($nama_barang) || $id_kategori <= 0 || $id_supplier <= 0) {
    header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Semua kolom bertanda bintang (*) wajib diisi!'));
    exit;
}

if ($stok < 0 || $harga < 0) {
    header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Stok dan harga tidak boleh bernilai negatif!'));
    exit;
}

// Proses Upload Gambar (FR-09 Fitur Tambahan 1)
$nama_gambar = null;
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['gambar']['tmp_name'];
    $fileName    = $_FILES['gambar']['name'];
    $fileSize    = $_FILES['gambar']['size'];
    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $maxFileSize       = 2 * 1024 * 1024; // 2 MB

    if (!in_array($fileExt, $allowedExtensions)) {
        header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Format gambar harus JPG, JPEG, atau PNG!'));
        exit;
    }

    if ($fileSize > $maxFileSize) {
        header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Ukuran gambar maksimal 2 MB!'));
        exit;
    }

    // Pastikan folder uploads ada
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Rename unik untuk mencegah duplikasi & konflik nama file
    $nama_gambar = uniqid('brg_', true) . '.' . $fileExt;
    $destPath    = $uploadDir . $nama_gambar;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Gagal mengunggah gambar ke server!'));
        exit;
    }
}

try {
    // Simpan ke database dengan PDO Prepared Statement
    $stmt = $pdo->prepare("
        INSERT INTO barang (nama_barang, id_kategori, id_supplier, stok, harga, gambar)
        VALUES (:nama, :id_kategori, :id_supplier, :stok, :harga, :gambar)
    ");
    $stmt->execute([
        ':nama'        => $nama_barang,
        ':id_kategori' => $id_kategori,
        ':id_supplier' => $id_supplier,
        ':stok'        => $stok,
        ':harga'       => $harga,
        ':gambar'      => $nama_gambar
    ]);

    header('Location: ' . BASE_URL . 'barang/index.php?success=' . urlencode('Barang berhasil ditambahkan!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'barang/tambah.php?error=' . urlencode('Terjadi kesalahan database: ' . $e->getMessage()));
    exit;
}

