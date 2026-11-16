<?php
/**
 * File: auth/proses_login.php
 * Verifikasi kredensial login dengan PDO Prepared Statement & password_verify()
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input wajib terisi
if (empty($username) || empty($password)) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=' . urlencode('Username dan password wajib diisi!'));
    exit;
}

try {
    // Cari user berdasarkan username (Prepared Statement)
    $stmt = $pdo->prepare("SELECT id_user, nama, username, password, role FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // Verifikasi keberadaan user dan kecocokan password hash
    if ($user && password_verify($password, $user['password'])) {
        // Regenerasi session ID untuk mencegah session fixation
        session_regenerate_id(true);

        // Simpan data user ke dalam session
        $_SESSION['user'] = [
            'id_user'   => $user['id_user'],
            'nama'      => $user['nama'],
            'username'  => $user['username'],
            'role'      => $user['role']
        ];

        header('Location: ' . BASE_URL . 'dashboard.php');
        exit;
    } else {
        // Kredensial tidak cocok
        header('Location: ' . BASE_URL . 'auth/login.php?error=' . urlencode('Username atau password yang Anda masukkan salah!'));
        exit;
    }
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'auth/login.php?error=' . urlencode('Terjadi kesalahan pada sistem: ' . $e->getMessage()));
    exit;
}

