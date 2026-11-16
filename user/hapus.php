<?php
/**
 * File: user/hapus.php
 * Menghapus akun pengguna (Khusus Admin)
 * Memastikan admin tidak boleh menghapus akunnya sendiri (FR-06)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('ID pengguna tidak valid!'));
    exit;
}

// Validasi Aturan Bisnis FR-06: Admin dilarang menghapus akunnya sendiri
if ($id == $_SESSION['user']['id_user']) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Tindakan ditolak: Anda tidak dapat menghapus akun Anda sendiri saat sedang login!'));
    exit;
}

try {
    $delStmt = $pdo->prepare("DELETE FROM users WHERE id_user = :id");
    $delStmt->execute([':id' => $id]);

    header('Location: ' . BASE_URL . 'user/index.php?success=' . urlencode('Akun pengguna berhasil dihapus!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Gagal menghapus pengguna: ' . $e->getMessage()));
    exit;
}

