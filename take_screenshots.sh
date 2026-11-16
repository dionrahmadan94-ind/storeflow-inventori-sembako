#!/usr/bin/env bash
set -e

BASE_DIR="/Users/macbookpro2019/Development/project/uts_dion"
DIR="$BASE_DIR/assets/screenshots"
mkdir -p "$DIR"

echo "Logging out first..."
npx agent-browser open "http://127.0.0.1:8088/auth/logout.php" || true
npx agent-browser wait 1000

echo "1. Capturing Login Page..."
npx agent-browser open "http://127.0.0.1:8088/auth/login.php"
npx agent-browser set viewport 1280 820
npx agent-browser wait 1000
npx agent-browser screenshot "$DIR/01_login.png"

echo "2. Logging in as Admin..."
npx agent-browser fill 'input[name="username"]' "admin"
npx agent-browser fill 'input[name="password"]' "admin123"
npx agent-browser click 'button[type="submit"]'
npx agent-browser wait 2000
npx agent-browser screenshot "$DIR/02_dashboard_admin.png"

echo "3. Capturing Notification Bell Dropdown..."
npx agent-browser click "#notifDropdown"
npx agent-browser wait 1000
npx agent-browser screenshot "$DIR/03_notifikasi_stok.png"

echo "4. Capturing Data Barang..."
npx agent-browser open "http://127.0.0.1:8088/barang/index.php"
npx agent-browser wait 1500
npx agent-browser screenshot "$DIR/04_data_barang.png"

echo "5. Capturing Detail Modal..."
npx agent-browser click '.btn-detail' || true
npx agent-browser wait 1000
npx agent-browser screenshot "$DIR/05_detail_barang_modal.png"
npx agent-browser press Escape || true
npx agent-browser wait 500

echo "6. Capturing Form Tambah Barang..."
npx agent-browser open "http://127.0.0.1:8088/barang/tambah.php"
npx agent-browser wait 1200
npx agent-browser screenshot "$DIR/06_form_tambah_barang.png"

echo "7. Capturing Data Kategori..."
npx agent-browser open "http://127.0.0.1:8088/kategori/index.php"
npx agent-browser wait 1200
npx agent-browser screenshot "$DIR/07_kategori.png"

echo "8. Capturing Data Supplier..."
npx agent-browser open "http://127.0.0.1:8088/supplier/index.php"
npx agent-browser wait 1200
npx agent-browser screenshot "$DIR/08_supplier.png"

echo "9. Capturing Kelola User..."
npx agent-browser open "http://127.0.0.1:8088/user/index.php"
npx agent-browser wait 1200
npx agent-browser screenshot "$DIR/09_kelola_user.png"

echo "10. Capturing Petugas Role View..."
npx agent-browser open "http://127.0.0.1:8088/auth/logout.php"
npx agent-browser wait 1000
npx agent-browser open "http://127.0.0.1:8088/auth/login.php"
npx agent-browser wait 800
npx agent-browser fill 'input[name="username"]' "petugas"
npx agent-browser fill 'input[name="password"]' "petugas123"
npx agent-browser click 'button[type="submit"]'
npx agent-browser wait 1500
npx agent-browser open "http://127.0.0.1:8088/barang/index.php"
npx agent-browser wait 1200
npx agent-browser screenshot "$DIR/10_petugas_view.png"

echo "All screenshots captured successfully!"
