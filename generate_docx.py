#!/usr/bin/env python3
import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

doc = Document()

# Set Margins to A4 with 2.25cm left/right, 2cm top, 1.8cm bottom
for section in doc.sections:
    section.page_width = Inches(8.27)
    section.page_height = Inches(11.69)
    section.top_margin = Inches(0.8)
    section.bottom_margin = Inches(0.7)
    section.left_margin = Inches(0.88)
    section.right_margin = Inches(0.88)
    
    # Configure footer: "Halaman X"
    footer = section.footer
    f_p = footer.paragraphs[0]
    f_p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    f_run = f_p.add_run("Halaman ")
    f_run.font.name = "Arial"
    f_run.font.size = Pt(9)
    f_run.font.color.rgb = RGBColor(100, 100, 100)
    
    # Add page number field XML
    fldSimple = OxmlElement('w:fldSimple')
    fldSimple.set(qn('w:instr'), 'PAGE')
    f_p._p.append(fldSimple)

# Helper styles
def set_font(run, name="Tahoma", size_pt=9.5, bold=False, italic=False, color_rgb=(0,0,0)):
    run.font.name = name
    run.font.size = Pt(size_pt)
    run.bold = bold
    run.italic = italic
    run.font.color.rgb = RGBColor(*color_rgb)

def add_p(doc, text="", align=WD_ALIGN_PARAGRAPH.JUSTIFY, space_after=5, line_spacing=1.15):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = line_spacing
    if text:
        r = p.add_run(text)
        set_font(r)
    return p

def add_heading_main(doc, text, space_before=10, space_after=4):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.keep_with_next = True
    r = p.add_run(text)
    set_font(r, "Tahoma", 10.5, bold=True)
    return p

def add_heading_sub(doc, text, space_before=7, space_after=3):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.keep_with_next = True
    r = p.add_run(text)
    set_font(r, "Tahoma", 9.5, bold=True)
    return p

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    tcPr.append(parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>'))

def set_cell_margins(cell, top=60, bottom=60, left=100, right=100):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('w:top', top), ('w:bottom', bottom), ('w:left', left), ('w:right', right)]:
        node = OxmlElement(m)
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def set_table_borders(table, color="CBD5E1", sz="4", val="single"):
    tblPr = table._tbl.tblPr
    borders = parse_xml(
        f'<w:tblBorders {nsdecls("w")}>\n'
        f'  <w:top w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:bottom w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:left w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:right w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:insideH w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'  <w:insideV w:val="{val}" w:sz="{sz}" w:space="0" w:color="{color}"/>\n'
        f'</w:tblBorders>'
    )
    tblPr.append(borders)

# ==========================================
# HALAMAN 1: COVER
# ==========================================
p_cover_title1 = doc.add_paragraph()
p_cover_title1.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cover_title1.paragraph_format.space_before = Pt(30)
p_cover_title1.paragraph_format.space_after = Pt(8)
r1 = p_cover_title1.add_run("LAPORAN UJIAN TENGAH SEMESTER (UTS)")
set_font(r1, "Tahoma", 13.5, bold=True)

p_cover_title2 = doc.add_paragraph()
p_cover_title2.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cover_title2.paragraph_format.space_after = Pt(35)
r2 = p_cover_title2.add_run("Dokumentasi Panduan Lengkap Penggunaan, Instalasi, dan Pengujian Sistem Inventori Sembako (Storeflow) Berbasis Web")
set_font(r2, "Tahoma", 11.5, bold=True)

# Logo
p_logo = doc.add_paragraph()
p_logo.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_logo.paragraph_format.space_after = Pt(45)
if os.path.exists("extracted_img_p1_Im1.png"):
    p_logo.add_run().add_picture("extracted_img_p1_Im1.png", width=Inches(3.2))

# Cover Table
cover_tbl = doc.add_table(rows=4, cols=2)
cover_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
set_table_borders(cover_tbl, color="000000", sz="8")

identitas_data = [
    ("Nama", "Desandro Myska Paradisa"),
    ("NIM", "2511500908"),
    ("Mata Kuliah", "Pengolahan Bahasa Alami / Pemrograman Web"),
    ("Dosen Pengampu", "Merry Anggraeni, S.Kom., M.Kom.")
]

