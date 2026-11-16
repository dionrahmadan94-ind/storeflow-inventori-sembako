<?php
/**
 * File: supplier/proses_ubah.php
 * Mengubah data supplier (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'supplier/index.php');
    exit;
}

$id_supplier   = (int) ($_POST['id_supplier'] ?? 0);
$nama_supplier = trim($_POST['nama_supplier'] ?? '');
$kontak        = trim($_POST['kontak'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');

if ($id_supplier <= 0 || empty($nama_supplier)) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('Nama supplier dan ID tidak valid!'));
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE supplier 
        SET nama_supplier = :nama,
            kontak = :kontak,
            alamat = :alamat
        WHERE id_supplier = :id
    ");
    $stmt->execute([
        ':nama'   => $nama_supplier,
        ':kontak' => $kontak ?: null,
        ':alamat' => $alamat ?: null,
        ':id'     => $id_supplier
    ]);

    header('Location: ' . BASE_URL . 'supplier/index.php?success=' . urlencode('Data supplier berhasil diperbarui!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

