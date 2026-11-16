<?php
/**
 * File: kategori/hapus.php
 * Menghapus kategori barang (Khusus Admin)
 * Memastikan kategori yang masih dipakai barang tidak boleh dihapus (FR-04)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('ID kategori tidak valid!'));
    exit;
}

try {
    // 1. Validasi integritas: cek apakah masih ada barang yang menggunakan kategori ini
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_kategori = :id");
    $checkStmt->execute([':id' => $id]);
    $count = (int) $checkStmt->fetchColumn();

    if ($count > 0) {
        header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode("Penolakan: Kategori ini tidak dapat dihapus karena masih digunakan oleh {$count} data barang!"));
        exit;
    }

    // 2. Eksekusi penghapusan
    $delStmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = :id");
    $delStmt->execute([':id' => $id]);

    header('Location: ' . BASE_URL . 'kategori/index.php?success=' . urlencode('Kategori berhasil dihapus!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Gagal menghapus kategori: ' . $e->getMessage()));
    exit;
}

