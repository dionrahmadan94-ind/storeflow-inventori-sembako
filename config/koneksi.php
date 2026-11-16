<?php
/**
 * File: config/koneksi.php
 * Koneksi Database PDO (PHP Data Objects) dengan Prepared Statement
 * Kompatibel dengan Mac Local MySQL (password 'root') & XAMPP Default (password '')
 */

$db_host = '127.0.0.1';
$db_port = 3306;
$db_name = 'db_inventori';
$db_user = 'root';
$db_pass = 'root'; // Password default pada Mac local / MAMP

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception saat terjadi error query
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Ambil data dalam bentuk array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Prepared statement asli dari database engine
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

try {
    // Coba koneksi dengan password utama
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Fallback otomatis coba tanpa password (kompatibilitas pengujian XAMPP default)
    try {
        $pdo = new PDO($dsn, $db_user, '', $options);
    } catch (PDOException $ex) {
        die('<div style="font-family:sans-serif;padding:24px;background:#fee2e2;color:#b91c1c;border-radius:12px;margin:20px;">' .
            '<h3>Gagal Terhubung ke Database</h3>' .
            '<p>Pastikan MySQL server aktif dan database <strong>' . htmlspecialchars($db_name) . '</strong> sudah diimpor.</p>' .
            '<small>Error detail: ' . htmlspecialchars($ex->getMessage()) . '</small>' .
            '</div>');
    }
}

