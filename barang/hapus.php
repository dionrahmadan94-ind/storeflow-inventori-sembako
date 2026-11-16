<?php
/**
 * File: barang/hapus.php
 * Menghapus data barang (Khusus Role Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

// Proteksi hak akses di sisi server (FR-03 & Matriks Hak Akses: Admin saja)
require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('ID barang tidak valid!'));
    exit;
}

try {
    // Ambil info barang untuk menghapus file gambarnya
    $stmt = $pdo->prepare("SELECT gambar FROM barang WHERE id_barang = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();

    if ($item) {
        if (!empty($item['gambar'])) {
            $filePath = __DIR__ . '/../uploads/' . $item['gambar'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Hapus dari database
        $delStmt = $pdo->prepare("DELETE FROM barang WHERE id_barang = :id");
        $delStmt->execute([':id' => $id]);

        header('Location: ' . BASE_URL . 'barang/index.php?success=' . urlencode('Data barang berhasil dihapus!'));
        exit;
    } else {
        header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('Data barang tidak ditemukan!'));
        exit;
    }
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('Gagal menghapus barang: ' . $e->getMessage()));
    exit;
}

