<?php
/**
 * File: supplier/hapus.php
 * Menghapus supplier barang (Khusus Admin)
 * Memastikan supplier yang masih dipakai barang tidak boleh dihapus (FR-05)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('ID supplier tidak valid!'));
    exit;
}

try {
    // 1. Validasi integritas: cek apakah masih ada barang yang disuplai oleh supplier ini
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_supplier = :id");
    $checkStmt->execute([':id' => $id]);
    $count = (int) $checkStmt->fetchColumn();

    if ($count > 0) {
        header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode("Penolakan: Supplier ini tidak dapat dihapus karena masih menjadi pemasok untuk {$count} data barang!"));
        exit;
    }

    // 2. Eksekusi penghapusan
    $delStmt = $pdo->prepare("DELETE FROM supplier WHERE id_supplier = :id");
    $delStmt->execute([':id' => $id]);

    header('Location: ' . BASE_URL . 'supplier/index.php?success=' . urlencode('Supplier berhasil dihapus!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'supplier/index.php?error=' . urlencode('Gagal menghapus supplier: ' . $e->getMessage()));
    exit;
}