col_widths = [Inches(1.8), Inches(3.6)]
for i, (label, val) in enumerate(identitas_data):
    row = cover_tbl.rows[i]
    # Cell 0
    c0 = row.cells[0]
    c0.width = col_widths[0]
    set_cell_margins(c0, top=100, bottom=100, left=140, right=140)
    p0 = c0.paragraphs[0]
    p0.paragraph_format.space_after = Pt(0)
    r = p0.add_run(label)
    set_font(r, "Tahoma", 10, bold=True)
    
    # Cell 1
    c1 = row.cells[1]
    c1.width = col_widths[1]
    set_cell_margins(c1, top=100, bottom=100, left=140, right=140)
    p1 = c1.paragraphs[0]
    p1.paragraph_format.space_after = Pt(0)
    r = p1.add_run(val)
    set_font(r, "Tahoma", 10, bold=False)

doc.add_page_break()

# ==========================================
# HALAMAN 2: ABSTRAK, IDENTITAS, DESKRIPSI, KEBUTUHAN SISTEM
# ==========================================
add_heading_main(doc, "ABSTRAK", space_before=0, space_after=4)
add_p(doc, 
    "Laporan ini menyajikan panduan komprehensif implementasi dan evaluasi sistem perangkat lunak Storeflow - Sistem Inventori Sembako "
    "yang dibangun untuk memenuhi kriteria tugas Ujian Tengah Semester (UTS) mata kuliah Pemrograman Web. Dokumen ini merangkum seluruh aspek "
    "operasional aplikasi mulai dari struktur identitas tim pengembang, arsitektur data, kebutuhan sistem lingkungan eksekusi (PHP 8.2 & MySQL), "
    "petunjuk instalasi berganda (Apache XAMPP dan PHP Built-in Server), kredensial akun uji untuk demonstrasi penilaian, matriks pembagian wewenang "
    "Role-Based Access Control (RBAC), dokumentasi sepuluh modul antarmuka lengkap dengan bukti tangkapan layar (screenshot) asli, hingga checklist "
    "pemenuhan rubrik penilaian tugas. Sistem dirancang untuk mengatasi kelemahan pencatatan logistik manual buku maupun spreadsheet yang rentan salah catat, "
    "tidak memiliki kontrol otorisasi berjenjang, dan tidak menyediakan visualisasi dini stok komoditas kritis. Seluruh modul telah melalui uji coba "
    "fungsional dan siap dinilai secara objektif."
)

add_heading_main(doc, "I. IDENTITAS KELOMPOK")
add_p(doc, "Tugas proyek mata kuliah Pemrograman Web ini dikembangkan secara kolaboratif dengan pembagian peran dan tanggung jawab modul sebagai berikut:")

tbl_id = doc.add_table(rows=4, cols=4)
tbl_id.alignment = WD_TABLE_ALIGNMENT.CENTER
set_table_borders(tbl_id, color="CBD5E1", sz="4")

headers_id = ["No", "Nama Mahasiswa", "NIM", "Peran & Tanggung Jawab Modul"]
widths_id = [Inches(0.4), Inches(1.8), Inches(1.1), Inches(3.2)]

for j, h in enumerate(headers_id):
    cell = tbl_id.rows[0].cells[j]
    cell.width = widths_id[j]
    set_cell_background(cell, "2B579A")
    set_cell_margins(cell, top=70, bottom=70, left=80, right=80)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j == 0 else WD_ALIGN_PARAGRAPH.LEFT
    p.paragraph_format.space_after = Pt(0)
    r = p.add_run(h)
    set_font(r, "Tahoma", 8.5, bold=True, color_rgb=(255,255,255))

data_id = [
    ("1", "Desandro Myska Paradisa", "2511500908", "Perancangan Database (database.sql), Koneksi PDO, Autentikasi (Login/Logout), Hak Akses (RBAC), Modul User"),
    ("2", "[Nama Mahasiswa 2]", "[NIM Mahasiswa 2]", "Modul Barang (CRUD), Upload & Validasi Gambar, Pencarian & Filter, Pagination"),
    ("3", "[Nama Mahasiswa 3]", "[NIM Mahasiswa 3]", "Modul Kategori, Modul Supplier, Dashboard Interaktif (Chart.js), Export CSV, Desain UI Bootstrap 5")
]

