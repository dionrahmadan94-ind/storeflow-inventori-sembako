<?php
$page_title = 'Data Supplier';
$active_menu = 'supplier';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Parameter pencarian
$q = trim($_GET['q'] ?? '');

$whereSql = "";
$params = [];
if ($q !== '') {
    $whereSql = "WHERE (s.nama_supplier LIKE :q1 OR s.kontak LIKE :q2 OR s.alamat LIKE :q3)";
    $params[':q1'] = "%{$q}%";
    $params[':q2'] = "%{$q}%";
    $params[':q3'] = "%{$q}%";
}

// Ambil data supplier dari database beserta jumlah barang yang disuplai (Prepared Statement)
$sql = "
    SELECT 
        s.id_supplier,
        s.nama_supplier,
        s.kontak,
        s.alamat,
        COUNT(b.id_barang) as total_barang
    FROM supplier s
    LEFT JOIN barang b ON s.id_supplier = b.id_supplier
    {$whereSql}
    GROUP BY s.id_supplier, s.nama_supplier, s.kontak, s.alamat
    ORDER BY s.id_supplier ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$supplierList = $stmt->fetchAll();
?>

<main class="sf-main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="sf-body-container">
        <!-- Notifikasi -->
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
                    <h3 class="fw-bold mb-0">Supplier / Pemasok</h3>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                        <?= count($supplierList) ?> Mitra <?= $q !== '' ? 'Ditemukan' : 'Terdaftar' ?>
                    </span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Daftar distributor resmi dan penyuplai komoditas sembako ke gudang.
                </p>
            </div>
            
            <?php if (is_admin()): ?>
                <button type="button" class="btn btn-sf-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Supplier</span>
                </button>
            <?php else: ?>
                <span class="badge bg-light text-muted border rounded-pill px-3 py-2">
                    <i class="bi bi-lock me-1"></i> Mode Hanya Lihat (Petugas)
                </span>
            <?php endif; ?>
        </div>

        <!-- Filter & Search Toolbar (Sesuai Gaya Data Barang) -->
        <div class="sf-card mb-4 p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="sf-search-box w-100">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" placeholder="Cari nama supplier, kontak, alamat..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sf-primary justify-content-center" style="height: 40px; padding: 0 20px;">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <?php if ($q !== ''): ?>
                        <a href="<?= BASE_URL ?>supplier/index.php" class="btn btn-sf-light" title="Reset pencarian" style="height: 40px; padding: 0 14px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="sf-card p-0 overflow-hidden mb-4">
            <div class="sf-table-responsive">
                <table class="table sf-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Nama Supplier</th>
                            <th>Kontak Telepon</th>
                            <th>Alamat Gudang / Kantor</th>
                            <th class="text-center">Barang Disuplai</th>
                            <th class="text-center">Status Pemakaian</th>
                            <?php if (is_admin()): ?>
                                <th class="text-end" style="width: 120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($supplierList)): ?>
                            <?php foreach ($supplierList as $idx => $sup): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($sup['nama_supplier']) ?></div>
                                        <small class="text-muted">ID: #SUP-0<?= $sup['id_supplier'] ?></small>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if (!empty($sup['kontak'])): ?>
                                            <a href="tel:<?= htmlspecialchars($sup['kontak']) ?>" class="text-decoration-none fw-semibold">
                                                <i class="bi bi-telephone me-1 text-muted"></i> <?= htmlspecialchars($sup['kontak']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted" style="max-width: 320px;">
                                        <?php if (!empty($sup['alamat'])): ?>
                                            <i class="bi bi-geo-alt me-1 text-muted"></i> <?= htmlspecialchars($sup['alamat']) ?>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge-soft-success">
                                            <?= $sup['total_barang'] ?> Barang
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?php if ($sup['total_barang'] > 0): ?>
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-lock-fill me-1"></i> Terpakai
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-unlock-fill me-1"></i> Bebas Hapus
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (is_admin()): ?>
                                        <td class="text-end text-nowrap">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn-sf-icon btn-edit-sup" title="Ubah Supplier" data-item="<?= htmlspecialchars(json_encode([
                                                    'id' => $sup['id_supplier'],
                                                    'nama' => $sup['nama_supplier'],
                                                    'kontak' => $sup['kontak'] ?? '',
                                                    'alamat' => $sup['alamat'] ?? ''
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn-sf-icon danger btn-del-sup" title="Hapus Supplier" data-id="<?= $sup['id_supplier'] ?>" data-nama="<?= htmlspecialchars($sup['nama_supplier'], ENT_QUOTES, 'UTF-8') ?>" data-total="<?= (int) $sup['total_barang'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                    Tidak ada supplier yang cocok dengan kata kunci pencarian.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- /.sf-body-container -->

    <?php if (is_admin()): ?>
    <!-- Modal Tambah Supplier -->
    <div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Tambah Supplier Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>supplier/proses_tambah.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="nama_supplier" class="form-control sf-input" placeholder="Contoh: PT Sumber Pangan Nusantara" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Kontak / No Telepon</label>
                            <input type="text" name="kontak" class="form-control sf-input" placeholder="0812-xxxx-xxxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control sf-input" rows="3" placeholder="Alamat kantor atau gudang supplier..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-sf-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sf-primary">Simpan Supplier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Supplier -->
    <div class="modal fade" id="modalUbahSupplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Ubah Data Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>supplier/proses_ubah.php" method="POST">
                    <input type="hidden" name="id_supplier" id="editSupId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="nama_supplier" id="editSupNama" class="form-control sf-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Kontak / No Telepon</label>
                            <input type="text" name="kontak" id="editSupKontak" class="form-control sf-input">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Alamat Lengkap</label>
                            <textarea name="alamat" id="editSupAlamat" class="form-control sf-input" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-sf-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sf-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let modalUbahSupplier = null;

        function editSupplier(sup) {
            document.getElementById('editSupId').value = sup.id;
            document.getElementById('editSupNama').value = sup.nama;
            document.getElementById('editSupKontak').value = sup.kontak;
            document.getElementById('editSupAlamat').value = sup.alamat;
            if (!modalUbahSupplier) {
                modalUbahSupplier = new bootstrap.Modal(document.getElementById('modalUbahSupplier'));
            }
            modalUbahSupplier.show();
        }

        function deleteSupplier(id, nama, totalBarang) {
            if (totalBarang > 0) {
                alert(`Penolakan Penghapusan:\nSupplier "${nama}" tidak dapat dihapus karena masih menjadi pemasok untuk ${totalBarang} data barang.`);
                return;
            }
            if (confirm(`Yakin ingin menghapus supplier "${nama}"?`)) {
                window.location.href = `<?= BASE_URL ?>supplier/hapus.php?id=${id}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-edit-sup').forEach(btn => {
                btn.addEventListener('click', function () {
                    try {
                        const data = JSON.parse(this.getAttribute('data-item'));
                        editSupplier(data);
                    } catch (e) {
                        console.error('Error parsing supplier data:', e);
                    }
                });
            });

            document.querySelectorAll('.btn-del-sup').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const total = parseInt(this.getAttribute('data-total') || '0', 10);
                    deleteSupplier(id, nama, total);
                });
            });
        });
    </script>
    <?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
