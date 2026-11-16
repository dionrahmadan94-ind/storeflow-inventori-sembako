<?php
/**
 * Konfigurasi Aplikasi & Path
 * Sistem Inventori Sembako
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Direktori fisik root proyek
    $project_root = str_replace('\\', '/', dirname(__DIR__));
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    
    if ($doc_root && strpos($project_root, $doc_root) === 0) {
        $relative_path = substr($project_root, strlen($doc_root));
    } else {
        $relative_path = '';
    }
    $relative_path = '/' . trim($relative_path, '/');
    $baseUrl = rtrim($protocol . $host . ($relative_path !== '/' ? $relative_path : ''), '/') . '/';
    define('BASE_URL', $baseUrl);
}

