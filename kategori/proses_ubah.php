<?php
/**
 * File: kategori/proses_ubah.php
 * Mengubah nama kategori (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'kategori/index.php');
    exit;
}

$id_kategori   = (int) ($_POST['id_kategori'] ?? 0);
$nama_kategori = trim($_POST['nama_kategori'] ?? '');

if ($id_kategori <= 0 || empty($nama_kategori)) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Nama kategori dan ID tidak valid!'));
    exit;
}

try {
    // Cek duplikasi nama kategori selain ID ini
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM kategori WHERE nama_kategori = :nama AND id_kategori != :id");
    $checkStmt->execute([':nama' => $nama_kategori, ':id' => $id_kategori]);
    if ($checkStmt->fetchColumn() > 0) {
        header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Nama kategori tersebut sudah digunakan!'));
        exit;
    }

    $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = :nama WHERE id_kategori = :id");
    $stmt->execute([':nama' => $nama_kategori, ':id' => $id_kategori]);

    header('Location: ' . BASE_URL . 'kategori/index.php?success=' . urlencode('Kategori berhasil diperbarui!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