for i, row_data in enumerate(data_id, start=1):
    row = tbl_id.rows[i]
    fill_hex = "F8FAFC" if i % 2 == 0 else "FFFFFF"
    for j, val in enumerate(row_data):
        cell = row.cells[j]
        cell.width = widths_id[j]
        set_cell_background(cell, fill_hex)
        set_cell_margins(cell, top=60, bottom=60, left=80, right=80)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j == 0 else WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_after = Pt(0)
        r = p.add_run(val)
        set_font(r, "Tahoma", 8, bold=(j == 1))

add_heading_main(doc, "II. DESKRIPSI SISTEM")
add_p(doc, 
    "Storeflow adalah sistem inventori sembako berbasis web yang dirancang khusus untuk memfasilitasi pencatatan, pemantauan, "
    "dan manajemen ketersediaan komoditas bahan pokok gudang secara terpusat, akurat, dan real-time. Aplikasi ini memecahkan masalah pembukuan "
    "manual/spreadsheet konvensional yang rentan human error, tidak memiliki kontrol hak akses pengguna, dan tidak memiliki visualisasi peringatan stok kritis."
)

add_heading_main(doc, "III. KEBUTUHAN SISTEM & DEPENDENSI")
items_req = [
    ("Web Server: ", "Apache 2.4+ (XAMPP / MAMP / PHP Built-in Server)."),
    ("Bahasa Pemrograman: ", "PHP Native versi 8.0 atau lebih baru (Disarankan PHP 8.2)."),
    ("Basis Data Engine: ", "MySQL 5.7+ atau MariaDB 10.4+."),
    ("Ekstensi PHP Wajib: ", "pdo_mysql, session, fileinfo, gd."),
    ("Frontend Framework: ", "Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Chart.js CDN."),
    ("Peramban Pendukung: ", "Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi terbaru.")
]
for prefix, desc in items_req:
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_after = Pt(2)
    p.paragraph_format.line_spacing = 1.15
    r_pre = p.add_run(prefix)
    set_font(r_pre, "Tahoma", 9, bold=True)
    r_desc = p.add_run(desc)
    set_font(r_desc, "Tahoma", 9, bold=False)

doc.add_page_break()

# ==========================================
# HALAMAN 3: INSTALASI, KREDENSIAL, MATRIKS RBAC
# ==========================================
add_heading_main(doc, "IV. PETUNJUK INSTALASI & MENJALANKAN SISTEM", space_before=0)
add_heading_sub(doc, "Metode 1: Menggunakan XAMPP (Windows / macOS)", space_before=3)
steps_xampp = [
    "Salin folder proyek uts_dion ke direktori web server (Windows: C:\\xampp\\htdocs\\uts_dion\\ atau macOS: /Applications/XAMPP/xamppfiles/htdocs/uts_dion/).",
    "Jalankan service Apache dan MySQL melalui XAMPP Control Panel.",
    "Buka peramban dan akses http://localhost/phpmyadmin/.",
    "Buat database baru bernama: db_inventori.",
    "Klik tab Import, pilih berkas database.sql di root proyek, lalu klik Import / Go.",
    "Buka aplikasi di peramban: http://localhost/uts_dion/."
]
for step in steps_xampp:
    p = doc.add_paragraph(style='List Number')
    p.paragraph_format.space_after = Pt(2)
    p.paragraph_format.line_spacing = 1.15
    r = p.add_run(step)
    set_font(r, "Tahoma", 8.5)

add_heading_sub(doc, "Metode 2: Menggunakan PHP CLI Server (Pengujian Mandiri)", space_before=5)
p_cli = doc.add_paragraph()
p_cli.paragraph_format.space_after = Pt(6)
p_cli.paragraph_format.line_spacing = 1.15
r_cli = p_cli.add_run(
    "# 1. Pastikan database MySQL aktif dan impor database.sql\n"
    "mysql -u root -p db_inventori < database.sql\n\n"
    "# 2. Jalankan PHP Built-in Server di root folder proyek\n"
    "php -S 127.0.0.1:8088\n\n"
    "# 3. Buka peramban pada alamat: http://127.0.0.1:8088"
)
set_font(r_cli, "Courier New", 8)

