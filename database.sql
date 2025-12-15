-- Database: siakad_kampus
-- Buat database terlebih dahulu

CREATE DATABASE IF NOT EXISTS siakad_kampus;
USE siakad_kampus;

-- Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    prodi VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    status ENUM('Aktif', 'Lulus', 'Cuti', 'Non-Aktif') DEFAULT 'Aktif',
    total_sks INT DEFAULT 0,
    ipk DECIMAL(3,2) DEFAULT 0.00,
    catatan_khusus TEXT NULL,
    foto_profil VARCHAR(255) DEFAULT 'default.jpg',
    role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Mata Kuliah
CREATE TABLE IF NOT EXISTS matakuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Nilai
CREATE TABLE IF NOT EXISTS nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    matakuliah_id INT NOT NULL,
    nilai_huruf VARCHAR(2) NOT NULL,
    nilai_angka DECIMAL(3,2) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
    FOREIGN KEY (matakuliah_id) REFERENCES matakuliah(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Pengumuman
CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Data Dummy Mata Kuliah
INSERT INTO matakuliah (kode, nama, sks) VALUES
('TI001', 'Pemrograman Web', 3),
('TI002', 'Basis Data', 3),
('TI003', 'Algoritma Pemrograman', 4),
('TI004', 'Jaringan Komputer', 3),
('TI005', 'Sistem Operasi', 3),
('SI001', 'Manajemen Proyek TI', 3),
('SI002', 'Analisis dan Perancangan Sistem', 4),
('SI003', 'Pemrograman Berorientasi Objek', 3),
('SI004', 'Keamanan Informasi', 3),
('SI005', 'Data Mining', 3),
('MK001', 'Bahasa Inggris', 2),
('MK002', 'Pancasila', 2),
('MK003', 'Kewarganegaraan', 2);

-- Tabel Dokumen
CREATE TABLE IF NOT EXISTS dokumen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    jenis_dokumen VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Data Dummy Mahasiswa (password: password123)
INSERT INTO mahasiswa (nama, nim, email, password, prodi, semester, status, total_sks, ipk, catatan_khusus, foto_profil, role) VALUES
('Budi Santoso', '2021001', 'budi@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teknik Informatika', 5, 'Aktif', 89, 3.20, NULL, 'default.jpg', 'mahasiswa'),
('Siti Rahayu', '2021002', 'siti@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sistem Informasi', 5, 'Aktif', 92, 4.00, NULL, 'default.jpg', 'mahasiswa'),
('Ahmad Wijaya Kusuma', '2021003', 'ahmad@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teknik Informatika', 5, 'Lulus', 78, 1.50, '🔒 RAHASIA: Lulus karena Ayahnya Rektor - IPK 1.5 tidak memenuhi syarat kelulusan standar (min 2.75)', 'default.jpg', 'mahasiswa'),
('Admin SIAKAD', '9999999', 'admin@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 0, 'Aktif', 0, 0.00, NULL, 'default.jpg', 'admin');

-- Insert Data Dummy Nilai untuk Budi (ID 1)
INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, nilai_angka, semester) VALUES
(1, 1, 'B+', 3.5, 5),
(1, 2, 'A-', 3.7, 5),
(1, 3, 'B', 3.0, 5),
(1, 4, 'B+', 3.5, 5),
(1, 5, 'A-', 3.7, 5),
(1, 11, 'B', 3.0, 5);

-- Insert Data Dummy Nilai untuk Siti (ID 2)
INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, nilai_angka, semester) VALUES
(2, 6, 'A', 4.0, 5),
(2, 7, 'A', 4.0, 5),
(2, 8, 'A', 4.0, 5),
(2, 9, 'A', 4.0, 5),
(2, 10, 'A', 4.0, 5),
(2, 11, 'A', 4.0, 5);

-- Insert Data Dummy Nilai untuk Ahmad (ID 3)
INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, nilai_angka, semester) VALUES
(3, 1, 'D', 1.0, 5),
(3, 2, 'D+', 1.5, 5),
(3, 3, 'E', 0.0, 5),
(3, 4, 'D', 1.0, 5),
(3, 5, 'C-', 1.7, 5),
(3, 11, 'D+', 1.5, 5);

-- Insert Data Dummy Pengumuman
INSERT INTO pengumuman (judul, isi, tanggal) VALUES
('Pengumuman Jadwal UAS Semester Ganjil 2025/2026', 'Ujian Akhir Semester akan dilaksanakan mulai tanggal 15-22 Desember 2025. Harap mempersiapkan diri dengan baik.', '2025-12-10'),
('Pembayaran UKT Semester Genap', 'Batas akhir pembayaran UKT Semester Genap 2025/2026 adalah 31 Desember 2025.', '2025-12-05'),
('Workshop Keamanan Siber', 'Pendaftaran workshop "Web Security & IDOR Vulnerability" dibuka hingga 20 Desember 2025.', '2025-12-01');
