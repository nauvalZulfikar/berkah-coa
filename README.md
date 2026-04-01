# BERKAH COA — Chart of Accounts

Aplikasi web untuk mengelola Chart of Accounts (COA) proyek BERKAH, berdasarkan dokumen TAF-07.001-012.

## Fitur

| TC | Fitur | Deskripsi |
|----|-------|-----------|
| TC-01 | Buat Akun Baru | Tambah akun via modal, kode internal auto-generate berdasarkan hierarki (1000, 1100, 1110, 1111) |
| TC-03 | Hierarki Induk-Anak | Tabel collapsable dengan parent-child hierarchy, validasi anti-siklik (BR-02) |
| TC-06 | Aktivasi / Non-Aktivasi | Toggle switch per akun via AJAX, histori tetap tersimpan (BR-09) |
| TC-12 | Export & Import Excel | Export data ke `.xlsx`, download template kosong, import dari Excel dengan auto-generate kode & deteksi duplikat |

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2
- **Database**: MySQL (production: db.zein-corp.com) / SQLite (offline)
- **Frontend**: Bootstrap 5.3, Bootstrap Icons
- **Excel**: maatwebsite/excel 3.x

## Persyaratan

- PHP >= 8.2
- Composer
- MySQL 5.7+ (atau SQLite untuk mode offline)
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `xml`, `zip`

## Setup

### 1. Clone & Install

```bash
git clone <repo-url>
cd TAF
composer install
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuai database:

**Production (MySQL):**
```
DB_CONNECTION=mysql
DB_HOST=db.zein-corp.com
DB_PORT=3306
DB_DATABASE=berkah
DB_USERNAME=berkah_taf
DB_PASSWORD=<password>
```

**Offline (SQLite):**
```
DB_CONNECTION=sqlite
```

Kalau pakai SQLite, buat file database dan seed data dummy:

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=DummyDataSeeder
```

### 3. Jalankan Server

```bash
php artisan serve
```

Buka http://localhost:8000

## Pemakaian

### Buat Akun Baru (TC-01)

1. Klik tombol **Buat Akun Baru**
2. Isi: Nama Akun, Tipe Akun, Akun Induk (opsional)
3. Kode internal otomatis di-generate:
   - Root (tanpa induk): `1000`, `2000`, `3000`, ...
   - Child: `1100`, `1200`, ...
   - Grandchild: `1110`, `1120`, ...
   - Great-grandchild: `1111`, `1112`, ...
4. Klik **Simpan Akun**

### Hierarki (TC-03)

- Klik row di tabel untuk expand/collapse anak-anaknya
- Chevron `>` berubah jadi `v` saat expanded
- Indentasi otomatis per level

### Aktivasi / Non-Aktivasi (TC-06)

- Klik toggle switch di kolom "Aktif/Nonaktif"
- Status berubah langsung tanpa reload halaman

### Export Excel (TC-12)

- Klik **Export Excel** untuk download semua data akun sebagai `.xlsx`

### Import Excel (TC-12)

1. Klik **Download Template** untuk dapat file template kosong
2. Isi template dengan kolom:
   - `nama` — nama akun (wajib)
   - `tipe_akun` — nama tipe akun, mis. Aset, Liabilitas (wajib)
   - `nama_induk` — nama akun induk (kosongkan jika root)
3. Klik **Import Excel**, pilih file, klik **Import**
4. Jika nama akun sudah ada di database, data akan di-update
5. Jika nama akun baru, akan dibuat dengan kode internal otomatis

## Struktur Database

| Tabel | Deskripsi |
|-------|-----------|
| `gl_mst_akun` | Master akun COA |
| `gl_ref_tipe_akun` | Referensi tipe akun (Aset, Liabilitas, Ekuitas, dll) |
| `core_ref_status_data` | Referensi status data (Aktif, Non Aktif, Draf, dll) |

### Pola Kode Internal

```
1000  -> Parent (digit 1)
1100  -> Child (digit 2)
1110  -> Grandchild (digit 3)
1111  -> Great-grandchild (digit 4)
```

Digit yang masih `0` = posisi kosong (belum ada anak di level itu).

## Routes

| Method | URI | Fungsi |
|--------|-----|--------|
| GET | `/` | Redirect ke daftar akun |
| GET | `/akun` | Daftar akun (index) |
| POST | `/akun` | Simpan akun baru |
| GET | `/akun/{kode}` | Detail akun |
| PATCH | `/akun/{kode}/toggle-aktif` | Toggle aktif/nonaktif |
| PATCH | `/akun/{kode}/induk` | Update akun induk |
| GET | `/akun/export` | Export Excel |
| GET | `/akun/template` | Download template import |
| POST | `/akun/import` | Import Excel |
