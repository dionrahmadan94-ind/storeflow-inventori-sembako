<?php
// Sidebar Navigation (Clean Vector Design)
$userRole = $_SESSION['user']['role'] ?? 'petugas';
?>
<!-- Mobile Backdrop Overlay -->
<div class="sf-sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Sidebar -->
<aside class="sf-sidebar" id="sfSidebar">
    <!-- Brand -->
    <a href="<?= BASE_URL ?>dashboard.php" class="sf-brand">
        <div class="sf-brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                <path d="m3.3 7 8.7 5 8.7-5"/>
                <path d="M12 22V12"/>
            </svg>
        </div>
        <div class="sf-brand-text">
            Storeflow
        </div>
    </a>

    <!-- Scrollable Menu Wrapper -->
    <div class="sf-sidebar-menu-wrap">
        <!-- Menu Utama -->
        <div class="sf-menu-category">Menu</div>
        <ul class="sf-nav-list">
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>dashboard.php" class="sf-nav-link <?= ($active_menu === 'dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>barang/index.php" class="sf-nav-link <?= ($active_menu === 'barang') ? 'active' : '' ?>">
                    <i class="bi bi-boxes"></i>
                    <span>Data Barang</span>
                </a>
            </li>
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>kategori/index.php" class="sf-nav-link <?= ($active_menu === 'kategori') ? 'active' : '' ?>">
                    <i class="bi bi-tags"></i>
                    <span>Kategori</span>
                </a>
            </li>
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>supplier/index.php" class="sf-nav-link <?= ($active_menu === 'supplier') ? 'active' : '' ?>">
                    <i class="bi bi-truck"></i>
                    <span>Supplier</span>
                </a>
            </li>
        </ul>

        <!-- Menu Pengaturan -->
        <div class="sf-menu-category">Pengaturan</div>
        <ul class="sf-nav-list">
            <?php if ($userRole === 'admin'): ?>
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>user/index.php" class="sf-nav-link <?= ($active_menu === 'user') ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>Kelola User</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="sf-nav-item">
                <a href="<?= BASE_URL ?>auth/logout.php" class="sf-nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Bottom Status Widget -->
    <div class="sf-sidebar-footer">
        <div class="sf-role-card">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-shield-check text-success"></i>
                <span class="badge-role mb-0">Hak Akses</span>
            </div>
            <div class="text-white mb-1 fw-bold" style="font-size: 0.88rem;"><?= ucfirst($userRole) ?> Gudang</div>
            <p class="text-white-50 mb-0" style="font-size: 0.72rem; line-height: 1.35;">
                <?= $userRole === 'admin' ? 'Akses penuh seluruh modul master & transaksi.' : 'Akses operasional stok & ekspor data.' ?>
            </p>
        </div>
    </div>
</aside>
