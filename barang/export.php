<?php
/**
 * File: barang/export.php
 * Mengekspor data barang aktif (mengikuti filter pencarian aktif) ke format CSV
 * FR-09 Fitur Tambahan 3
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

// Ambil parameter filter aktif
$q = trim($_GET['q'] ?? '');
$kategoriFilter = trim($_GET['kategori'] ?? '');
$stokFilter = trim($_GET['stok'] ?? '');

$whereClauses = [];
$params = [];

if ($q !== '') {
    $whereClauses[] = "(b.nama_barang LIKE :keyword1 OR k.nama_kategori LIKE :keyword2)";
    $params[':keyword1'] = "%{$q}%";
    $params[':keyword2'] = "%{$q}%";
}

if ($kategoriFilter !== '') {
    $whereClauses[] = "b.id_kategori = :kategori";
    $params[':kategori'] = $kategoriFilter;
}

if ($stokFilter === 'aman') {
    $whereClauses[] = "b.stok >= 5";
} elseif ($stokFilter === 'kritis') {
    $whereClauses[] = "b.stok < 5";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

try {
    $dataSql = "
        SELECT 
            b.id_barang,
            b.nama_barang,
            k.nama_kategori,
            s.nama_supplier,
            s.kontak as kontak_supplier,
            b.harga,
            b.stok,
            b.created_at
        FROM barang b
        JOIN kategori k ON b.id_kategori = k.id_kategori
        JOIN supplier s ON b.id_supplier = s.id_supplier
        {$whereSql}
        ORDER BY b.id_barang ASC
    ";

    $stmt = $pdo->prepare($dataSql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Nama file unduhan
    $filename = "laporan-inventori-sembako-" . date('Ymd-His') . ".csv";

    // Set header HTTP untuk download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Tulis UTF-8 BOM agar Excel menampilkan aksen karakter dan angka dengan benar
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header Kolom CSV
    fputcsv($output, [
        'No',
        'Kode Barang',
        'Nama Barang',
        'Kategori',
        'Supplier',
        'Kontak Supplier',
        'Harga Satuan (Rp)',
        'Stok Fisik',
        'Status Stok',
        'Tanggal Input'
    ]);

    // Baris Data
    $no = 1;
    foreach ($items as $row) {
        $status = ($row['stok'] < 5) ? 'Kritis / Butuh Restock' : 'Aman';
        $kode   = 'BRG-' . str_pad($row['id_barang'], 3, '0', STR_PAD_LEFT);

        fputcsv($output, [
            $no++,
            $kode,
            $row['nama_barang'],
            $row['nama_kategori'],
            $row['nama_supplier'],
            $row['kontak_supplier'] ?? '-',
            number_format($row['harga'], 0, ',', '.'),
            $row['stok'],
            $status,
            $row['created_at']
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die("Gagal mengekspor data: " . $e->getMessage());
}

