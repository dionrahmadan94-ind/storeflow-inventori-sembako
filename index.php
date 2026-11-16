<?php
require_once __DIR__ . '/config/app.php';

// Redirect ke dashboard jika sudah ada session login aktif, atau ke login jika belum
if (isset($_SESSION['user']['id_user'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
} else {
    header('Location: ' . BASE_URL . 'auth/login.php');
}
exit;