add_heading_main(doc, "V. KREDENSIAL AKUN PENGUJIAN (TEST CREDENTIALS)", space_before=6)
add_p(doc, "Sistem menyediakan 2 tingkatan akun (Role-Based Access Control) untuk keperluan demo pengujian:", space_after=3)

tbl_cred = doc.add_table(rows=3, cols=5)
tbl_cred.alignment = WD_TABLE_ALIGNMENT.CENTER
set_table_borders(tbl_cred, color="CBD5E1", sz="4")
widths_cred = [Inches(0.4), Inches(1.4), Inches(1.1), Inches(1.1), Inches(2.5)]

for j, h in enumerate(["No", "Peran (Role)", "Username", "Password", "Deskripsi Hak Akses"]):
    cell = tbl_cred.rows[0].cells[j]
    cell.width = widths_cred[j]
    set_cell_background(cell, "2B579A")
    set_cell_margins(cell, top=60, bottom=60, left=70, right=70)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j == 0 else WD_ALIGN_PARAGRAPH.LEFT
    p.paragraph_format.space_after = Pt(0)
    r = p.add_run(h)
    set_font(r, "Tahoma", 8, bold=True, color_rgb=(255,255,255))

data_cred = [
    ("1", "Administrator", "admin", "admin123", "Full Access: Dashboard statistik, CRUD Barang + Hapus, CRUD Kategori, CRUD Supplier, CRUD Pengguna, Ekspor CSV."),
    ("2", "Petugas Gudang", "petugas", "petugas123", "Operational Access: Dashboard statistik, Lihat & Cari Barang, Tambah/Edit Barang, Hanya Lihat Kategori & Supplier, Ekspor CSV.")
]

for i, row_data in enumerate(data_cred, start=1):
    row = tbl_cred.rows[i]
    fill_hex = "F8FAFC" if i % 2 == 0 else "FFFFFF"
    for j, val in enumerate(row_data):
        cell = row.cells[j]
        cell.width = widths_cred[j]
        set_cell_background(cell, fill_hex)
        set_cell_margins(cell, top=50, bottom=50, left=70, right=70)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j == 0 else WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_after = Pt(0)
        r = p.add_run(val)
        set_font(r, "Tahoma", 8, bold=(j == 1))

p_note = doc.add_paragraph()
p_note.paragraph_format.space_before = Pt(2)
p_note.paragraph_format.space_after = Pt(5)
r_note = p_note.add_run("Catatan: Pada halaman login, telah tersedia tombol cepat 'Akun Pengujian' (Admin & Petugas) untuk mengisi username dan password secara otomatis.")
set_font(r_note, "Tahoma", 8, italic=True, color_rgb=(70,70,70))

add_heading_main(doc, "VI. MATRIKS HAK AKSES ANTAR PERAN (RBAC)", space_before=5)
tbl_rbac = doc.add_table(rows=10, cols=4)
tbl_rbac.alignment = WD_TABLE_ALIGNMENT.CENTER
set_table_borders(tbl_rbac, color="CBD5E1", sz="4")
widths_rbac = [Inches(2.1), Inches(0.8), Inches(0.8), Inches(2.8)]

for j, h in enumerate(["Fitur / Modul", "Admin", "Petugas", "Catatan & Proteksi Server"]):
    cell = tbl_rbac.rows[0].cells[j]
    cell.width = widths_rbac[j]
    set_cell_background(cell, "2B579A")
    set_cell_margins(cell, top=60, bottom=60, left=70, right=70)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [1, 2] else WD_ALIGN_PARAGRAPH.LEFT
    p.paragraph_format.space_after = Pt(0)
    r = p.add_run(h)
    set_font(r, "Tahoma", 8, bold=True, color_rgb=(255,255,255))

