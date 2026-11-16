<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('ID barang tidak valid!'));
    exit;
}

// Ambil data barang dari database
$stmt = $pdo->prepare("SELECT * FROM barang WHERE id_barang = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('Data barang tidak ditemukan!'));
    exit;
}

// Data valid, baru inisialisasi header & template
$page_title = 'Ubah Data Barang';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Ambil data kategori dan supplier untuk dropdown
$kategoriList = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
$supplierList = $pdo->query("SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier ASC")->fetchAll();

$thumb = !empty($item['gambar']) && file_exists(__DIR__ . '/../uploads/' . $item['gambar'])
    ? BASE_URL . 'uploads/' . htmlspecialchars($item['gambar'])
    : 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=300&q=80';
?>

<main class="sf-main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="sf-body-container">
        <!-- Breadcrumb & Back Button -->
        <div class="mb-4">
            <a href="<?= BASE_URL ?>barang/index.php" class="text-muted text-decoration-none small fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Barang
            </a>
            <h3 class="fw-bold mt-2 mb-1">Ubah Data Barang</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Perbarui rincian informasi item sembako pada database inventori.
            </p>
        </div>

        <div class="row">
            <div class="col-12 col-xl-9">
                <div class="sf-card p-4">
                    <form action="<?= BASE_URL ?>barang/proses_ubah.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_barang" value="<?= $item['id_barang'] ?>">
                        
                        <div class="row g-4">
                            <!-- Left Column: Form Info -->
                            <div class="col-12 col-md-7">
                                <!-- Nama Barang -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_barang" class="form-control sf-input" value="<?= htmlspecialchars($item['nama_barang']) ?>" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Kategori Dropdown -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Kategori <span class="text-danger">*</span></label>
                                        <select name="id_kategori" class="form-select sf-input" required>
                                            <?php foreach ($kategoriList as $k): ?>
                                                <option value="<?= $k['id_kategori'] ?>" <?= ($item['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Supplier Dropdown -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Supplier <span class="text-danger">*</span></label>
                                        <select name="id_supplier" class="form-select sf-input" required>
                                            <?php foreach ($supplierList as $s): ?>
                                                <option value="<?= $s['id_supplier'] ?>" <?= ($item['id_supplier'] == $s['id_supplier']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($s['nama_supplier']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Stok Fisik -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-4 text-muted">
                                                <i class="bi bi-box"></i>
                                            </span>
                                            <input type="number" name="stok" class="form-control sf-input border-start-0 rounded-end-4" value="<?= $item['stok'] ?>" min="0" required>
                                        </div>
                                    </div>

                                    <!-- Harga Satuan -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-4 fw-bold text-muted">
                                                Rp
                                            </span>
                                            <input type="number" name="harga" class="form-control sf-input border-start-0 rounded-end-4" value="<?= (int)$item['harga'] ?>" min="0" step="100" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Upload Gambar Preview -->
                            <div class="col-12 col-md-5">
                                <label class="form-label fw-bold small text-muted">Foto Barang Saat Ini</label>
                                
                                <div class="p-3 border rounded-4 text-center bg-light mb-2">
                                    <div id="imagePreviewContainer" class="mb-3">
                                        <img id="imagePreview" src="<?= $thumb ?>" alt="Preview" class="img-fluid rounded-3 shadow-sm" style="max-height: 180px; object-fit: cover;">
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.75rem;">
                                        Pilih file baru jika ingin mengganti foto (Maksimal 2 MB).
                                    </div>
                                </div>

                                <input type="file" name="gambar" id="gambarInput" class="form-control sf-input" accept="image/jpeg,image/png,image/jpg">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="<?= BASE_URL ?>barang/index.php" class="btn btn-sf-light">Batal</a>
                            <button type="submit" class="btn btn-sf-primary">
                                <i class="bi bi-check-lg"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- /.sf-body-container -->

    <script>
        const gambarInput = document.getElementById('gambarInput');
        const imagePreview = document.getElementById('imagePreview');

        gambarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2 MB!');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
