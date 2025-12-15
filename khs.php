<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// CELAH KEAMANAN IDOR: Mengambil ID dari URL tanpa validasi!
// Seharusnya cek apakah ID yang diminta = ID user yang login
$requested_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$logged_user_id = $_SESSION['user_id'];

$conn = getConnection();

// VULNERABLE: Langsung ambil data berdasarkan ID dari URL
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->bind_param("i", $requested_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data tidak ditemukan!");
}

$mahasiswa = $result->fetch_assoc();

// Ambil data nilai dengan JOIN ke mata kuliah
$nilai_query = "
    SELECT n.*, m.kode, m.nama, m.sks 
    FROM nilai n 
    JOIN matakuliah m ON n.matakuliah_id = m.id 
    WHERE n.mahasiswa_id = ? 
    ORDER BY m.kode
";
$nilai_stmt = $conn->prepare($nilai_query);
$nilai_stmt->bind_param("i", $requested_id);
$nilai_stmt->execute();
$nilai_result = $nilai_stmt->get_result();

$matakuliah = [];
while ($row = $nilai_result->fetch_assoc()) {
    $matakuliah[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Hasil Studi - DEL Portal Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .khs-container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .khs-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        
        .khs-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,122.7C1248,107,1344,85,1392,74.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') bottom center no-repeat;
            opacity: 0.3;
        }
        
        .university-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .university-logo i {
            font-size: 40px;
            color: #667eea;
        }
        
        .khs-body {
            padding: 40px;
        }
        
        .student-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #667eea;
        }
        
        .info-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            width: 200px;
            color: #495057;
        }
        
        .info-value {
            flex: 1;
            color: #212529;
        }
        
        .table-nilai {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table-nilai thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table-nilai thead th {
            border: none;
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        .table-nilai tbody tr {
            transition: all 0.3s;
        }
        
        .table-nilai tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }
        
        .table-nilai tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .grade-A { background: #d4edda; color: #155724; }
        .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-C { background: #fff3cd; color: #856404; }
        .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f5c6cb; color: #721c24; }
        
        .ipk-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
        }
        
        .ipk-value {
            font-size: 48px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .warning-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .secret-box {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .btn-print {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(17, 153, 142, 0.3);
            color: white;
        }
        
        @media print {
            .navbar, .btn-print, .no-print { display: none; }
            .khs-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark no-print">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-university"></i> DEL Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="khs.php?id=<?= $logged_user_id ?>">
                            <i class="fas fa-file-alt"></i> Lihat Nilai
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KHS Content -->
    <div class="container">
        <div class="khs-container">
            <!-- Header -->
            <div class="khs-header">
                <div class="university-logo">
                    <i class="fas fa-university"></i>
                </div>
                <h2 class="mb-2 fw-bold">INSTITUT TEKNOLOGI DEL</h2>
                <h4 class="mb-1">KARTU HASIL STUDI (KHS)</h4>
                <p class="mb-0">Semester Ganjil 2025/2026</p>
            </div>

            <!-- Body -->
            <div class="khs-body">
                <!-- Student Info -->
                <div class="student-info">
                    <h5 class="fw-bold mb-3" style="color: #667eea;">
                        <i class="fas fa-user-graduate me-2"></i>Informasi Mahasiswa
                    </h5>
                    <div class="info-row">
                        <div class="info-label">Nama</div>
                        <div class="info-value">: <?= htmlspecialchars($mahasiswa['nama']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">NIM</div>
                        <div class="info-value">: <?= htmlspecialchars($mahasiswa['nim']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Program Studi</div>
                        <div class="info-value">: <?= htmlspecialchars($mahasiswa['prodi']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Semester</div>
                        <div class="info-value">: <?= htmlspecialchars($mahasiswa['semester']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Status</div>
                        <div class="info-value">: <span class="badge bg-<?= $mahasiswa['status'] == 'Aktif' ? 'success' : 'danger' ?>"><?= htmlspecialchars($mahasiswa['status']) ?></span></div>
                    </div>
                </div>

                <!-- Warning jika melihat data orang lain -->
                <?php if ($requested_id != $logged_user_id): ?>
                <div class="warning-box no-print">
                    <h5 class="mb-2">
                        <i class="fas fa-exclamation-triangle"></i> Peringatan Keamanan!
                    </h5>
                    <p class="mb-0">
                        <strong>IDOR Vulnerability Detected!</strong><br>
                        Anda sedang melihat data mahasiswa dengan ID: <?= $requested_id ?>, 
                        padahal Anda login sebagai ID: <?= $logged_user_id ?>.<br>
                        <small class="text-muted">Ini adalah celah keamanan yang seharusnya tidak ada di aplikasi production.</small>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Tabel Nilai -->
                <h5 class="fw-bold mb-3 mt-4" style="color: #667eea;">
                    <i class="fas fa-list-alt me-2"></i>Daftar Nilai Mata Kuliah
                </h5>
                
                <table class="table table-nilai mb-0">
                    <thead>
                        <tr>
                            <th width="10%">No</th>
                            <th width="15%">Kode MK</th>
                            <th width="40%">Nama Mata Kuliah</th>
                            <th width="10%" class="text-center">SKS</th>
                            <th width="15%" class="text-center">Nilai Huruf</th>
                            <th width="10%" class="text-center">Nilai Angka</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (count($matakuliah) > 0):
                            $no = 1;
                            foreach ($matakuliah as $mk): 
                                // Tentukan class badge berdasarkan nilai
                                $grade_class = 'grade-' . substr($mk['nilai_huruf'], 0, 1);
                        ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($mk['kode']) ?></strong></td>
                            <td><?= htmlspecialchars($mk['nama']) ?></td>
                            <td class="text-center"><?= $mk['sks'] ?></td>
                            <td class="text-center">
                                <span class="grade-badge <?= $grade_class ?>">
                                    <?= htmlspecialchars($mk['nilai_huruf']) ?>
                                </span>
                            </td>
                            <td class="text-center"><strong><?= number_format($mk['nilai_angka'], 2) ?></strong></td>
                        </tr>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data nilai</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- IPK Section -->
                <div class="ipk-section">
                    <h5 class="mb-2">Indeks Prestasi Kumulatif (IPK)</h5>
                    <div class="ipk-value"><?= number_format($mahasiswa['ipk'], 2) ?></div>
                    <p class="mb-0">Total SKS: <?= $mahasiswa['total_sks'] ?> SKS</p>
                </div>

                <!-- Secret Box untuk Anak Rektor -->
                <?php if (!empty($mahasiswa['catatan_khusus'])): ?>
                <div class="secret-box no-print">
                    <h5 class="mb-2" style="color: #721c24;">
                        <i class="fas fa-lock"></i> DATA RAHASIA TERUNGKAP!
                    </h5>
                    <p class="mb-0" style="color: #721c24;">
                        <?= htmlspecialchars($mahasiswa['catatan_khusus']) ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- Signature Section -->
                <div class="row mt-5 mb-4">
                    <div class="col-md-6">
                        <div class="text-center">
                            <p class="mb-5">Mahasiswa,</p>
                            <p class="mb-0"><strong><?= htmlspecialchars($mahasiswa['nama']) ?></strong></p>
                            <p class="text-muted mb-0">NIM: <?= htmlspecialchars($mahasiswa['nim']) ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <p class="mb-0">Jakarta, <?= date('d F Y') ?></p>
                            <p class="mb-5">Ketua Program Studi,</p>
                            <p class="mb-0"><strong>Dr. Ir. Susanto, M.Kom</strong></p>
                            <p class="text-muted mb-0">NIP: 197501152005011001</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center no-print">
                    <button onclick="window.print()" class="btn btn-print">
                        <i class="fas fa-print me-2"></i>Cetak KHS
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary" style="border-radius: 50px; padding: 12px 30px;">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- IDOR Demo Info -->
        <div class="alert alert-info mt-4 no-print" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-info-circle"></i> Demo IDOR Vulnerability
            </h5>
            <p class="mb-0">
                Coba ubah parameter URL: <code>khs.php?id=1</code>, <code>khs.php?id=2</code>, atau <code>khs.php?id=3</code> 
                untuk melihat data mahasiswa lain!<br>
                <strong>ID 3 = Data Rahasia Anak Rektor dengan IPK 1.5 tapi Lulus!</strong>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top no-print">
        <div class="container">
            <div class="text-center">
                    <p class="mb-1 text-muted">© 2025 DEL Portal Kampus - Institut Teknologi Del</p>
                <p class="mb-0">
                    <i class="fas fa-shield-alt text-danger"></i> 
                    <small class="text-danger fw-bold">WARNING: Aplikasi ini mengandung celah keamanan IDOR untuk tujuan pembelajaran!</small>
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
