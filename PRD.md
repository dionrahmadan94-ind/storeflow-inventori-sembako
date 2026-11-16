# PRD: Web Inventory Sembako

| Keterangan | Isi |
|---|---|
| Nama proyek | Sistem Inventori Barang (Kelompok AB) |
| Jenis | Proyek kelompok, maksimal 3 mahasiswa |
| Deadline | 16 November 2026, pukul 23.59 WIB |
| Teknologi | PHP Native, MySQL, HTML, CSS, JavaScript, Bootstrap 5 |
| Koneksi database | PDO (prepared statement) |
| Status dokumen | Draft v1.0 (9 November 2026) |

---

## 1. Latar Belakang

Pencatatan stok barang secara manual (buku atau spreadsheet terpisah) rawan salah catat, sulit dicari, dan tidak memberi gambaran stok secara cepat. Aplikasi ini menyediakan pencatatan barang berbasis web yang terpusat, memiliki pembagian hak akses, dan menampilkan ringkasan stok pada dashboard.

## 2. Tujuan

1. Memenuhi seluruh ketentuan teknis minimal tugas (database relasional, CRUD, login, dashboard, pencarian, validasi, Bootstrap 5, fitur tambahan).
2. Menghasilkan aplikasi yang alur bisnisnya jelas dan mudah didemokan.
3. Memastikan setiap anggota memahami seluruh alur aplikasi.

### Di luar cakupan (Non-Goals)
- Transaksi barang masuk dan keluar (tidak wajib sesuai ketentuan dosen)
- Laporan keuangan, integrasi pihak ketiga, dan notifikasi email
- Aplikasi mobile native

## 3. Pengguna dan Hak Akses

| Role | Deskripsi |
|---|---|
| **Admin** | Pengelola penuh sistem |
| **Petugas** | Staf gudang yang mengelola data barang harian |

### Matriks hak akses

| Fitur | Admin | Petugas |
|---|:---:|:---:|
| Login dan logout | Ya | Ya |
| Lihat dashboard | Ya | Ya |
| Lihat, cari data barang | Ya | Ya |
| Tambah dan ubah barang | Ya | Ya |
| Hapus barang | Ya | Tidak |
| CRUD kategori | Ya | Hanya lihat |
| CRUD supplier | Ya | Hanya lihat |
| CRUD user | Ya | Tidak |
| Export Excel/CSV | Ya | Ya |

Halaman yang tidak boleh diakses harus ditolak di sisi server (bukan hanya disembunyikan di menu).

## 4. Kebutuhan Fungsional

### FR-01 Autentikasi
- Form login berisi username dan password.
- Password disimpan dengan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Session dimulai saat login berhasil dan dihancurkan saat logout (`session_destroy()`).
- Halaman selain login wajib mengecek session. Pengguna yang belum login diarahkan ke halaman login.
- Pesan error ditampilkan bila username atau password salah.

### FR-02 Dashboard
Menampilkan ringkasan setelah login:
- Total barang, total kategori, total supplier
- Jumlah dan daftar barang dengan stok menipis (stok kurang dari 5)
- Grafik sederhana jumlah barang per kategori (Chart.js lewat CDN)

### FR-03 CRUD Barang
- Tambah, tampil, ubah, hapus data barang.
- Field: nama barang, kategori (dropdown dari tabel kategori), supplier (dropdown dari tabel supplier), stok, harga, gambar.
- Daftar barang menampilkan nama kategori dan nama supplier lewat `JOIN`.

### FR-04 CRUD Kategori
- Tambah, tampil, ubah, hapus kategori.
- Kategori yang masih dipakai barang tidak boleh dihapus. Tampilkan pesan yang jelas.

### FR-05 CRUD Supplier
- Tambah, tampil, ubah, hapus supplier.
- Supplier yang masih dipakai barang tidak boleh dihapus. Tampilkan pesan yang jelas.

### FR-06 CRUD User (Admin saja)
- Tambah, tampil, ubah, hapus user dengan pilihan role admin atau petugas.
- Saat ubah user, password boleh dikosongkan bila tidak ingin diganti.
- Admin tidak boleh menghapus akunnya sendiri.

