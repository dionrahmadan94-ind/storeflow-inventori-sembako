<?php
$page_title = 'Data Barang';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Filter & Pencarian
$q = trim($_GET['q'] ?? '');
$kategoriFilter = trim($_GET['kategori'] ?? '');
$stokFilter = trim($_GET['stok'] ?? '');

// Ambil daftar kategori untuk dropdown filter
$stmtKat = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$kategoriList = $stmtKat->fetchAll();

// Bangun Query dengan Prepared Statement
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

// Hitung Total Data untuk Pagination (10 data per halaman - FR-09)
$countSql = "
    SELECT COUNT(*) 
    FROM barang b 
    JOIN kategori k ON b.id_kategori = k.id_kategori 
    {$whereSql}
";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRows = (int) $stmtCount->fetchColumn();

$limit = 10;
$totalPages = max(1, (int) ceil($totalRows / $limit));
$page = isset($_GET['page']) ? max(1, min($totalPages, (int) $_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Query Data Barang dengan JOIN Kategori & Supplier
$dataSql = "
    SELECT 
        b.id_barang,
        b.nama_barang,
        b.id_kategori,
        b.id_supplier,
        b.stok,
        b.harga,
        b.gambar,
        b.created_at,
        k.nama_kategori,
        s.nama_supplier,
        s.kontak as kontak_supplier,
        s.alamat as alamat_supplier
    FROM barang b
    JOIN kategori k ON b.id_kategori = k.id_kategori
    JOIN supplier s ON b.id_supplier = s.id_supplier
    {$whereSql}
    ORDER BY b.id_barang DESC
    LIMIT :offset, :limit
";

$stmtData = $pdo->prepare($dataSql);
foreach ($params as $key => $val) {
    $stmtData->bindValue($key, $val);
}
$stmtData->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtData->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtData->execute();
$barangList = $stmtData->fetchAll();

// URL Query String untuk Pagination & Export CSV
$queryParams = $_GET;
unset($queryParams['page']);
$baseQueryString = http_build_query($queryParams);
$exportUrl = BASE_URL . 'barang/export.php' . ($baseQueryString ? '?' . $baseQueryString : '');
?>

<main class="sf-main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="sf-body-container">
        <!-- Notifikasi Feedback -->
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-4" style="font-size: 0.88rem;">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['success']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-4" style="font-size: 0.88rem;">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['error']) ?></div>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-1 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h3 class="fw-bold mb-0">Data Barang</h3>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                        Total <?= $totalRows ?> Barang
                    </span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Kelola seluruh data inventori sembako, harga, dan ketersediaan stok fisik gudang.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= $exportUrl ?>" class="btn btn-sf-light">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                    <span>Export CSV</span>
                </a>
                <a href="<?= BASE_URL ?>barang/tambah.php" class="btn btn-sf-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Barang</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar (Storeflow Clean Layout) -->
        <div class="sf-card mb-4 p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                <!-- Search Keyword -->
                <div class="col-12 col-md-4">
                    <div class="sf-search-box w-100">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" placeholder="Cari nama barang..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>

                <!-- Filter Kategori -->
                <div class="col-6 col-md-3">
                    <select name="kategori" class="form-select sf-input" style="height: 40px;">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoriList as $kat): ?>
                            <option value="<?= $kat['id_kategori'] ?>" <?= ($kategoriFilter == $kat['id_kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Status Stok -->
                <div class="col-6 col-md-3">
                    <select name="stok" class="form-select sf-input" style="height: 40px;">
                        <option value="">Semua Status Stok</option>
                        <option value="aman" <?= ($stokFilter === 'aman') ? 'selected' : '' ?>>Stok Aman (&ge; 5)</option>
                        <option value="kritis" <?= ($stokFilter === 'kritis') ? 'selected' : '' ?>>Stok Kritis (&lt; 5)</option>
                    </select>
                </div>

                <!-- Filter Submit & Reset -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sf-primary w-100 justify-content-center" style="height: 40px;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <?php if ($q !== '' || $kategoriFilter !== '' || $stokFilter !== ''): ?>
                        <a href="<?= BASE_URL ?>barang/index.php" class="btn btn-sf-light" title="Reset filter" style="height: 40px; padding: 0 14px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Main Data Table (Storeflow Style Table) -->
        <div class="sf-card p-0 overflow-hidden mb-4">
            <div class="sf-table-responsive">
                <table class="table sf-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Info Barang</th>
                            <th>Kategori</th>
                            <th>Supplier</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($barangList)): ?>
                            <?php foreach ($barangList as $index => $item): ?>
                                <?php 
                                    $isKritis = $item['stok'] < 5;
                                    $statusBadge = $isKritis 
                                        ? '<span class="badge-soft-danger"><i class="bi bi-exclamation-circle me-1"></i>Kritis</span>' 
                                        : '<span class="badge-soft-success"><i class="bi bi-check-circle me-1"></i>Aman</span>';
                                    
                                    $thumb = !empty($item['gambar']) && file_exists(__DIR__ . '/../uploads/' . $item['gambar'])
                                        ? BASE_URL . 'uploads/' . htmlspecialchars($item['gambar'])
                                        : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=150&q=80';

                                    $itemJson = [
                                        'id' => $item['id_barang'],
                                        'kode' => 'BRG-' . str_pad($item['id_barang'], 3, '0', STR_PAD_LEFT),
                                        'nama' => $item['nama_barang'],
                                        'kategori' => $item['nama_kategori'],
                                        'supplier' => $item['nama_supplier'],
                                        'harga' => $item['harga'],
                                        'stok' => $item['stok'],
                                        'gambar' => $thumb,
                                        'kontak' => $item['kontak_supplier'] ?? '-',
                                        'alamat' => $item['alamat_supplier'] ?? '-'
                                    ];
                                ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $offset + $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>" class="sf-thumb-sm" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/package-placeholder.svg';">
                                            <div class="overflow-hidden" style="max-width: 220px;">
                                                <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($item['nama_barang']) ?>">
                                                    <?= htmlspecialchars($item['nama_barang']) ?>
                                                </div>
                                                <small class="text-muted">#BRG-<?= str_pad($item['id_barang'], 3, '0', STR_PAD_LEFT) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                            <?= htmlspecialchars($item['nama_kategori']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="overflow-hidden" style="max-width: 180px;">
                                            <div class="fw-semibold text-dark text-truncate" style="font-size: 0.84rem;" title="<?= htmlspecialchars($item['nama_supplier']) ?>">
                                                <?= htmlspecialchars($item['nama_supplier']) ?>
                                            </div>
                                            <small class="text-muted"><?= htmlspecialchars($item['kontak_supplier'] ?? '-') ?></small>
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap fw-bold">
                                        Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="fw-bold fs-6 <?= $isKritis ? 'text-danger' : 'text-dark' ?>">
                                            <?= number_format($item['stok'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?= $statusBadge ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Detail Button -->
                                            <button type="button" class="btn-sf-icon btn-detail" title="Lihat Detail" data-item="<?= htmlspecialchars(json_encode($itemJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <!-- Edit Button -->
                                            <a href="<?= BASE_URL ?>barang/ubah.php?id=<?= $item['id_barang'] ?>" class="btn-sf-icon" title="Ubah Data">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- Kurangi Stok (Admin & Petugas) -->
                                            <form method="POST" action="<?= BASE_URL ?>barang/kurangi.php" style="display:inline;">
                                                <input type="hidden" name="id" value="<?= $item['id_barang'] ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="btn-sf-icon danger" title="Kurangi Stok 1">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>
                                            </form>
                                            <!-- Delete Button (Only Admin) -->
                                            <?php if (is_admin()): ?>
                                                <button type="button" class="btn-sf-icon danger" title="Hapus Barang" onclick="confirmDelete(<?= $item['id_barang'] ?>, '<?= htmlspecialchars(addslashes($item['nama_barang'])) ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                    Tidak ada data barang yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer: Pagination & Count -->
            <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <span class="text-muted" style="font-size: 0.84rem;">
                    Menampilkan <strong><?= $totalRows > 0 ? $offset + 1 : 0 ?> - <?= min($offset + $limit, $totalRows) ?></strong> dari <strong><?= $totalRows ?></strong> barang
                </span>
                <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm m-0 gap-1">
                            <!-- Prev Link -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 border-0 bg-light text-muted" href="?<?= http_build_query(array_merge($queryParams, ['page' => $page - 1])) ?>">
                                    <i class="bi bi-chevron-left me-1"></i> Prev
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                    <a class="page-link rounded-circle px-3 border-0 <?= ($i === $page) ? 'text-white' : 'text-dark bg-light' ?>" 
                                       style="<?= ($i === $page) ? 'background-color: var(--sf-primary);' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Link -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 border-0 bg-light text-dark" href="?<?= http_build_query(array_merge($queryParams, ['page' => $page + 1])) ?>">
                                    Next <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

    </div> <!-- /.sf-body-container -->

    <!-- Storeflow Style Detail Modal (Image 3 Tablet Inspector Modal) -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <div>
                        <span class="badge bg-light text-dark border rounded-pill mb-1" id="modalKode">BRG-001</span>
                        <h5 class="modal-title fw-bold" id="modalNama">Detail Barang</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <img id="modalGambar" src="" alt="Barang" class="img-fluid rounded-4 shadow-sm mb-4" style="max-height: 200px; object-fit: cover; width: 100%;" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/package-placeholder.svg';">
                    
                    <div class="row g-3 text-start">
                        <div class="col-6">
                            <small class="text-muted d-block">Kategori</small>
                            <span class="fw-bold" id="modalKategori">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Harga Satuan</small>
                            <span class="fw-bold text-success" id="modalHarga">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Stok Tersedia</small>
                            <span class="fw-bold fs-5" id="modalStok">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Status Ketersediaan</small>
                            <span id="modalStatusBadge">-</span>
                        </div>
                        <div class="col-12 pt-2 border-top">
                            <small class="text-muted d-block">Supplier Pemasok</small>
                            <span class="fw-bold d-block" id="modalSupplier">-</span>
                            <small class="text-muted" id="modalKontak">-</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-sf-light" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="modalEditBtn" class="btn btn-sf-primary">
                        <i class="bi bi-pencil me-1"></i> Ubah Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Interaktif Modal & Konfirmasi Hapus -->
    <script>
        let detailModal = null;

        function renderDetailModal(item) {
            document.getElementById('modalKode').innerText = item.kode || '-';
            document.getElementById('modalNama').innerText = item.nama || '-';
            document.getElementById('modalGambar').src = item.gambar || '';
            document.getElementById('modalKategori').innerText = item.kategori || '-';
            document.getElementById('modalHarga').innerText = 'Rp ' + Number(item.harga || 0).toLocaleString('id-ID');
            document.getElementById('modalStok').innerText = Number(item.stok || 0).toLocaleString('id-ID') + ' pcs';
            
            const isKritis = Number(item.stok || 0) < 5;
            document.getElementById('modalStatusBadge').innerHTML = isKritis 
                ? '<span class="badge-soft-danger"><i class="bi bi-exclamation-triangle me-1"></i>Stok Kritis</span>'
                : '<span class="badge-soft-success"><i class="bi bi-check-circle me-1"></i>Stok Aman</span>';

            document.getElementById('modalSupplier').innerText = item.supplier || '-';
            document.getElementById('modalKontak').innerText = 'Kontak: ' + (item.kontak || '-');
            document.getElementById('modalEditBtn').href = '<?= BASE_URL ?>barang/ubah.php?id=' + item.id;
            
            if (!detailModal) {
                detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
            }
            detailModal.show();
        }

        function showDetail(item) {
            renderDetailModal(item);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi modal instance
            const modalEl = document.getElementById('detailModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                detailModal = new bootstrap.Modal(modalEl);
            }

            // Event listener untuk tombol detail
            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', function () {
                    try {
                        const rawData = this.getAttribute('data-item');
                        const item = JSON.parse(rawData);
                        renderDetailModal(item);
                    } catch (e) {
                        console.error('Error saat parsing data detail barang:', e);
                    }
                });
            });
        });

        function confirmDelete(id, name) {
            if (confirm(`Yakin ingin menghapus barang "${name}"?`)) {
                window.location.href = `<?= BASE_URL ?>barang/hapus.php?id=${id}`;
            }
        }
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
