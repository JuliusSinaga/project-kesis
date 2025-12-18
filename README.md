# DEL Portal Kampus - Panduan Instalasi

## Cara Setup Database

### 1. Buat Database
Buka phpMyAdmin atau MySQL command line, kemudian jalankan:

```sql
CREATE DATABASE siakad_kampus;
```

### 2. Import File SQL
- **Via phpMyAdmin**: 
  - Pilih database `siakad_kampus`
  - Klik tab "Import"
  - Pilih file `database.sql`
  - Klik "Go"

- **Via Command Line**:
```bash
mysql -u root -p siakad_kampus < database.sql
```

### 3. Konfigurasi Database (Jika perlu)
Edit file `config.php` sesuai dengan setting MySQL Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Ganti jika berbeda
define('DB_PASS', '');              // Masukkan password jika ada
define('DB_NAME', 'siakad_kampus');
```

## Menjalankan Aplikasi

### Menggunakan PHP Built-in Server
```bash
cd d:\Project-Kesis
php -S localhost:8000
```

Akses: http://localhost:8000

### Menggunakan XAMPP/WAMP
1. Copy folder ke `htdocs` atau `www`
2. Akses: http://localhost/Project-Kesis

## Akun Demo

### Login dengan akun yang sudah ada:
- **Email**: budi@kampus.ac.id
- **Password**: password

atau

- **Email**: siti@kampus.ac.id
- **Password**: password

atau

- **Email**: ahmad@kampus.ac.id (Anak Rektor - Data Rahasia!)
- **Password**: password

### Atau Register Akun Baru:
Klik link "Daftar" di halaman login

## Testing IDOR Vulnerability

1. Login dengan akun apapun
2. Buka halaman KHS
3. Ubah parameter URL:
   - `khs.php?id=1` → Lihat data Budi (IPK 3.2)
   - `khs.php?id=2` → Lihat data Siti (IPK 4.0)
   - `khs.php?id=3` → Lihat data Ahmad (IPK 1.5 - Anak Rektor!)

## Struktur Database

### Tabel: mahasiswa
- id, nama, nim, email, password, prodi, semester, status, total_sks, ipk, catatan_khusus

### Tabel: matakuliah
- id, kode, nama, sks

### Tabel: nilai
- id, mahasiswa_id, matakuliah_id, nilai_huruf, nilai_angka, semester

### Tabel: pengumuman
- id, judul, isi, tanggal

## Fitur Aplikasi

✅ Login & Register dengan database MySQL
✅ Dashboard mahasiswa dengan info IPK & SKS
✅ Halaman KHS (Kartu Hasil Studi)
✅ Profile management dengan upload foto
✅ Upload & download dokumen
✅ Admin panel untuk kelola data mahasiswa
✅ Input nilai saat registrasi

## Kerentanan Keamanan (untuk Pembelajaran)

⚠️ **Aplikasi ini mengandung 7 vulnerability berbeda (selain XSS):**

1. **IDOR** (Insecure Direct Object Reference) - `khs.php`
2. **CSRF** (Cross-Site Request Forgery) - `profile.php`, `admin.php`
3. **Insecure File Upload** - `profile.php`, `documents.php`
4. **Path Traversal** - `download.php`
5. **Broken Access Control** - `admin.php`
6. **Information Disclosure** - Error messages
7. **SQL Injection** - `admin.php` search feature

📖 **Baca detail lengkap di:** [VULNERABILITY_REPORT.md](VULNERABILITY_REPORT.md)

## Catatan Penting

⚠️ **Aplikasi ini mengandung celah keamanan IDOR yang disengaja untuk tujuan pembelajaran keamanan web.**

⚠️ **JANGAN deploy ke production atau hosting publik!**

## Troubleshooting

### Error: Connection refused
- Pastikan MySQL/XAMPP sudah running
- Cek username dan password di `config.php`

### Error: Table doesn't exist
- Pastikan sudah import file `database.sql`
- Cek nama database sudah benar

### Password tidak cocok
- Password default untuk semua akun demo: **password**
- Password di-hash dengan `password_hash()` PHP
