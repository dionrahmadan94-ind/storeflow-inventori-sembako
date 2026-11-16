<?php
/**
 * transaksi/penjualan.php
 * Halaman penjualan – mengurangi stok barang berdasarkan jumlah yang dibeli.
 *
 * UI sederhana: pengguna dapat menambahkan baris barang, pilih barang
 * dari dropdown (nama + kode) dan mengisi kuantitas yang dibeli.
 * Pada submit, stok akan dikurangi secara atomik (transaction).
 */

require_once __DIR__ . '/../includes/header.php';   // session, $pdo, BASE_URL
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = $_POST['items'] ?? [];
    $errors = [];
    $successMsg = '';

    if (empty($items)) {
        $errors[] = 'Tidak ada barang yang dipilih.';
    } else {
        try {
            $pdo->beginTransaction();
            foreach ($items as $idx => $data) {
                $id  = (int)($data['id'] ?? 0);
                $qty = max(1, (int)($data['qty'] ?? 1));
                if ($id <= 0) {
                    $errors[] = "Item #{$idx} memiliki ID tidak valid.";
                    continue;
                }
                // Ambil stok saat ini dengan lock row
                $stmt = $pdo->prepare('SELECT stok, nama_barang FROM barang WHERE id_barang = :id FOR UPDATE');
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if (!$row) {
                    $errors[] = "Barang dengan ID {$id} tidak ditemukan.";
                    continue;
                }
                $current = (int)$row['stok'];
                $newStock = max(0, $current - $qty);
                // Update stok
                $upd = $pdo->prepare('UPDATE barang SET stok = :new WHERE id_barang = :id');
                $upd->execute([':new' => $newStock, ':id' => $id]);
            }
            if (empty($errors)) {
                $pdo->commit();
                $successMsg = 'Stok berhasil dikurangi sesuai penjualan.';
            } else {
                $pdo->rollBack();
            }
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
    // Redirect dengan pesan supaya refresh tidak mengulang POST
    if (!empty($successMsg)) {
        header('Location: ' . BASE_URL . 'transaksi/penjualan.php?success=' . urlencode($successMsg));
        exit;
    }
    if (!empty($errors)) {
        $errorStr = implode(' | ', $errors);
        header('Location: ' . BASE_URL . 'transaksi/penjualan.php?error=' . urlencode($errorStr));
        exit;
    }
}

// Jika GET, tampilkan form
$q = trim($_GET['q'] ?? '');
// Ambil daftar barang untuk dropdown (id, nama, stok)
$stmtBarang = $pdo->query('SELECT id_barang, nama_barang, stok FROM barang ORDER BY nama_barang ASC');
$barangList = $stmtBarang->fetchAll();
?>
<main class="sf-main-content">
    <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
    <div class="sf-body-container">
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success rounded-4 py-2 px-3 mb-4" style="font-size:0.88rem;">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['success']) ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger rounded-4 py-2 px-3 mb-4" style="font-size:0.88rem;">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div><?= htmlspecialchars($_GET['error']) ?></div>
            </div>
        <?php endif; ?>
        <div class="sf-card p-4">
            <h3 class="fw-bold mb-3">Penjualan – Kurangi Stok Barang</h3>
            <form method="POST" id="penjualanForm">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Stok Tersedia</th>
                            <th>Kuantitas Dibeli</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsContainer">
                        <!-- Baris pertama (template) -->
                        <tr class="item-row">
                            <td>
                                <select name="items[0][id]" class="form-select" required>
                                    <option value="">Pilih barang</option>
                                    <?php foreach ($barangList as $b): ?>
                                        <option value="<?= $b['id_barang'] ?>" data-stok="<?= $b['stok'] ?>">
                                            <?= htmlspecialchars($b['nama_barang']) ?> (ID: <?= $b['id_barang'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="stock-available">-</td>
                            <td><input type="number" name="items[0][qty]" class="form-control" value="1" min="1" required></td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Hapus"><i class="bi bi-x"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary" id="addRowBtn"><i class="bi bi-plus-lg"></i> Tambah Barang</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-cart"></i> Simpan Penjualan</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
    // Update stok tersedia saat pilih barang
    function updateStockDisplay(selectElem) {
        const row = selectElem.closest('tr');
        const stockCell = row.querySelector('.stock-available');
        const stok = selectElem.options[selectElem.selectedIndex].dataset.stok || '-';
        stockCell.textContent = stok;
    }
    document.querySelectorAll('select[name^="items"]').forEach(select => {
        select.addEventListener('change', () => updateStockDisplay(select));
    });

    // Tambah baris baru
    document.getElementById('addRowBtn').addEventListener('click', () => {
        const container = document.getElementById('itemsContainer');
        const index = container.querySelectorAll('.item-row').length;
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        newRow.innerHTML = `
            <td>
                <select name="items[${index}][id]" class="form-select" required>
                    <option value="">Pilih barang</option>
                    <?php foreach ($barangList as $b): ?>
                        <option value="<?= $b['id_barang'] ?>" data-stok="<?= $b['stok'] ?>">
                            <?= htmlspecialchars($b['nama_barang']) ?> (ID: <?= $b['id_barang'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="stock-available">-</td>
            <td><input type="number" name="items[${index}][qty]" class="form-control" value="1" min="1" required></td>
            <td><button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Hapus"><i class="bi bi-x"></i></button></td>
        `;
        container.appendChild(newRow);
        // Attach events for new select
        const select = newRow.querySelector('select');
        select.addEventListener('change', () => updateStockDisplay(select));
        // Attach remove handler
        newRow.querySelector('.btn-remove-row').addEventListener('click', () => newRow.remove());
    });

    // Hapus baris
    document.querySelectorAll('.btn-remove-row').forEach(btn => {
        btn.addEventListener('click', e => {
            e.target.closest('tr').remove();
        });
    });
</script>
<?php
require_once __DIR__ . '/../includes/footer.php';
?>
