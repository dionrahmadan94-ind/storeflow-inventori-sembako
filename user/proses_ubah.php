<?php
/**
 * File: user/proses_ubah.php
 * Mengubah data pengguna & opsional ganti password (Khusus Admin)
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'user/index.php');
    exit;
}

$id_user  = (int) ($_POST['id_user'] ?? 0);
$nama     = trim($_POST['nama'] ?? '');
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$role     = trim($_POST['role'] ?? 'petugas');

if ($id_user <= 0 || empty($nama) || empty($username)) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Nama dan username wajib diisi!'));
    exit;
}

if (!in_array($role, ['admin', 'petugas'])) {
    $role = 'petugas';
}

try {
    // Validasi Keunikan Username selain ID user ini
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id_user != :id");
    $checkStmt->execute([':username' => $username, ':id' => $id_user]);
    if ($checkStmt->fetchColumn() > 0) {
        header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Username tersebut sudah digunakan oleh akun lain!'));
        exit;
    }

    // Jika password diisi, validasi minimal 6 karakter lalu update dengan hash baru
    if (!empty($password)) {
        if (strlen($password) < 6) {
            header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Password baru minimal 6 karakter!'));
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET nama = :nama,
                username = :username,
                password = :password,
                role = :role
            WHERE id_user = :id
        ");
        $stmt->execute([
            ':nama'     => $nama,
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role'     => $role,
            ':id'       => $id_user
        ]);
    } else {
        // Password dibiarkan kosong bila tidak ingin diganti (FR-06)
        $stmt = $pdo->prepare("
            UPDATE users 
            SET nama = :nama,
                username = :username,
                role = :role
            WHERE id_user = :id
        ");
        $stmt->execute([
            ':nama'     => $nama,
            ':username' => $username,
            ':role'     => $role,
            ':id'       => $id_user
        ]);
    }

    // Jika mengedit akun sendiri, sinkronkan data session
    if ($id_user == $_SESSION['user']['id_user']) {
        $_SESSION['user']['nama']     = $nama;
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['role']     = $role;
    }

    header('Location: ' . BASE_URL . 'user/index.php?success=' . urlencode('Data pengguna berhasil diperbarui!'));
    exit;
} catch (PDOException $e) {
    header('Location: ' . BASE_URL . 'user/index.php?error=' . urlencode('Database error: ' . $e->getMessage()));
    exit;
}

