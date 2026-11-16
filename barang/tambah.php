<?php
$page_title = 'Tambah Barang Baru';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Ambil data kategori dan supplier untuk dropdown
$kategoriList = $pdo->query("SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC")->fetchAll();
$supplierList = $pdo->query("SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier ASC")->fetchAll();
?>

<main class="sf-main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

    <div class="sf-body-container">
        <!-- Breadcrumb & Back Button -->
        <div class="mb-4">
            <a href="<?= BASE_URL ?>barang/index.php" class="text-muted text-decoration-none small fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Barang
            </a>
            <h3 class="fw-bold mt-2 mb-1">Tambah Barang Baru</h3>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">
                Daftarkan item sembako baru ke dalam database inventori gudang.
            </p>
        </div>

        <div class="row">
            <div class="col-12 col-xl-9">
                <div class="sf-card p-4">
                    <form action="<?= BASE_URL ?>barang/proses_tambah.php" method="POST" enctype="multipart/form-data" id="formBarang">
                        
                        <div class="row g-4">
                            <!-- Left Column: Form Info -->
                            <div class="col-12 col-md-7">
                                <!-- Nama Barang -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Nama Barang <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_barang" class="form-control sf-input" placeholder="Contoh: Beras Ramos Premium 5kg" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Kategori Dropdown -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Kategori <span class="text-danger">*</span></label>
                                        <select name="id_kategori" class="form-select sf-input" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php foreach ($kategoriList as $k): ?>
                                                <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Supplier Dropdown -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Supplier <span class="text-danger">*</span></label>
                                        <select name="id_supplier" class="form-select sf-input" required>
                                            <option value="">Pilih Supplier</option>
                                            <?php foreach ($supplierList as $s): ?>
                                                <option value="<?= $s['id_supplier'] ?>"><?= htmlspecialchars($s['nama_supplier']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Stok Fisik -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Jumlah Stok Awal <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-4 text-muted">
                                                <i class="bi bi-box"></i>
                                            </span>
                                            <input type="number" name="stok" class="form-control sf-input border-start-0 rounded-end-4" placeholder="0" min="0" required>
                                        </div>
                                    </div>

                                    <!-- Harga Satuan -->
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label fw-bold small text-muted">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 rounded-start-4 fw-bold text-muted">
                                                Rp
                                            </span>
                                            <input type="number" name="harga" class="form-control sf-input border-start-0 rounded-end-4" placeholder="0" min="0" step="100" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Upload Gambar Preview -->
                            <div class="col-12 col-md-5">
                                <label class="form-label fw-bold small text-muted">Foto Barang</label>
                                
                                <div class="p-3 border rounded-4 text-center bg-light mb-2">
                                    <!-- Preview Box -->
                                    <div id="imagePreviewContainer" class="d-none mb-3">
                                        <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded-3 shadow-sm" style="max-height: 180px; object-fit: cover;">
                                    </div>
                                    <div id="uploadPlaceholder">
                                        <i class="bi bi-cloud-arrow-up display-5 text-muted mb-2 d-block"></i>
                                        <div class="fw-semibold text-dark mb-1" style="font-size: 0.88rem;">Pilih foto barang</div>
                                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                            Format JPG, JPEG, PNG (Maksimal 2 MB)
                                        </p>
                                    </div>
                                </div>

                                <input type="file" name="gambar" id="gambarInput" class="form-control sf-input" accept="image/jpeg,image/png,image/jpg">
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="<?= BASE_URL ?>barang/index.php" class="btn btn-sf-light">Batal</a>
                            <button type="submit" class="btn btn-sf-primary">
                                <i class="bi bi-check-lg"></i> Simpan Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- /.sf-body-container -->

    <script>
        // Live Preview Image Upload
        const gambarInput = document.getElementById('gambarInput');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');

        gambarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Check size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file maksimal 2 MB!');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.classList.remove('d-none');
                    uploadPlaceholder.classList.add('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '#';
                imagePreviewContainer.classList.add('d-none');
                uploadPlaceholder.classList.remove('d-none');
            }
        });
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
