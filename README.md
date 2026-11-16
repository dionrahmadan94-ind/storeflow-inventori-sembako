# Storeflow - Sistem Inventori Sembako Berbasis Web

Aplikasi pencatatan dan pengelolaan stok sembako berbasis web yang terpusat dengan antarmuka modern, kontrol akses berbasis peran (RBAC), grafik analitik interaktif, widget notifikasi stok kritis, serta ekspor data ke format CSV.

---

## 📌 Fitur Utama

- **Autentikasi Aman:** Password di-hash menggunakan algoritma BCRYPT (`password_hash()`) dan proteksi sesi server-side.
- **Role-Based Access Control (RBAC):**
  - **Administrator:** Akses penuh seluruh sistem (CRUD Barang + Hapus, CRUD Kategori, CRUD Supplier, CRUD User, Export CSV).
  - **Petugas Gudang:** Akses operasional harian (Lihat/Cari Barang, Tambah/Ubah Barang, Hanya Lihat Kategori & Supplier, Export CSV; dilarang menghapus barang dan tidak dapat mengakses modul User).
- **Dashboard Statistik:** Ringkasan KPI stok, grafik distribusi kategori (Chart.js), dan widget *Inventory Alerts* untuk stok kritis (< 5 unit).
- **Pengelolaan Data Barang:** Dilengkapi foto komoditas, pencarian real-time via PDO Prepared Statement, filter kategori/stok, pagination 10 item per halaman, dan modal pop-up detail barang.
- **Upload Gambar:** Dukungan format JPG, JPEG, PNG (maksimal 2 MB) dengan penamaan berkas unik otomatis (`uniqid()`).
- **Ekspor Data:** Fitur Export CSV berformat UTF-8 BOM yang dapat langsung dibuka dengan rapi di Microsoft Excel.
- **Pemberitahuan Stok Terpusat (Notification Bell):** Ikon lonceng dinamis pada topbar dengan *live badge* mendeteksi barang dengan sisa unit kritis (< 5 unit).

---

## 🛠️ Kebutuhan Sistem (Prerequisites)

- **PHP:** Versi 8.0 atau lebih baru (Disarankan PHP 8.2+)
- **Database:** MySQL 5.7+ atau MariaDB 10.4+
- **Ekstensi PHP:** `pdo_mysql`, `session`, `fileinfo`, `gd`
- **Web Server:** Apache (XAMPP / MAMP) atau PHP Built-in Server

---

## 🚀 Panduan Instalasi & Menjalankan

### Cara 1: Menggunakan XAMPP (Windows / macOS)
1. Salin/clone repository ini ke direktori web server:
   - Windows: `C:\xampp\htdocs\uts_dion\`
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/uts_dion/`
2. Jalankan service **Apache** dan **MySQL** melalui XAMPP Control Panel.
3. Buka browser dan akses `http://localhost/phpmyadmin/`.
4. Buat database baru dengan nama: `db_inventori`.
5. Pilih tab **Import**, pilih berkas `database.sql` di root proyek, lalu klik **Import / Go**.
6. Akses aplikasi melalui browser di: `http://localhost/uts_dion/`.

### Cara 2: Menggunakan PHP CLI Server (Pengujian Mandiri)
1. Buka Terminal pada root folder proyek:
   ```bash
   # Impor database
   mysql -u root -p db_inventori < database.sql

   # Jalankan server bawaan PHP
   php -S 127.0.0.1:8088
   ```
2. Buka browser pada alamat: `http://127.0.0.1:8088`.

---

## 🔑 Akun Pengujian (Test Credentials)

| No | Peran (Role) | Username | Password | Deskripsi Hak Akses |
|:--:|---|:--:|:--:|---|
| 1 | **Administrator** | `admin` | `admin123` | Akses penuh seluruh sistem |
| 2 | **Petugas Gudang** | `petugas` | `petugas123` | Akses operasional harian terbatas |

> *Tips: Pada halaman login tersedia tombol cepat **Akun Pengujian** untuk mengisi form login secara instan dalam 1 klik.*

---

## 📁 Struktur Berkas Proyek

```text
├── assets/             # Berkas CSS, JS, Gambar, dan Screenshot
├── auth/               # Modul autentikasi (login.php, logout.php)
├── barang/             # Modul CRUD Barang, kurangi stok, dan export_csv.php
├── config/             # Konfigurasi aplikasi dan koneksi PDO (koneksi.php)
├── includes/           # Komponen layout (header, footer, sidebar, topbar, auth helper)
├── kategori/           # Modul master Kategori sembako
├── supplier/           # Modul master Mitra Supplier distributor
├── uploads/            # Direktori penyimpanan foto komoditas barang
├── user/               # Modul manajemen pengguna (khusus Administrator)
├── dashboard.php       # Halaman utama ringkasan statistik & Chart.js
├── database.sql        # Skrip skema database MySQL & sample data
├── PANDUAN.docx        # Berkas panduan & laporan resmi format Microsoft Word
├── Panduan_Dion.pdf    # Dokumen panduan & laporan resmi format PDF
└── README.md           # Dokumentasi proyek
```

---

## 📄 Lisensi & Hak Cipta
Dibuat sebagai pemenuhan Tugas Proyek Pemrograman Web. Seluruh hak cipta dilindungi undang-undang.
