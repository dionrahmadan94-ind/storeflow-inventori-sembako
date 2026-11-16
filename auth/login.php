<?php
require_once __DIR__ . '/../config/app.php';

// Jika sudah login, redirect langsung ke dashboard
if (isset($_SESSION['user']['id_user']) && !isset($_GET['preview'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Storeflow Inventori</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Storeflow Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<div class="sf-login-page">
    <!-- Left Hero (Storeflow Hero Section) -->
    <div class="sf-login-hero">
        <div class="sf-hero-content">
            <div class="sf-hero-brand">
                <div class="logo-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                        <path d="m3.3 7 8.7 5 8.7-5"/>
                        <path d="M12 22V12"/>
                    </svg>
                </div>
                <span class="fs-4 fw-bold">Storeflow</span>
            </div>
            <h1 class="sf-hero-title">Sistem Inventori Sembako Terpadu.</h1>
            <p class="sf-hero-desc">
                Pencatatan dan pengelolaan stok sembako kini lebih terpusat, terstruktur, dan akurat dengan sistem kontrol pergudangan yang andal.
            </p>
        </div>
    </div>

    <!-- Right Login Form -->
    <div class="sf-login-form-area">
        <div class="sf-login-card">
            <!-- Center Logo -->
            <div class="sf-login-logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                </svg>
            </div>
            
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-1">Masuk ke Storeflow</h3>
                <p class="text-muted" style="font-size: 0.9rem;">Sistem Inventori Barang Sembako</p>
            </div>

            <!-- Alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success rounded-4 py-2 px-3 d-flex align-items-center gap-2 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="<?= BASE_URL ?>auth/proses_login.php" method="POST" id="loginForm">
                <!-- Username Field -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3 text-muted">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" name="username" id="usernameInput" class="form-control sf-input border-start-0 rounded-end-4" placeholder="Masukkan username" required autocomplete="username">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-4 ps-3 text-muted">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" class="form-control sf-input border-start-0 border-end-0" placeholder="Masukkan password" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary border-start-0 border rounded-end-4 px-3 bg-white text-muted" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                        <label class="form-check-label text-muted small" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                    <span class="small text-muted">Akses Terenkripsi</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-sf-primary w-100 py-2 justify-content-center fw-bold rounded-pill mb-3" style="font-size: 0.95rem;">
                    Log In
                </button>
            </form>

            <!-- Quick Demo Credentials Pill (Bantuan Presentasi/Dosen) -->
            <div class="sf-demo-credentials text-center">
                <span class="fw-bold text-dark d-block mb-2">
                    <i class="bi bi-key-fill text-muted me-1"></i> Akun Pengujian (Kredensial):
                </span>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;" onclick="setDemo('admin', 'admin123')">
                        <i class="bi bi-shield-lock me-1"></i> Admin (admin123)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;" onclick="setDemo('petugas', 'petugas123')">
                        <i class="bi bi-person-badge me-1"></i> Petugas (petugas123)
                    </button>
                </div>
            </div>

            <!-- Footer info -->
            <div class="text-center mt-4">
                <small class="text-muted" style="font-size: 0.78rem;">
                    &copy; 2026 Storeflow Sembako. Tugas Pemrograman Web.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        eyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Auto-fill demo credentials
    function setDemo(user, pass) {
        document.getElementById('usernameInput').value = user;
        document.getElementById('passwordInput').value = pass;
    }
</script>
</body>
</html>
