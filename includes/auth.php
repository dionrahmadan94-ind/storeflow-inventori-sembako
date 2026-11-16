<?php
/**
 * File: includes/auth.php
 * Middleware Autentikasi & Hak Akses (RBAC) di sisi server
 */

require_once __DIR__ . '/../config/app.php';

/**
 * Wajibkan pengguna sudah login untuk mengakses halaman
 */
function require_login() {
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['id_user'])) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

/**
 * Wajibkan role tertentu (misal: 'admin') untuk mengakses halaman/aksi tertentu
 */
function require_role($allowed_roles = ['admin']) {
    require_login();

    if (is_string($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }

    $user_role = $_SESSION['user']['role'] ?? '';

    if (!in_array($user_role, $allowed_roles)) {
        // Tolak akses di sisi server sesuai ketentuan PRD (bukan hanya sembunyikan menu)
        header('Location: ' . BASE_URL . 'dashboard.php?error=' . urlencode('Akses ditolak: Anda tidak memiliki izin untuk fitur ini.'));
        exit;
    }
}

/**
 * Helper untuk mengecek apakah user yang sedang login adalah admin
 */
function is_admin() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Helper untuk mendapatkan data user saat ini
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}
?>