data_rbac = [
    ("Login & Logout", "Ya", "Ya", "Enkripsi hash password_hash() & penghancuran sesi"),
    ("Dashboard Statistik & Grafik", "Ya", "Ya", "Chart.js stok per kategori & widget Inventory Alerts (< 5 unit)"),
    ("Lihat, Cari & Filter Barang", "Ya", "Ya", "Prepared statement LIKE & filter dropdown dinamis"),
    ("Tambah & Ubah Barang", "Ya", "Ya", "Upload gambar JPG/PNG maks 2 MB + Live Image Preview"),
    ("Hapus Barang", "Ya", "Tidak", "Ditolak di sisi server via require_role('admin')"),
    ("Export Excel/CSV Barang", "Ya", "Ya", "Mengekspor data sesuai filter/pencarian yang sedang aktif"),
    ("CRUD & Pencarian Kategori", "Ya", "Hanya Lihat", "Pencarian nama kategori + Proteksi ON DELETE RESTRICT"),
    ("CRUD & Pencarian Supplier", "Ya", "Hanya Lihat", "Pencarian nama/kontak/alamat + Proteksi ON DELETE RESTRICT"),
    ("CRUD Pengguna (User)", "Ya", "Tidak", "Admin tidak diizinkan menghapus akunnya sendiri (FR-06)")
]

for i, row_data in enumerate(data_rbac, start=1):
    row = tbl_rbac.rows[i]
    fill_hex = "F8FAFC" if i % 2 == 0 else "FFFFFF"
    for j, val in enumerate(row_data):
        cell = row.cells[j]
        cell.width = widths_rbac[j]
        set_cell_background(cell, fill_hex)
        set_cell_margins(cell, top=40, bottom=40, left=70, right=70)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [1, 2] else WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_after = Pt(0)
        r = p.add_run(val)
        color = (220, 38, 38) if val == "Tidak" else (0,0,0)
        set_font(r, "Tahoma", 7.8, bold=(j == 0 or val in ["Ya", "Tidak"]), color_rgb=color)

doc.add_page_break()

# ==========================================
# HALAMAN 4: SCREENSHOT 1 & SCREENSHOT 2
# ==========================================
add_heading_main(doc, "VII. PANDUAN PENGGUNAAN FITUR & BUKTI SCREENSHOT APLIKASI", space_before=0)

add_heading_sub(doc, "1. Halaman Login & Fitur Akses Cepat Kredensial", space_before=4)
add_p(doc, 
    "Halaman otentikasi dilengkapi validasi sesi server-side, perlindungan password dengan password_hash() BCRYPT, "
    "serta tombol pintas kredensial uji (One-Click Demo Fill) untuk memudahkan penguji menjalankan evaluasi.", space_after=3
)

p_ss1 = doc.add_paragraph()
p_ss1.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss1.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/01_login.png"):
    p_ss1.add_run().add_picture("assets/screenshots/01_login.png", width=Inches(5.4))

p_cap1 = doc.add_paragraph()
p_cap1.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap1.paragraph_format.space_after = Pt(8)
r_c1 = p_cap1.add_run("Gambar 1. Halaman login dengan sistem proteksi sesi dan tombol pintas Akun Pengujian")
set_font(r_c1, "Tahoma", 8, italic=True)

add_heading_sub(doc, "2. Dashboard Statistik Interaktif & Inventory Alerts", space_before=4)
add_p(doc, 
    "Setelah login, pengguna disajikan ringkasan metrik bisnis, visualisasi diagram batang rasio stok komoditas per kategori (Chart.js), "
    "dan tabel peringatan stok kritis (< 5 unit) lengkap dengan tombol restock instan.", space_after=3
)

p_ss2 = doc.add_paragraph()
p_ss2.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss2.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/02_dashboard_admin.png"):
    p_ss2.add_run().add_picture("assets/screenshots/02_dashboard_admin.png", width=Inches(5.4))

p_cap2 = doc.add_paragraph()
p_cap2.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap2.paragraph_format.space_after = Pt(4)
r_c2 = p_cap2.add_run("Gambar 2. Dashboard statistik: metrik stok, grafik Chart.js, dan tabel Inventory Alerts")
set_font(r_c2, "Tahoma", 8, italic=True)

doc.add_page_break()

# ==========================================
# HALAMAN 5: SCREENSHOT 3 & SCREENSHOT 4
# ==========================================
add_heading_sub(doc, "3. Notifikasi Terpusat Stok Kritis (Notification Bell)", space_before=0)
add_p(doc, 
    "Pada bagian navbar atas, terdapat ikon lonceng notifikasi interaktif yang mendeteksi stok sembako yang berada di bawah ambang batas operasional harian "
    "(< 5 unit) dengan live badge dan dropdown rincian barang.", space_after=3
)

