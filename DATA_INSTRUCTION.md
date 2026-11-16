# Panduan Memasukkan Data Asli

Anda dapat mengisi **data barang, kategori, dan supplier** secara manual melalui antarmuka web atau dengan mengimpor file CSV.

## 1️⃣ Reset Database (Opsional)
Jika Anda ingin menghapus semua data yang ada (termasuk data dummy) dan memulai dari awal, jalankan skrip utilitas berikut:

```bash
php reset_data.php
```
Skrip ini akan **TRUNCATE** tabel `users`, `kategori`, `supplier`, dan `barang`. Setelah selesai Anda dapat mengisi data kembali.

> **Catatan:** `users` juga di‑reset; Anda dapat login kembali dengan akun default:
> - **admin** / `admin123`
> - **petugas** / `petugas123`

## 2️⃣ Menambah Data via UI Web
1. Buka aplikasi di browser, misalnya `http://localhost/uts_dion/`.
2. Pilih menu **Data Barang**, **Kategori**, atau **Supplier**.
3. Klik tombol **Tambah** pada masing‑masing halaman.
4. Isi semua field yang diperlukan. Pada halaman **Barang**, Anda dapat **Upload Gambar** – gambar akan disimpan di folder `uploads/` dan otomatis ditampilkan pada notifikasi serta daftar barang.
5. Simpan – data akan langsung tersimpan di database.

## 3️⃣ Impor CSV (Jika banyak data)
### 3.1 Siapkan file CSV
- **kategori.csv** – kolom: `nama_kategori`
- **supplier.csv** – kolom: `nama_supplier,kontak,alamat`
- **barang.csv** – kolom: `nama_barang,id_kategori,id_supplier,stok,harga,gambar`
  - Pastikan kolom `gambar` berisi nama file yang sudah ada di folder `uploads/` (atau kosong jika tidak ada gambar).

### 3.2 Jalankan skrip impor
Buat file PHP `import_csv.php` (atau gunakan yang sudah ada) dan jalankan:

```bash
php import_csv.php kategori kategori.csv
php import_csv.php supplier supplier.csv
php import_csv.php barang barang.csv
```

Skrip contoh tersedia di `scripts/` (buat folder jika belum ada).

## 4️⃣ Verifikasi
- **Daftar Barang** harus menampilkan gambar yang Anda upload (bukan placeholder).
- **Notifikasi Stok** menampilkan barang dengan stok < 5 berdasarkan data yang Anda masukkan.
- **Kategori** & **Supplier** tertampil sesuai entri yang Anda buat.

---

Jika ada pertanyaan atau butuh bantuan lanjutan, beri tahu saya!
