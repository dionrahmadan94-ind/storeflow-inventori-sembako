<?php
/**
 * barang/kurangi.php
 * Reduces the stock of a barang (item) by a specified quantity.
 * Expected POST parameters:
 *   - id: int (id_barang)
 *   - qty: int (quantity to subtract, default 1)
 *
 * After updating, redirects back to barang/index.php with a success or error message.
 */
require_once __DIR__ . '/../includes/header.php'; // starts session, loads $pdo
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Allow both admin and petugas to reduce stock
$role = $_SESSION['user']['role'] ?? '';
if (!in_array($role, ['admin', 'petugas'])) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('Akses ditolak: hanya admin atau petugas yang dapat mengurangi stok.'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

if ($id <= 0) {
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode('ID barang tidak valid'));
    exit;
}

try {
    // Get current stock with row lock
    $stmt = $pdo->prepare('SELECT stok FROM barang WHERE id_barang = :id FOR UPDATE');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new Exception('Barang tidak ditemukan');
    }
    $current = (int)$row['stok'];
    // Validate that we are not reducing more than available
    if ($qty > $current) {
        throw new Exception("Stok tidak cukup. Stok tersedia: $current");
    }
    $newStock = $current - $qty; // never negative because of validation
    // Update stock
    $upd = $pdo->prepare('UPDATE barang SET stok = :new WHERE id_barang = :id');
    $upd->execute([':new' => $newStock, ':id' => $id]);
    $msg = "Stok berhasil dikurangi menjadi $newStock";
    header('Location: ' . BASE_URL . 'barang/index.php?success=' . urlencode($msg));
    exit;
} catch (Throwable $e) {
    $error = $e->getMessage();
    header('Location: ' . BASE_URL . 'barang/index.php?error=' . urlencode($error));
    exit;
}
?>