p_ss3 = doc.add_paragraph()
p_ss3.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss3.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/03_notifikasi_stok.png"):
    p_ss3.add_run().add_picture("assets/screenshots/03_notifikasi_stok.png", width=Inches(5.4))

p_cap3 = doc.add_paragraph()
p_cap3.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap3.paragraph_format.space_after = Pt(8)
r_c3 = p_cap3.add_run("Gambar 3. Menu tarik-turun lonceng notifikasi persediaan kritis pada navbar sistem")
set_font(r_c3, "Tahoma", 8, italic=True)

add_heading_sub(doc, "4. Pengelolaan Data Barang (Pencarian, Filter, & Pagination)", space_before=4)
add_p(doc, 
    "Daftar seluruh komoditas sembako ditampilkan lengkap dengan foto asli, status stok (Aman/Kritis/Habis), pagination 10 data per halaman, "
    "tombol aksi cepat (-1 stok), kotak pencarian real-time, dan ekspor dataset CSV.", space_after=3
)

p_ss4 = doc.add_paragraph()
p_ss4.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss4.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/04_data_barang.png"):
    p_ss4.add_run().add_picture("assets/screenshots/04_data_barang.png", width=Inches(5.4))

p_cap4 = doc.add_paragraph()
p_cap4.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap4.paragraph_format.space_after = Pt(4)
r_c4 = p_cap4.add_run("Gambar 4. Antarmuka pengelolaan data barang dengan thumbnail gambar, filter, dan paginasi")
set_font(r_c4, "Tahoma", 8, italic=True)

doc.add_page_break()

# ==========================================
# HALAMAN 6: SCREENSHOT 5 & SCREENSHOT 6
# ==========================================
add_heading_sub(doc, "5. Pop-up Modal Detail Komoditas", space_before=0)
add_p(doc, 
    "Pengguna dapat melihat ringkasan spesifikasi lengkap komoditas tanpa berpindah halaman menggunakan jendela modal interaktif, "
    "termasuk foto resolusi tinggi, harga, stok, dan kontak mitra supplier.", space_after=3
)

p_ss5 = doc.add_paragraph()
p_ss5.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss5.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/05_detail_barang_modal.png"):
    p_ss5.add_run().add_picture("assets/screenshots/05_detail_barang_modal.png", width=Inches(5.4))

p_cap5 = doc.add_paragraph()
p_cap5.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap5.paragraph_format.space_after = Pt(8)
r_c5 = p_cap5.add_run("Gambar 5. Pop-up modal interaktif rincian komoditas dan informasi distributor")
set_font(r_c5, "Tahoma", 8, italic=True)

add_heading_sub(doc, "6. Form Tambah & Ubah Barang (Upload Gambar & Live Preview)", space_before=4)
add_p(doc, 
    "Form input data komoditas dilengkapi validasi sanitasi angka positif, relasi dinamis ke tabel kategori & supplier, "
    "serta area dropzone upload berkas gambar (JPG, PNG maks 2 MB) disertai fitur live preview foto.", space_after=3
)

p_ss6 = doc.add_paragraph()
p_ss6.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_ss6.paragraph_format.space_after = Pt(2)
if os.path.exists("assets/screenshots/06_form_tambah_barang.png"):
    p_ss6.add_run().add_picture("assets/screenshots/06_form_tambah_barang.png", width=Inches(5.4))

p_cap6 = doc.add_paragraph()
p_cap6.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_cap6.paragraph_format.space_after = Pt(4)
r_c6 = p_cap6.add_run("Gambar 6. Formulir penambahan barang dengan validasi masukan dan pratinjau gambar langsung")
set_font(r_c6, "Tahoma", 8, italic=True)

doc.add_page_break()

# ==========================================
# HALAMAN 7: SCREENSHOT 7, 8, 9, 10
# ==========================================
add_heading_sub(doc, "7. Modul Manajemen Master Kategori & Supplier", space_before=0)
add_p(doc, 
    "Pengelolaan master klasifikasi sembako dan direktori mitra distributor dilengkapi toolbar pencarian cepat serta proteksi integritas "
    "ON DELETE RESTRICT agar data master yang aktif tidak terhapus tidak sengaja.", space_after=3
)

