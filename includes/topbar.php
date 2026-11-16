<?php
// Topbar Component (Storeflow Design)
$user = $_SESSION['user'] ?? ['nama' => 'Admin Gudang', 'role' => 'admin'];

// Query data notifikasi stok kritis (< 5 unit)
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/koneksi.php';
}

$notifCount = 0;
$notifKritisList = [];

if (isset($pdo)) {
    try {
        $stmtNotifCount = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok < 5");
        $notifCount = (int) $stmtNotifCount->fetchColumn();

        $stmtNotifList = $pdo->query("
            SELECT b.id_barang, b.nama_barang, b.stok, b.harga, b.gambar, k.nama_kategori
            FROM barang b
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            WHERE b.stok < 5
            ORDER BY b.stok ASC, b.id_barang DESC
            LIMIT 5
        ");
        $notifKritisList = $stmtNotifList->fetchAll();
    } catch (Throwable $e) {
        $notifCount = 0;
        $notifKritisList = [];
    }
}
?>
<header class="sf-topbar">
    <!-- Left: Page Breadcrumb / Mobile Toggle -->
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sf-icon d-lg-none" id="sidebarToggle" aria-label="Toggle menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div class="d-none d-md-flex align-items-center gap-2 text-muted fw-semibold" style="font-size: 0.88rem;">
            <span>Sistem Inventori</span>
            <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
            <span class="text-dark fw-bold"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></span>
        </div>
    </div>

    <!-- Center: Quick Search Bar (Disembunyikan pada tab data barang, kategori, supplier, dan user) -->
    <?php if (!in_array($active_menu ?? '', ['barang', 'kategori', 'supplier', 'user'])): ?>
    <div class="sf-search-box d-none d-sm-block">
        <i class="bi bi-search search-icon"></i>
        <form action="<?= BASE_URL ?>barang/index.php" method="GET" class="m-0">
            <input type="text" name="q" placeholder="Cari nama barang, kategori..." autocomplete="off">
        </form>
    </div>
    <?php endif; ?>

    <!-- Right: Actions & User Profile -->
    <div class="sf-topbar-actions">
        <!-- Notification Bell & Dropdown -->
        <div class="dropdown">
            <button class="btn-sf-icon position-relative border-0" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Pemberitahuan Stok" aria-label="Pemberitahuan Stok" style="background: #fff; border: 1px solid var(--sf-border-strong) !important;">
                <i class="bi bi-bell"></i>
                <?php if ($notifCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem; padding: 0.22em 0.45em; transform: translate(-25%, -20%) !important;">
                        <?= $notifCount > 99 ? '99+' : $notifCount ?>
                        <span class="visually-hidden">peringatan stok kritis</span>
                    </span>
                <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 sf-notif-menu mt-2" aria-labelledby="notifDropdown">
                <!-- Header -->
                <div class="sf-notif-header d-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="sf-notif-header-title">Pemberitahuan Stok</span>
                            <?php if ($notifCount > 0): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem; font-weight: 700;">
                                    <?= $notifCount ?> Kritis
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem; font-weight: 700;">
                                    Aman
                                </span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted d-block mt-0.5" style="font-size: 0.74rem;">Batas minimum operasional: &lt; 5 unit</small>
                    </div>
                </div>

                <!-- List Items -->
                <div class="sf-notif-list">
                    <?php if (!empty($notifKritisList)): ?>
                        <?php foreach ($notifKritisList as $item): ?>
                            <?php 
                                $isHabis = ($item['stok'] == 0);
                                $isCritical = ($item['stok'] <= 2);
                                $badgeCls = $isHabis 
                                    ? 'bg-danger text-white' 
                                    : ($isCritical 
                                        ? 'bg-danger-subtle text-danger border border-danger-subtle' 
                                        : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle');
                                $badgeLbl = $isHabis ? 'Habis (0)' : 'Sisa ' . $item['stok'] . ' unit';
                                $imgSrc = !empty($item['gambar']) && file_exists(__DIR__ . '/../uploads/' . $item['gambar'])
                                    ? BASE_URL . 'uploads/' . htmlspecialchars($item['gambar'])
                                    : BASE_URL . 'assets/img/package-placeholder.svg';
                            ?>
                            <a href="<?= BASE_URL ?>barang/ubah.php?id=<?= $item['id_barang'] ?>" class="sf-notif-row">
                                <div class="sf-notif-thumb-wrap">
                                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>" class="sf-notif-thumb" onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/package-placeholder.svg';">
                                    <span class="sf-notif-dot <?= $isCritical ? 'critical' : 'warning' ?>"></span>
                                </div>
                                <div class="sf-notif-content">
                                    <div class="sf-notif-title" title="<?= htmlspecialchars($item['nama_barang']) ?>">
                                        <?= htmlspecialchars($item['nama_barang']) ?>
                                    </div>
                                    <div class="sf-notif-meta">
                                        <span class="text-truncate"><?= htmlspecialchars($item['nama_kategori'] ?? 'Umum') ?></span>
                                        <span class="sf-bullet">&bull;</span>
                                        <span>Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="sf-notif-action">
                                    <span class="badge <?= $badgeCls ?> rounded-pill sf-notif-badge">
                                        <?= $badgeLbl ?>
                                    </span>
                                    <span class="sf-notif-link">
                                        Restock <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="sf-notif-empty text-center">
                            <div class="sf-notif-empty-icon mb-2">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="fw-bold text-dark" style="font-size: 0.88rem;">Semua Stok Barang Aman</div>
                            <p class="text-muted mb-0 mt-1" style="font-size: 0.78rem;">Tidak ada komoditas yang berada di bawah 5 unit.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="sf-notif-footer">
                    <a href="<?= BASE_URL ?>barang/index.php?stok=kritis" class="btn btn-sf-light btn-sm w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-funnel"></i>
                        <span>Lihat Semua Barang Kritis (<?= $notifCount ?>)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile Pill -->
        <div class="sf-user-profile dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="sf-avatar">
                    <span><?= strtoupper(substr($user['nama'], 0, 2)) ?></span>
                </div>
                <div class="sf-user-info d-none d-lg-flex">
                    <span class="sf-user-name"><?= htmlspecialchars($user['nama']) ?></span>
                    <span class="sf-user-role"><?= htmlspecialchars($user['role']) ?></span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 mt-2 p-2" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom mb-2">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($user['nama']) ?></div>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;"><?= htmlspecialchars($user['role']) ?></small>
                </li>
                <li>
                    <a class="dropdown-item rounded-3 py-2 text-danger" href="<?= BASE_URL ?>auth/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

