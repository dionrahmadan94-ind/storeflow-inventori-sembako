<?php
$page_title = 'Kelola Pengguna';
$active_menu = 'user';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';

// Proteksi server-side: Hanya Admin yang dapat mengakses halaman ini!
require_role('admin');

// Parameter pencarian (FR-07: Pencarian user berdasarkan nama/username)
$q = trim($_GET['q'] ?? '');

$whereSql = "";
$params = [];
if ($q !== '') {
    $whereSql = "WHERE (nama LIKE :q1 OR username LIKE :q2)";
    $params[':q1'] = "%{$q}%";
    $params[':q2'] = "%{$q}%";
}

// Ambil daftar pengguna dari database
$sql = "SELECT id_user, nama, username, role, created_at FROM users {$whereSql} ORDER BY id_user ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$userList = $stmt->fetchAll();
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
                    <h3 class="fw-bold mb-0">Kelola Akun Pengguna</h3>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                        <?= count($userList) ?> Akun <?= $q !== '' ? 'Ditemukan' : 'Terdaftar' ?>
                    </span>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.88rem;">
                    Atur hak akses akun sistem inventori (Role Administrator & Petugas Gudang).
                </p>
            </div>
            
            <button type="button" class="btn btn-sf-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="bi bi-person-plus"></i>
                <span>Tambah Pengguna</span>
            </button>
        </div>

        <!-- Filter & Search Toolbar (Sesuai Gaya Data Barang) -->
        <div class="sf-card mb-4 p-3">
            <form action="" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="sf-search-box w-100">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" placeholder="Cari nama pengguna atau username..." value="<?= htmlspecialchars($q) ?>">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sf-primary justify-content-center" style="height: 40px; padding: 0 20px;">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <?php if ($q !== ''): ?>
                        <a href="<?= BASE_URL ?>user/index.php" class="btn btn-sf-light" title="Reset pencarian" style="height: 40px; padding: 0 14px;">
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
                            <th>Pengguna</th>
                            <th>Username</th>
                            <th class="text-center">Role / Hak Akses</th>
                            <th>Tanggal Registrasi</th>
                            <th class="text-end" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userList)): ?>
                            <?php foreach ($userList as $idx => $u): ?>
                                <?php 
                                    $isSelf = ($u['id_user'] == $currentUser['id_user']);
                                ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="sf-avatar" style="width: 36px; height: 36px; font-size: 0.82rem;">
                                                <?= strtoupper(substr($u['nama'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['nama']) ?></div>
                                                <?php if ($isSelf): ?>
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.68rem;">Akun Anda</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <code class="text-dark bg-light px-2 py-1 rounded">@<?= htmlspecialchars($u['username']) ?></code>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge bg-dark rounded-pill px-3 py-1 fw-bold">
                                                <i class="bi bi-shield-check me-1 text-warning"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-bold">
                                                <i class="bi bi-person-badge me-1 text-primary"></i> Petugas
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-nowrap" style="font-size: 0.82rem;">
                                        <?= date('d M Y, H:i', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn-sf-icon btn-edit-user" title="Ubah User" data-item="<?= htmlspecialchars(json_encode([
                                                'id' => $u['id_user'],
                                                'nama' => $u['nama'],
                                                'username' => $u['username'],
                                                'role' => $u['role']
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($isSelf): ?>
                                                <!-- Admin dilarang menghapus akunnya sendiri (FR-06) -->
                                                <button type="button" class="btn-sf-icon" disabled title="Tidak dapat menghapus akun sendiri" style="opacity: 0.4; cursor: not-allowed;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn-sf-icon danger btn-del-user" title="Hapus User" data-id="<?= $u['id_user'] ?>" data-nama="<?= htmlspecialchars($u['nama'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 text-muted"></i>
                                    Tidak ada pengguna yang cocok dengan kata kunci pencarian.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- /.sf-body-container -->

    <!-- Modal Tambah User -->
    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>user/proses_tambah.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control sf-input" placeholder="Misal: John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control sf-input" placeholder="username unik tanpa spasi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control sf-input" placeholder="Minimal 6 karakter" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Role / Peran <span class="text-danger">*</span></label>
                            <select name="role" class="form-select sf-input" required>
                                <option value="petugas">Petugas Gudang (Operasional)</option>
                                <option value="admin">Administrator (Akses Penuh)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-sf-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sf-primary">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ubah User -->
    <div class="modal fade" id="modalUbahUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold">Ubah Data Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>user/proses_ubah.php" method="POST">
                    <input type="hidden" name="id_user" id="editUserId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="editUserNama" class="form-control sf-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="editUserUsername" class="form-control sf-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="form-control sf-input" placeholder="Kosongkan bila tidak ingin diganti" minlength="6">
                            <small class="text-muted" style="font-size: 0.75rem;">Biarkan kosong jika tidak ingin mengganti password.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Role / Peran <span class="text-danger">*</span></label>
                            <select name="role" id="editUserRole" class="form-select sf-input" required>
                                <option value="petugas">Petugas Gudang</option>
                                <option value="admin">Administrator</option>
                            </select>
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
        let modalUbahUser = null;

        function editUser(u) {
            document.getElementById('editUserId').value = u.id;
            document.getElementById('editUserNama').value = u.nama;
            document.getElementById('editUserUsername').value = u.username;
            document.getElementById('editUserRole').value = u.role;
            if (!modalUbahUser) {
                modalUbahUser = new bootstrap.Modal(document.getElementById('modalUbahUser'));
            }
            modalUbahUser.show();
        }

        function deleteUser(id, nama) {
            if (confirm(`Yakin ingin menghapus pengguna "${nama}"?`)) {
                window.location.href = `<?= BASE_URL ?>user/hapus.php?id=${id}`;
            }
        }
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