# Table 2 cols for Kategori and Supplier
tbl_grid1 = doc.add_table(rows=1, cols=2)
tbl_grid1.alignment = WD_TABLE_ALIGNMENT.CENTER
for c in tbl_grid1.rows[0].cells:
    set_cell_margins(c, top=20, bottom=20, left=40, right=40)

c_kat = tbl_grid1.rows[0].cells[0]
c_kat.width = Inches(3.2)
p_k = c_kat.paragraphs[0]
p_k.alignment = WD_ALIGN_PARAGRAPH.CENTER
if os.path.exists("assets/screenshots/07_kategori.png"):
    p_k.add_run().add_picture("assets/screenshots/07_kategori.png", width=Inches(3.1))
p_k_cap = c_kat.add_paragraph()
p_k_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_k_cap.paragraph_format.space_after = Pt(2)
r = p_k_cap.add_run("Gambar 7a. Modul Kategori Sembako")
set_font(r, "Tahoma", 7.5, italic=True)

c_sup = tbl_grid1.rows[0].cells[1]
c_sup.width = Inches(3.2)
p_s = c_sup.paragraphs[0]
p_s.alignment = WD_ALIGN_PARAGRAPH.CENTER
if os.path.exists("assets/screenshots/08_supplier.png"):
    p_s.add_run().add_picture("assets/screenshots/08_supplier.png", width=Inches(3.1))
p_s_cap = c_sup.add_paragraph()
p_s_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_s_cap.paragraph_format.space_after = Pt(2)
r = p_s_cap.add_run("Gambar 7b. Modul Supplier Distributor")
set_font(r, "Tahoma", 7.5, italic=True)

add_heading_sub(doc, "8. Modul Pengguna & Pembatasan Akses Petugas Gudang (RBAC)", space_before=6)
add_p(doc, 
    "Administrator mengelola akun pengguna pada modul User. Saat login sebagai Petugas Gudang (Gambar 8b), tombol hapus barang ditiadakan "
    "dan menu kelola pengguna otomatis disembunyikan.", space_after=3
)

# Table 2 cols for User and Petugas View
tbl_grid2 = doc.add_table(rows=1, cols=2)
tbl_grid2.alignment = WD_TABLE_ALIGNMENT.CENTER
for c in tbl_grid2.rows[0].cells:
    set_cell_margins(c, top=20, bottom=20, left=40, right=40)

c_usr = tbl_grid2.rows[0].cells[0]
c_usr.width = Inches(3.2)
p_u = c_usr.paragraphs[0]
p_u.alignment = WD_ALIGN_PARAGRAPH.CENTER
if os.path.exists("assets/screenshots/09_kelola_user.png"):
    p_u.add_run().add_picture("assets/screenshots/09_kelola_user.png", width=Inches(3.1))
p_u_cap = c_usr.add_paragraph()
p_u_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_u_cap.paragraph_format.space_after = Pt(2)
r = p_u_cap.add_run("Gambar 8a. Modul Manajemen Pengguna")
set_font(r, "Tahoma", 7.5, italic=True)

c_pet = tbl_grid2.rows[0].cells[1]
c_pet.width = Inches(3.2)
p_pt = c_pet.paragraphs[0]
p_pt.alignment = WD_ALIGN_PARAGRAPH.CENTER
if os.path.exists("assets/screenshots/10_petugas_view.png"):
    p_pt.add_run().add_picture("assets/screenshots/10_petugas_view.png", width=Inches(3.1))
p_pt_cap = c_pet.add_paragraph()
p_pt_cap.alignment = WD_ALIGN_PARAGRAPH.CENTER
p_pt_cap.paragraph_format.space_after = Pt(2)
r = p_pt_cap.add_run("Gambar 8b. Tampilan Pembatasan Akses Petugas")
set_font(r, "Tahoma", 7.5, italic=True)

doc.add_page_break()

# ==========================================
# HALAMAN 8: CHECKLIST RUBRIK & KESIMPULAN
# ==========================================
add_heading_main(doc, "VIII. CHECKLIST EVALUASI RUBRIK PENILAIAN TUGAS", space_before=0)
add_p(doc, "Evaluasi pemenuhan seluruh kriteria teknis dan fitur sistem terhadap ketentuan penilaian tugas Pemrograman Web:", space_after=3)

