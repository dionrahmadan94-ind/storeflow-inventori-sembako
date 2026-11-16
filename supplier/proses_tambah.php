<?php
/**
 * File: supplier/proses_tambah.php
 * Menambah supplier baru (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'supplier/index.php');
    exit;
}

$nama_supplier = trim($_POST['nama_supplier'] ?? '');
$kontak        = trim($_POST['kontak'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');

if (empty($nama_supplier)) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('Nama supplier wajib diisi!'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO supplier (nama_supplier, kontak, alamat)
        VALUES (:nama, :kontak, :alamat)
    ");
    $stmt->execute([
        ':nama'   => $nama_supplier,
        ':kontak' => $kontak ?: null,
        ':alamat' => $alamat ?: null
    ]);

    header('Location: ' . BASE_URL . 'supplier/index.php?success=' . urlencode('Supplier baru berhasil ditambahkan!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