### FR-07 Pencarian
- Pencarian barang berdasarkan kata kunci pada nama barang, dengan filter tambahan kategori.
- Pencarian kategori, supplier, dan user berdasarkan nama.
- Query pencarian memakai prepared statement dengan `LIKE`.

### FR-08 Validasi Form
- Field wajib tidak boleh kosong.
- Stok dan harga harus angka dan tidak negatif.
- Username unik. Panjang password minimal 6 karakter.
- Pesan sukses atau error ditampilkan memakai komponen alert Bootstrap.
- Validasi dilakukan di sisi klien (atribut HTML5) dan di sisi server (PHP).

### FR-09 Fitur Tambahan
Ketiganya dikerjakan:
1. **Upload gambar barang**: format jpg, jpeg, png, ukuran maksimal 2 MB, nama file di-rename unik, disimpan di folder `uploads/`.
2. **Pagination**: 10 data per halaman pada daftar barang.
3. **Export Excel/CSV**: mengunduh daftar barang (sesuai filter pencarian aktif) dalam format CSV.

## 5. Kebutuhan Non-Fungsional

| Aspek | Ketentuan |
|---|---|
| Keamanan | Prepared statement (PDO) di semua query, `password_hash`, escape output dengan `htmlspecialchars()`, validasi tipe dan ukuran file upload |
| Tampilan | Bootstrap 5, responsif, navbar konsisten di semua halaman |
| Kode | Terstruktur, koneksi database dipisah di satu file, header dan footer memakai include |
| Kompatibilitas | XAMPP (PHP 8.x, MySQL/MariaDB), browser Chrome dan Firefox terbaru |

## 6. Desain Database

Nama database: `db_inventori`

### Relasi

```
kategori (1) ----< barang >---- (1) supplier
users berdiri sendiri (untuk autentikasi dan role)
```

### Struktur tabel

**users**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_user | INT AUTO_INCREMENT | Primary key |
| nama | VARCHAR(100) | Wajib |
| username | VARCHAR(50) | Unik |
| password | VARCHAR(255) | Hasil hash |
| role | ENUM('admin','petugas') | Wajib |
| created_at | TIMESTAMP | Default sekarang |

**kategori**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_kategori | INT AUTO_INCREMENT | Primary key |
| nama_kategori | VARCHAR(100) | Unik, wajib |

**supplier**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_supplier | INT AUTO_INCREMENT | Primary key |
| nama_supplier | VARCHAR(100) | Wajib |
| kontak | VARCHAR(20) | Nomor telepon |
| alamat | TEXT | Opsional |

**barang**

| Kolom | Tipe | Keterangan |
|---|---|---|
| id_barang | INT AUTO_INCREMENT | Primary key |
| nama_barang | VARCHAR(150) | Wajib |
| id_kategori | INT | Foreign key ke kategori(id_kategori) |
| id_supplier | INT | Foreign key ke supplier(id_supplier) |
| stok | INT | Default 0 |
| harga | DECIMAL(12,2) | Wajib |
| gambar | VARCHAR(255) | Nama file, boleh kosong |
| created_at | TIMESTAMP | Default sekarang |

Aturan foreign key: `ON DELETE RESTRICT` agar kategori atau supplier yang masih dipakai tidak bisa terhapus.

### Data awal (dummy)
- 2 user: 1 admin dan 1 petugas
- 5 kategori
- 4 supplier
- Minimal 15 barang (beberapa dengan stok kurang dari 5 agar dashboard terlihat hidup)

## 7. Struktur Folder

```
inventori/
├── config/
│   └── koneksi.php
├── includes/
│   ├── auth.php          (cek session dan role)
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── auth/
│   ├── login.php
│   └── logout.php
├── barang/
│   ├── index.php
│   ├── tambah.php
│   ├── ubah.php
│   ├── hapus.php
│   └── export.php
├── kategori/
│   └── (index, tambah, ubah, hapus)
├── supplier/
│   └── (index, tambah, ubah, hapus)
├── user/
│   └── (index, tambah, ubah, hapus)
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── uploads/
├── dashboard.php
├── index.php             (redirect ke login atau dashboard)
├── database.sql
└── PANDUAN.pdf
```

## 8. Alur Pengguna Utama