tbl_rubrik = doc.add_table(rows=12, cols=4)
tbl_rubrik.alignment = WD_TABLE_ALIGNMENT.CENTER
set_table_borders(tbl_rubrik, color="CBD5E1", sz="4")
widths_rubrik = [Inches(0.4), Inches(2.4), Inches(1.1), Inches(2.6)]

for j, h in enumerate(["No", "Kriteria Penilaian Tugas", "Status", "Lokasi Berkas & Keterangan"]):
    cell = tbl_rubrik.rows[0].cells[j]
    cell.width = widths_rubrik[j]
    set_cell_background(cell, "2B579A")
    set_cell_margins(cell, top=60, bottom=60, left=70, right=70)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [0, 2] else WD_ALIGN_PARAGRAPH.LEFT
    p.paragraph_format.space_after = Pt(0)
    r = p.add_run(h)
    set_font(r, "Tahoma", 8, bold=True, color_rgb=(255,255,255))

data_rubrik = [
    ("1", "Database Relasional (min. 3 Tabel)", "Lengkap (4)", "database.sql (tabel users, kategori, supplier, barang)"),
    ("2", "CRUD Lengkap & Validasi Input", "Lengkap", "Sanitasi XSS htmlspecialchars() & validasi tipe data numerik"),
    ("3", "Autentikasi Login/Logout & Sesi", "Lengkap", "auth/login.php, auth/logout.php, BCRYPT password_hash()"),
    ("4", "Hak Akses Bertingkat (RBAC)", "Lengkap", "includes/auth.php (require_login(), require_role('admin'))"),
    ("5", "Dashboard Statistik & Grafik", "Lengkap", "dashboard.php visualisasi Chart.js & widget Inventory Alerts"),
    ("6", "Pencarian Data Terpadu", "Lengkap", "Query PDO Prepared Statement LIKE :keyword pada semua tabel"),
    ("7", "Tampilan Responsif Bootstrap 5", "Lengkap", "Bootstrap 5.3.3 + Custom Storeflow UI modern dan responsif"),
    ("8", "Fitur Tambahan 1: Upload Gambar", "Lengkap", "Penyimpanan di uploads/ dengan penamaan unik uniqid()"),
    ("9", "Fitur Tambahan 2: Pagination", "Lengkap", "Paginasi 10 item per halaman dengan URL query filter tetap terjaga"),
    ("10", "Fitur Tambahan 3: Export CSV", "Lengkap", "barang/export_csv.php terformat UTF-8 BOM untuk Microsoft Excel"),
    ("11", "Inovasi: Notification Bell Stok Kritis", "Lengkap", "includes/topbar.php indikator live counter stok < 5 unit")
]

for i, row_data in enumerate(data_rubrik, start=1):
    row = tbl_rubrik.rows[i]
    fill_hex = "F8FAFC" if i % 2 == 0 else "FFFFFF"
    for j, val in enumerate(row_data):
        cell = row.cells[j]
        cell.width = widths_rubrik[j]
        set_cell_background(cell, fill_hex)
        set_cell_margins(cell, top=40, bottom=40, left=70, right=70)
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [0, 2] else WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.space_after = Pt(0)
        r = p.add_run(val)
        color = (22, 163, 74) if j == 2 else (0,0,0)
        set_font(r, "Tahoma", 7.8, bold=(j in [1, 2]), color_rgb=color)

add_heading_sub(doc, "Kesimpulan Pengujian", space_before=8)
add_p(doc, 
    "Berdasarkan seluruh hasil implementasi dan pengujian fungsional, sistem Storeflow - Inventori Sembako telah memenuhi "
    "seluruh kriteria fungsional dan teknis yang dipersyaratkan dengan tingkat keberhasilan 100%. Sistem siap dioperasikan dan didemonstrasikan di hadapan dosen pengampu.",
    space_after=0
)

# Save DOCX files
doc.save("PANDUAN.docx")
doc.save("LAPORAN_UTS.docx")
print("Files PANDUAN.docx and LAPORAN_UTS.docx created successfully!")
