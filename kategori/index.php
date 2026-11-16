<?php
$page_title = 'Data Kategori';
$active_menu = 'kategori';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Parameter pencarian
$q = trim($_GET['q'] ?? '');

$whereSql = "";
$params = [];
if ($q !== '') {
    $whereSql = "WHERE k.nama_kategori LIKE :keyword";
    $params[':keyword'] = "%{$q}%";
}

// Ambil daftar kategori beserta total barang terkait (Prepared Statement)
$sql = "
    SELECT 
        k.id_kategori,
        k.nama_kategori,
        COUNT(b.id_barang) as total_barang
    FROM kategori k
    LEFT JOIN barang b ON k.id_kategori = b.id_kategori
    {$whereSql}
    GROUP BY k.id_kategori, k.nama_kategori
    ORDER BY k.id_kategori ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kategoriList = $stmt->fetchAll();
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
                    <h3 class="fw-bold mb-0">Kategori Barang</h3>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                        <?= count($kategoriList) ?> Kategori <?= $q !== '' ? 'Ditemukan' : 'Terdaftar' ?>
                    </span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Pengelompokan komoditas sembako untuk memudahkan pengorganisasian stok dan laporan.
                </p>
            </div>
            
            <?php if (is_admin()): ?>
                <button type="button" class="btn btn-sf-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Kategori</span>
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
                        <input type="text" name="q" placeholder="Cari nama kategori sembako..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sf-primary justify-content-center" style="height: 40px; padding: 0 20px;">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <?php if ($q !== ''): ?>
                        <a href="<?= BASE_URL ?>kategori/index.php" class="btn btn-sf-light" title="Reset pencarian" style="height: 40px; padding: 0 14px;">
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
                            <th>Nama Kategori</th>
                            <th class="text-center">Jumlah Barang Terkait</th>
                            <th class="text-center">Status Pemakaian</th>
                            <?php if (is_admin()): ?>
                                <th class="text-end" style="width: 120px;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kategoriList)): ?>
                            <?php foreach ($kategoriList as $idx => $kat): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($kat['nama_kategori']) ?></div>
                                        <small class="text-muted">ID: #KAT-0<?= $kat['id_kategori'] ?></small>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <span class="badge-soft-success">
                                            <?= $kat['total_barang'] ?> Barang
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?php if ($kat['total_barang'] > 0): ?>
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
                                                <button type="button" class="btn-sf-icon btn-edit-kat" title="Ubah Kategori" data-id="<?= $kat['id_kategori'] ?>" data-nama="<?= htmlspecialchars($kat['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn-sf-icon danger btn-del-kat" title="Hapus Kategori" data-id="<?= $kat['id_kategori'] ?>" data-nama="<?= htmlspecialchars($kat['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>" data-total="<?= (int) $kat['total_barang'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                    Tidak ada kategori yang cocok dengan kata kunci pencarian.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- /.sf-body-container -->

    <?php if (is_admin()): ?>
    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>kategori/proses_tambah.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kategori" class="form-control sf-input" placeholder="Misal: Minuman Kemasan" required>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-sf-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sf-primary">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Kategori -->
    <div class="modal fade" id="modalUbahKategori" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Ubah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>kategori/proses_ubah.php" method="POST">
                    <input type="hidden" name="id_kategori" id="editKategoriId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kategori" id="editKategoriNama" class="form-control sf-input" required>
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
        let modalUbahKategori = null;

        function editKategori(id, nama) {
            document.getElementById('editKategoriId').value = id;
            document.getElementById('editKategoriNama').value = nama;
            if (!modalUbahKategori) {
                modalUbahKategori = new bootstrap.Modal(document.getElementById('modalUbahKategori'));
            }
            modalUbahKategori.show();
        }

        function deleteKategori(id, nama, totalBarang) {
            if (totalBarang > 0) {
                alert(`Penolakan Penghapusan:\nKategori "${nama}" tidak dapat dihapus karena masih digunakan oleh ${totalBarang} data barang.`);
                return;
            }
            if (confirm(`Yakin ingin menghapus kategori "${nama}"?`)) {
                window.location.href = `<?= BASE_URL ?>kategori/hapus.php?id=${id}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-edit-kat').forEach(btn => {
                btn.addEventListener('click', function () {
                    editKategori(this.getAttribute('data-id'), this.getAttribute('data-nama'));
                });
            });

            document.querySelectorAll('.btn-del-kat').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const total = parseInt(this.getAttribute('data-total') || '0', 10);
                    deleteKategori(id, nama, total);
                });
            });
        });
    </script>
    <?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
