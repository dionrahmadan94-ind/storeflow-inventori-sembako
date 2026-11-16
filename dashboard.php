<?php
$page_title = 'Home / Dashboard';
$active_menu = 'dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Ambil Statistik dari Database
try {
    // 1. Total Jenis Barang
    $stmt = $pdo->query("SELECT COUNT(*) FROM barang");
    $totalBarang = (int) $stmt->fetchColumn();

    // 2. Total Stok Fisik
    $stmt = $pdo->query("SELECT COALESCE(SUM(stok), 0) FROM barang");
    $totalStokFisik = (int) $stmt->fetchColumn();

    // 3. Total Kategori & Supplier
    $stmt = $pdo->query("SELECT COUNT(*) FROM kategori");
    $totalKategori = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM supplier");
    $totalSupplier = (int) $stmt->fetchColumn();

    // 4. Barang Stok Kritis (< 5)
    $stmt = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok < 5");
    $totalKritis = (int) $stmt->fetchColumn();

    // 5. Data Grafik Stok per Kategori
    $stmt = $pdo->query("
        SELECT k.nama_kategori, COALESCE(SUM(b.stok), 0) as total_stok, COUNT(b.id_barang) as total_item
        FROM kategori k
        LEFT JOIN barang b ON k.id_kategori = b.id_kategori
        GROUP BY k.id_kategori, k.nama_kategori
        ORDER BY total_stok DESC
    ");
    $chartRows = $stmt->fetchAll();
    $chartLabels = array_column($chartRows, 'nama_kategori');
    $chartStokData = array_map('intval', array_column($chartRows, 'total_stok'));
    // Target safety stock (misal: 25 * total item per kategori)
    $chartTargetData = array_map(function($row) {
        return max(50, (int)$row['total_item'] * 25);
    }, $chartRows);

    // 6. Top Produk dengan Stok Terbanyak (Maksimal 4)
    $stmt = $pdo->query("
        SELECT b.*, k.nama_kategori
        FROM barang b
        JOIN kategori k ON b.id_kategori = k.id_kategori
        ORDER BY b.stok DESC
        LIMIT 4
    ");
    $topProducts = $stmt->fetchAll();

    // Hitung max stok untuk rasio persentase progress bar
    $maxStok = !empty($topProducts) ? max(array_column($topProducts, 'stok')) : 100;
    if ($maxStok <= 0) $maxStok = 100;

    // 7. Inventory Alerts (Stok < 5)
    $stmt = $pdo->query("
        SELECT b.*, k.nama_kategori, s.nama_supplier
        FROM barang b
        JOIN kategori k ON b.id_kategori = k.id_kategori
        JOIN supplier s ON b.id_supplier = s.id_supplier
        WHERE b.stok < 5
        ORDER BY b.stok ASC
        LIMIT 4
    ");
    $alertItems = $stmt->fetchAll();

    // 8. Barang Terkini (Maksimal 5)
    $stmt = $pdo->query("
        SELECT b.*, k.nama_kategori
        FROM barang b
        JOIN kategori k ON b.id_kategori = k.id_kategori
        ORDER BY b.id_barang DESC
        LIMIT 5
    ");
    $recentBarang = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}

$alertMsg = $_GET['error'] ?? ($_GET['success'] ?? '');
?>

<!-- Main Content Wrapper -->
<main class="sf-main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="sf-body-container">
        <!-- Notifikasi jika ada redirect hak akses atau pesan sukses -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-4" style="font-size: 0.88rem;">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['error']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-4" style="font-size: 0.88rem;">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['success']) ?></div>
            </div>
        <?php endif; ?>

        <!-- Welcome Greeting Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 pb-1 gap-3">
            <div>
                <h3 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($currentUser['nama']) ?>!</h3>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    <?= date('l, d F Y') ?> &bull; Ringkasan operasional dan ketersediaan stok sembako.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>barang/export.php" class="btn btn-sf-light">
                    <i class="bi bi-download"></i>
                    <span>Export CSV</span>
                </a>
                <a href="<?= BASE_URL ?>barang/tambah.php" class="btn btn-sf-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Barang</span>
                </a>
            </div>
        </div>

        <!-- 4 Top Stat Cards (Storeflow Metric Cards) -->
        <div class="row g-3 mb-4">
            <!-- Total Barang -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sf-card sf-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="sf-stat-label">Total Jenis Barang</span>
                        <div class="btn-sf-icon" style="width: 34px; height: 34px;">
                            <i class="bi bi-box-seam text-success"></i>
                        </div>
                    </div>
                    <div class="sf-stat-value"><?= number_format($totalBarang) ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--sf-text-muted);">item</span></div>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="sf-stat-trend positive">
                            <i class="bi bi-check2"></i> Terdaftar
                        </span>
                        <span class="text-muted ms-1" style="font-size: 0.75rem;">database aktif</span>
                    </div>
                </div>
            </div>

            <!-- Total Stok Fisik -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sf-card sf-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="sf-stat-label">Total Stok Fisik</span>
                        <div class="btn-sf-icon" style="width: 34px; height: 34px;">
                            <i class="bi bi-stack text-primary"></i>
                        </div>
                    </div>
                    <div class="sf-stat-value"><?= number_format($totalStokFisik) ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--sf-text-muted);">pcs</span></div>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="sf-stat-trend positive">
                            <i class="bi bi-check-circle-fill"></i> Normal
                        </span>
                        <span class="text-muted ms-1" style="font-size: 0.75rem;">ketersediaan gudang</span>
                    </div>
                </div>
            </div>

            <!-- Kategori & Supplier -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sf-card sf-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="sf-stat-label">Master Data</span>
                        <div class="btn-sf-icon" style="width: 34px; height: 34px;">
                            <i class="bi bi-diagram-3 text-info"></i>
                        </div>
                    </div>
                    <div class="sf-stat-value"><?= $totalKategori ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--sf-text-muted);">Kat / <?= $totalSupplier ?> Sup</span></div>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="sf-stat-trend neutral">
                            <i class="bi bi-link-45deg"></i> Terhubung
                        </span>
                        <span class="text-muted ms-1" style="font-size: 0.75rem;">relasi aktif</span>
                    </div>
                </div>
            </div>

            <!-- Stok Kritis (< 5) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="sf-card sf-stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="sf-stat-label">Stok Kritis (&lt; 5 pcs)</span>
                        <div class="btn-sf-icon" style="width: 34px; height: 34px; background: <?= $totalKritis > 0 ? 'var(--sf-danger-bg)' : 'var(--sf-success-bg)' ?>; border-color: <?= $totalKritis > 0 ? '#FECACA' : '#BBF7D0' ?>;">
                            <i class="bi <?= $totalKritis > 0 ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success' ?>"></i>
                        </div>
                    </div>
                    <div class="sf-stat-value <?= $totalKritis > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $totalKritis ?> <span style="font-size: 0.95rem; font-weight: 500; color: var(--sf-text-muted);">item</span>
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <?php if ($totalKritis > 0): ?>
                            <span class="sf-stat-trend warning">
                                <i class="bi bi-clock-history"></i> Restock
                            </span>
                            <span class="text-muted ms-1" style="font-size: 0.75rem;">butuh perhatian</span>
                        <?php else: ?>
                            <span class="sf-stat-trend positive">
                                <i class="bi bi-shield-check"></i> Aman
                            </span>
                            <span class="text-muted ms-1" style="font-size: 0.75rem;">semua stok cukup</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Row: Chart Distribusi & Top Produk -->
        <div class="row g-4 mb-4">
            <!-- Left Chart (Storeflow Dual Bar Chart) -->
            <div class="col-12 col-lg-8">
                <div class="sf-card h-100">
                    <div class="sf-card-header">
                        <div>
                            <h5 class="sf-card-title">Distribusi Stok per Kategori</h5>
                            <small class="text-muted" style="font-size: 0.8rem;">Perbandingan stok fisik riil vs estimasi cadangan minimal</small>
                        </div>
                        <div class="d-none d-sm-block">
                            <span class="badge rounded-pill bg-light text-dark border px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                                <i class="bi bi-pie-chart me-1 text-success"></i> <?= $totalKategori ?> Kategori
                            </span>
                        </div>
                    </div>
                    <div style="height: 290px; position: relative;">
                        <canvas id="categoryStockChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right: Top Products with Progress (Image 2) -->
            <div class="col-12 col-lg-4">
                <div class="sf-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="sf-card-header">
                            <div>
                                <h5 class="sf-card-title">Top Produk Stok</h5>
                                <small class="text-muted" style="font-size: 0.8rem;">Stok terbanyak di gudang</small>
                            </div>
                            <a href="<?= BASE_URL ?>barang/index.php" class="text-muted small fw-semibold">Lihat Semua</a>
                        </div>
                        <div class="d-flex flex-column">
                            <?php if (!empty($topProducts)): ?>
                                <?php foreach ($topProducts as $tp): ?>
                                    <?php 
                                        $persen = round(($tp['stok'] / $maxStok) * 100);
                                        $thumb = !empty($tp['gambar']) && file_exists(__DIR__ . '/uploads/' . $tp['gambar']) 
                                            ? BASE_URL . 'uploads/' . htmlspecialchars($tp['gambar'])
                                            : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=150&q=80';
                                    ?>
                                    <div class="sf-product-item">
                                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($tp['nama_barang']) ?>" class="sf-product-thumb" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/package-placeholder.svg';">
                                        <div class="sf-product-info">
                                            <div class="sf-product-name" title="<?= htmlspecialchars($tp['nama_barang']) ?>"><?= htmlspecialchars($tp['nama_barang']) ?></div>
                                            <div class="sf-product-meta">Rp <?= number_format($tp['harga'], 0, ',', '.') ?> &bull; Stok: <?= number_format($tp['stok'], 0, ',', '.') ?> pcs</div>
                                            <div class="sf-progress-track">
                                                <div class="sf-progress-fill" style="width: <?= $persen ?>%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small text-center py-4">Belum ada data barang.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Inventory Alerts (Image 2 Mirror) & Recent Items Table -->
        <div class="row g-4">
            <!-- Inventory Alerts Widget -->
            <div class="col-12 col-lg-5">
                <div class="sf-card h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="sf-card-header">
                            <div>
                                <h5 class="sf-card-title">Inventory Alerts</h5>
                                <small class="text-danger fw-semibold" style="font-size: 0.8rem;">
                                    <?= $totalKritis ?> item butuh restock segera
                                </small>
                            </div>
                            <a href="<?= BASE_URL ?>barang/index.php?stok=kritis" class="text-muted small fw-semibold">Lihat semua</a>
                        </div>

                        <div class="d-flex flex-column">
                            <?php if (!empty($alertItems)): ?>
                                <?php foreach ($alertItems as $alert): ?>
                                    <?php 
                                        $isCritical = $alert['stok'] <= 2;
                                        $dotClass = $isCritical ? 'critical' : 'low';
                                        $badgeBg = $isCritical ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning';
                                        $badgeText = $isCritical ? 'Critical' : 'Low';
                                    ?>
                                    <div class="sf-alert-item">
                                        <div class="sf-alert-left">
                                            <span class="sf-alert-dot <?= $dotClass ?>" title="<?= $badgeText ?>"></span>
                                            <div class="overflow-hidden">
                                                <div class="sf-alert-name" title="<?= htmlspecialchars($alert['nama_barang']) ?>">
                                                    <?= htmlspecialchars($alert['nama_barang']) ?>
                                                </div>
                                                <div class="sf-alert-category">
                                                    <?= htmlspecialchars($alert['nama_kategori']) ?> &bull; <?= htmlspecialchars($alert['nama_supplier']) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sf-alert-right">
                                            <div class="sf-alert-qty <?= $isCritical ? 'text-danger' : 'text-warning' ?>">
                                                <?= $alert['stok'] ?> unit left
                                            </div>
                                            <span class="badge <?= $badgeBg ?> sf-alert-badge"><?= $badgeText ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-shield-check text-success display-6 mb-2 d-block"></i>
                                    <span class="text-muted small">Semua stok barang dalam kondisi aman.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="<?= BASE_URL ?>barang/tambah.php" class="btn btn-sf-light w-100 justify-content-center">
                            <i class="bi bi-box-arrow-in-down me-1"></i> Tambah / Restock Barang
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Barang Table (Image 3 Modern Clean Table) -->
            <div class="col-12 col-lg-7">
                <div class="sf-card h-100 p-0 overflow-hidden d-flex flex-column justify-content-between">
                    <div>
                        <div class="sf-card-header px-4 pt-4 mb-2">
                            <div>
                                <h5 class="sf-card-title">Barang Baru Ditambahkan</h5>
                                <small class="text-muted" style="font-size: 0.8rem;">Daftar inventori terkini di gudang</small>
                            </div>
                            <a href="<?= BASE_URL ?>barang/index.php" class="text-muted small fw-semibold">Lihat Semua</a>
                        </div>

                        <div class="sf-table-responsive px-2">
                            <table class="table sf-table">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Kategori</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentBarang)): ?>
                                        <?php foreach ($recentBarang as $rb): ?>
                                            <?php 
                                                $isKritis = $rb['stok'] < 5;
                                                $thumb = !empty($rb['gambar']) && file_exists(__DIR__ . '/uploads/' . $rb['gambar'])
                                                    ? BASE_URL . 'uploads/' . htmlspecialchars($rb['gambar'])
                                                    : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=80&q=80';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($rb['nama_barang']) ?>" class="sf-thumb-sm" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/package-placeholder.svg';">
                                                        <div class="overflow-hidden" style="max-width: 160px;">
                                                            <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($rb['nama_barang']) ?>">
                                                                <?= htmlspecialchars($rb['nama_barang']) ?>
                                                            </div>
                                                            <small class="text-muted">#BRG-<?= str_pad($rb['id_barang'], 3, '0', STR_PAD_LEFT) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($rb['nama_kategori']) ?></span></td>
                                                <td class="text-end fw-semibold text-nowrap">Rp <?= number_format($rb['harga'], 0, ',', '.') ?></td>
                                                <td class="text-center fw-bold text-nowrap <?= $isKritis ? 'text-danger' : '' ?>">
                                                    <?= number_format($rb['stok'], 0, ',', '.') ?>
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <?php if ($isKritis): ?>
                                                        <span class="badge-soft-danger">Kritis</span>
                                                    <?php else: ?>
                                                        <span class="badge-soft-success">Aman</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada barang di database.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="p-3 border-top text-center bg-light bg-opacity-50">
                        <small class="text-muted">Menampilkan data inventori sembako riil</small>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- /.sf-body-container -->

    <!-- Chart.js Live Data Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('categoryStockChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chartLabels) ?>,
                    datasets: [
                        {
                            label: 'Stok Fisik Saat Ini (Unit)',
                            data: <?= json_encode($chartStokData) ?>,
                            backgroundColor: '#0F382C', // Deep forest green
                            borderRadius: 6,
                            barPercentage: 0.55,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Target Cadangan (Unit)',
                            data: <?= json_encode($chartTargetData) ?>,
                            backgroundColor: '#A7F3D0', // Soft mint
                            borderRadius: 6,
                            barPercentage: 0.55,
                            categoryPercentage: 0.7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 15,
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 11,
                                    weight: 600
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: "'Plus Jakarta Sans'", size: 12, weight: 'bold' },
                            bodyFont: { family: "'Plus Jakarta Sans'", size: 11 },
                            padding: 10,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: "'Plus Jakarta Sans'", size: 11, weight: 600 },
                                color: '#64748B'
                            }
                        },
                        y: {
                            border: { dash: [4, 4] },
                            grid: { color: '#F1F5F9' },
                            ticks: {
                                font: { family: "'Plus Jakarta Sans'", size: 11 },
                                color: '#94A3B8'
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
