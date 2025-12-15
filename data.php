<?php
/**
 * Del Kampus - Database Dummy
 * File ini berisi array sebagai simulasi database
 * WARNING: File ini mengandung vulnerability untuk tujuan pembelajaran keamanan
 */

// Data Users
$users = [
    1 => [
        'id' => 1,
        'nama' => 'Budi Santoso',
        'nim' => '2021001',
        'email' => 'budi@kampus.ac.id',
        'password' => 'budi123',
        'prodi' => 'Teknik Informatika',
        'semester' => 5,
        'status' => 'Aktif'
    ],
    2 => [
        'id' => 2,
        'nama' => 'Siti Rahayu',
        'nim' => '2021002',
        'email' => 'siti@kampus.ac.id',
        'password' => 'siti123',
        'prodi' => 'Sistem Informasi',
        'semester' => 5,
        'status' => 'Aktif'
    ],
    3 => [
        'id' => 3,
        'nama' => 'Ahmad Wijaya Kusuma',
        'nim' => '2021003',
        'email' => 'ahmad@kampus.ac.id',
        'password' => 'ahmad123',
        'prodi' => 'Teknik Informatika',
        'semester' => 5,
        'status' => 'Lulus'
    ]
];

// Data Nilai Mahasiswa
$nilai = [
    1 => [ // Mahasiswa Biasa
        'mahasiswa_id' => 1,
        'ipk' => 3.2,
        'total_sks' => 89,
        'matakuliah' => [
            ['kode' => 'TI001', 'nama' => 'Pemrograman Web', 'sks' => 3, 'nilai' => 'B+', 'angka' => 3.5],
            ['kode' => 'TI002', 'nama' => 'Basis Data', 'sks' => 3, 'nilai' => 'A-', 'angka' => 3.7],
            ['kode' => 'TI003', 'nama' => 'Algoritma Pemrograman', 'sks' => 4, 'nilai' => 'B', 'angka' => 3.0],
            ['kode' => 'TI004', 'nama' => 'Jaringan Komputer', 'sks' => 3, 'nilai' => 'B+', 'angka' => 3.5],
            ['kode' => 'TI005', 'nama' => 'Sistem Operasi', 'sks' => 3, 'nilai' => 'A-', 'angka' => 3.7],
            ['kode' => 'MK001', 'nama' => 'Bahasa Inggris', 'sks' => 2, 'nilai' => 'B', 'angka' => 3.0]
        ]
    ],
    2 => [ // Mahasiswa Berprestasi
        'mahasiswa_id' => 2,
        'ipk' => 4.0,
        'total_sks' => 92,
        'matakuliah' => [
            ['kode' => 'SI001', 'nama' => 'Manajemen Proyek TI', 'sks' => 3, 'nilai' => 'A', 'angka' => 4.0],
            ['kode' => 'SI002', 'nama' => 'Analisis dan Perancangan Sistem', 'sks' => 4, 'nilai' => 'A', 'angka' => 4.0],
            ['kode' => 'SI003', 'nama' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'nilai' => 'A', 'angka' => 4.0],
            ['kode' => 'SI004', 'nama' => 'Keamanan Informasi', 'sks' => 3, 'nilai' => 'A', 'angka' => 4.0],
            ['kode' => 'SI005', 'nama' => 'Data Mining', 'sks' => 3, 'nilai' => 'A', 'angka' => 4.0],
            ['kode' => 'MK001', 'nama' => 'Bahasa Inggris', 'sks' => 2, 'nilai' => 'A', 'angka' => 4.0]
        ]
    ],
    3 => [ // Anak Rektor (Data Rahasia/Aib)
        'mahasiswa_id' => 3,
        'ipk' => 1.5,
        'total_sks' => 78,
        'matakuliah' => [
            ['kode' => 'TI001', 'nama' => 'Pemrograman Web', 'sks' => 3, 'nilai' => 'D', 'angka' => 1.0],
            ['kode' => 'TI002', 'nama' => 'Basis Data', 'sks' => 3, 'nilai' => 'D+', 'angka' => 1.5],
            ['kode' => 'TI003', 'nama' => 'Algoritma Pemrograman', 'sks' => 4, 'nilai' => 'E', 'angka' => 0.0],
            ['kode' => 'TI004', 'nama' => 'Jaringan Komputer', 'sks' => 3, 'nilai' => 'D', 'angka' => 1.0],
            ['kode' => 'TI005', 'nama' => 'Sistem Operasi', 'sks' => 3, 'nilai' => 'C-', 'angka' => 1.7],
            ['kode' => 'MK001', 'nama' => 'Bahasa Inggris', 'sks' => 2, 'nilai' => 'D+', 'angka' => 1.5]
        ],
        'catatan_khusus' => '🔒 RAHASIA: Lulus karena Ayahnya Rektor - IPK 1.5 tidak memenuhi syarat kelulusan standar (min 2.75)'
    ]
];

// Data Pengumuman
$pengumuman = [
    [
        'judul' => 'Pengumuman Jadwal UAS Semester Ganjil 2025/2026',
        'tanggal' => '10 Desember 2025',
        'isi' => 'Ujian Akhir Semester akan dilaksanakan mulai tanggal 15-22 Desember 2025. Harap mempersiapkan diri dengan baik.'
    ],
    [
        'judul' => 'Pembayaran UKT Semester Genap',
        'tanggal' => '5 Desember 2025',
        'isi' => 'Batas akhir pembayaran UKT Semester Genap 2025/2026 adalah 31 Desember 2025.'
    ],
    [
        'judul' => 'Workshop Keamanan Siber',
        'tanggal' => '1 Desember 2025',
        'isi' => 'Pendaftaran workshop "Web Security & IDOR Vulnerability" dibuka hingga 20 Desember 2025.'
    ]
];