1. Pengguna membuka aplikasi, lalu diarahkan ke halaman login.
2. Login berhasil, pengguna masuk ke dashboard sesuai role.
3. Pengguna membuka menu Barang, mencari barang, lalu menambah atau mengubah data.
4. Admin membuka menu Kategori, Supplier, dan User untuk mengelola data master.
5. Pengguna mengunduh data barang lewat tombol export.
6. Pengguna logout dan session dihapus.

## 9. Pembagian Tugas

| Anggota | Tanggung jawab |
|---|---|
| Anggota 1 | Desain database dan `database.sql`, koneksi, login, logout, role, CRUD user |
| Anggota 2 | CRUD barang, upload gambar, pencarian, pagination |
| Anggota 3 | CRUD kategori dan supplier, dashboard, export CSV, layout Bootstrap |
| Bersama | Testing silang, PANDUAN.pdf, video presentasi |

Setiap anggota tetap wajib memahami seluruh alur karena akan ditanya saat presentasi.

## 10. Timeline

| Tanggal | Target |
|---|---|
| 9 sampai 10 Nov | Setup XAMPP, desain database, koneksi, layout dasar, login dan role |
| 11 sampai 13 Nov | Pengerjaan semua CRUD, pencarian, dashboard, fitur tambahan |
| 14 Nov | Integrasi, testing silang, perbaikan bug |
| 15 Nov | PANDUAN.pdf, rekam dan upload video YouTube |
| 16 Nov | Buffer, cek ulang paket, upload ke e-learning sebelum 23.59 WIB |

## 11. Deliverables

1. Folder proyek `.zip` berisi seluruh file PHP, CSS, JS, aset, dan folder uploads.
2. `database.sql` berisi struktur tabel dan data dummy.
3. `PANDUAN.pdf` berisi identitas anggota, judul dan deskripsi aplikasi, kebutuhan sistem, cara instalasi, akun login pengujian, dan screenshot fitur.
4. Link video presentasi YouTube (boleh private), dimasukkan ke e-learning.

## 12. Kriteria Keberhasilan (Mapping ke Penilaian)

| Komponen (bobot) | Bukti pemenuhan |
|---|---|
| Struktur database dan relasi (10%) | 4 tabel, PK dan FK di `barang`, dijelaskan di PANDUAN |
| Koneksi dan struktur kode (10%) | PDO di `config/koneksi.php`, include header, navbar, footer |
| Fitur CRUD (20%) | CRUD barang, kategori, supplier, user, saling berhubungan |
| Login dan hak akses (10%) | 2 role dengan hak berbeda sesuai matriks |
| Dashboard, tampilan, navigasi (10%) | Dashboard statistik dan grafik, Bootstrap 5 responsif |
| Validasi dan keamanan (10%) | Validasi klien dan server, hash password, prepared statement |
| Kreativitas dan fitur tambahan (10%) | Upload gambar, pagination, export CSV, grafik |
| Dokumentasi (10%) | PANDUAN.pdf lengkap dengan screenshot |
| Presentasi dan demo (10%) | Video demo alur per role, semua anggota siap menjawab |

## 13. Checklist Sebelum Submit

- [ ] Aplikasi jalan di XAMPP dari kondisi bersih memakai `database.sql`
- [ ] Akun admin dan petugas bisa login, hak akses berbeda terbukti
- [ ] Semua CRUD berfungsi dan relasi tampil benar
- [ ] Hapus kategori atau supplier yang dipakai barang ditolak dengan pesan jelas
- [ ] Pencarian, pagination, upload gambar, dan export berfungsi
- [ ] Semua query memakai prepared statement
- [ ] Password di database berupa hash
- [ ] PANDUAN.pdf memuat akun uji dan screenshot
- [ ] Video sudah diunggah dan link bisa dibuka
- [ ] ZIP dan link video sudah diunggah ke e-learning sebelum 16 November 2026 pukul 23.59 WIB

## 14. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Waktu hanya 7 hari | Tema sederhana, kerja paralel sesuai pembagian, buffer di 16 Nov |
| Konflik kode antar anggota | Satu folder modul per anggota, gunakan Git atau sinkronisasi rutin |
| Fitur kelewat (misalnya 2 role) | Gunakan checklist di dokumen ini |
| Anggota tidak paham keseluruhan alur | Sesi walkthrough bersama sebelum rekam video |
