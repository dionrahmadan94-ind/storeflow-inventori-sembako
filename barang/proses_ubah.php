<?php
/**
 * File: barang/proses_ubah.php
 * Memproses pembaruan data barang & pergantian gambar
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'barang/index.php');
    exit;
}

$id_barang   = (int) ($_POST['id_barang'] ?? 0);
$nama_barang = trim($_POST['nama_barang'] ?? '');
$id_kategori = (int) ($_POST['id_kategori'] ?? 0);
$id_supplier = (int) ($_POST['id_supplier'] ?? 0);
$stok        = (int) ($_POST['stok'] ?? 0);
$harga       = (float) ($_POST['harga'] ?? 0);

if ($id_barang <= 0 || empty($nama_barang) || $id_kategori <= 0 || $id_supplier <= 0) {
    header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Semua kolom wajib diisi!'));
    exit;
}

if ($stok < 0 || $harga < 0) {
    header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Stok dan harga tidak boleh bernilai negatif!'));
    exit;
}

// Ambil data barang yang ada
$stmtOld = $pdo->prepare("SELECT gambar FROM barang WHERE id_barang = :id LIMIT 1");
$stmtOld->execute([':id' => $id_barang]);
$oldItem = $stmtOld->fetch();

if (!$oldItem) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('Barang tidak ditemukan!'));
    exit;
}

$nama_gambar = $oldItem['gambar'];

// Cek apakah ada file gambar baru yang diunggah
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['gambar']['tmp_name'];
    $fileName    = $_FILES['gambar']['name'];
    $fileSize    = $_FILES['gambar']['size'];
    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    $maxFileSize       = 2 * 1024 * 1024; // 2 MB

    if (!in_array($fileExt, $allowedExtensions)) {
        header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Format gambar harus JPG, JPEG, atau PNG!'));
        exit;
    }

    if ($fileSize > $maxFileSize) {
        header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Ukuran gambar maksimal 2 MB!'));
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Hapus gambar lama jika ada
    if (!empty($oldItem['gambar']) && file_exists($uploadDir . $oldItem['gambar'])) {
        @unlink($uploadDir . $oldItem['gambar']);
    }

    // Simpan gambar baru
    $nama_gambar = uniqid('brg_', true) . '.' . $fileExt;
    $destPath    = $uploadDir . $nama_gambar;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Gagal mengunggah file baru!'));
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        UPDATE barang 
        SET nama_barang = :nama,
            id_kategori = :id_kategori,
            id_supplier = :id_supplier,
            stok = :stok,
            harga = :harga,
            gambar = :gambar
        WHERE id_barang = :id
    ");
    $stmt->execute([
        ':nama'        => $nama_barang,
        ':id_kategori' => $id_kategori,
        ':id_supplier' => $id_supplier,
        ':stok'        => $stok,
        ':harga'       => $harga,
        ':gambar'      => $nama_gambar,
        ':id'          => $id_barang
    ]);

    header('Location: ' . BASE_URL . 'barang/index.php?success=' . urlencode('Perubahan data barang berhasil disimpan!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'barang/ubah.php?id=' . $id_barang . '&error=' . urlencode('Gagal memperbarui database: ' . $e->getMessage()));
    exit;
}

