<?php
/**
 * reset_data.php
 * Utility script to **empty** all core tables (users, kategori, supplier, barang)
 * so you can start fresh with your own data.
 *
 * It disables foreign‑key checks, truncates each table, then reenables checks.
 * Run from the project root:
 *   php reset_data.php
 */
require_once __DIR__ . '/config/koneksi.php';

// Disable FK checks to allow truncating tables with relations
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

$tables = ['users', 'kategori', 'supplier', 'barang'];
foreach ($tables as $t) {
    $pdo->exec("TRUNCATE TABLE `$t`");
    echo "Truncated `$t`\n";
}

// Re‑enable FK checks
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "All tables cleared. Use the web UI (tambah.php) to insert your own data, or import via CSV.\n";
?>
