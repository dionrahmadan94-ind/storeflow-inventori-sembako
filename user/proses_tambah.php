<?php
/**
 * File: user/proses_tambah.php
 * Menambah pengguna baru dengan hash password (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'user/index.php');
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$role     = trim($_POST['role'] ?? 'petugas');

// Validasi Form
if (empty($nama) || empty($username) || empty($password)) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Semua kolom wajib diisi!'));
    exit;
}

// Validasi Panjang Password Minimal 6 Karakter (FR-08)
if (strlen($password) < 6) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Password harus memiliki panjang minimal 6 karakter!'));
    exit;
}

// Validasi Role
if (!in_array($role, ['admin', 'petugas'])) {
    $role = 'petugas';
}

try {
    // Validasi Keunikan Username (FR-08)
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $checkStmt->execute([':username' => $username]);
    if ($checkStmt->fetchColumn() > 0) {
        header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Username tersebut sudah digunakan oleh akun lain!'));
        exit;
    }

    // Hash Password dengan BCRYPT (FR-01)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (nama, username, password, role)
        VALUES (:nama, :username, :password, :role)
    ");
    $stmt->execute([
        ':nama'     => $nama,
        ':username' => $username,
        ':password' => $hashedPassword,
        ':role'     => $role
    ]);

    header('Location: ' . BASE_URL . 'user/index.php?success=' . urlencode('Pengguna baru berhasil ditambahkan!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

