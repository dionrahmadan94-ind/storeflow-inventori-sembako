<?php
/**
 * File: kategori/proses_tambah.php
 * Menambah kategori baru (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'kategori/index.php');
    exit;
}

$nama_kategori = trim($_POST['nama_kategori'] ?? '');

if (empty($nama_kategori)) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Nama kategori wajib diisi!'));
    exit;
}

try {
    // Cek duplikasi nama kategori
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM kategori WHERE nama_kategori = :nama");
    $checkStmt->execute([':nama' => $nama_kategori]);
    if ($checkStmt->fetchColumn() > 0) {
        header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Nama kategori tersebut sudah ada!'));
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (:nama)");
    $stmt->execute([':nama' => $nama_kategori]);

    header('Location: ' . BASE_URL . 'kategori/index.php?success=' . urlencode('Kategori baru berhasil ditambahkan!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'kategori/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

