<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Ambil daftar mata kuliah
$conn = getConnection();
$matakuliah_query = "SELECT * FROM matakuliah ORDER BY kode";
$matakuliah_result = $conn->query($matakuliah_query);
$matakuliah_list = [];
while ($row = $matakuliah_result->fetch_assoc()) {
    $matakuliah_list[] = $row;
}

// Proses registrasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $prodi = $_POST['prodi'] ?? '';
    $semester = (int)($_POST['semester'] ?? 0);
    
    // Validasi input
    if (empty($nama) || empty($nim) || empty($email) || empty($password) || empty($prodi) || $semester < 1) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $password_confirm) {
        $error = "Password dan konfirmasi password tidak sama!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek email dan NIM sudah terdaftar
        $check_stmt = $conn->prepare("SELECT id FROM mahasiswa WHERE email = ? OR nim = ?");
        $check_stmt->bind_param("ss", $email, $nim);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email atau NIM sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert mahasiswa
            $insert_stmt = $conn->prepare("INSERT INTO mahasiswa (nama, nim, email, password, prodi, semester, status, total_sks, ipk) VALUES (?, ?, ?, ?, ?, ?, 'Aktif', 0, 0.00)");
            $insert_stmt->bind_param("sssssi", $nama, $nim, $email, $password_hash, $prodi, $semester);
            
            if ($insert_stmt->execute()) {
                $mahasiswa_id = $conn->insert_id;
                
                // Insert nilai jika ada
                $total_sks = 0;
                $total_nilai = 0;
                $jumlah_mk = 0;
                
                if (isset($_POST['matakuliah']) && is_array($_POST['matakuliah'])) {
                    foreach ($_POST['matakuliah'] as $mk_id) {
                        $nilai_huruf = $_POST['nilai_huruf'][$mk_id] ?? '';
                        
                        if (!empty($nilai_huruf)) {
                            // Konversi nilai huruf ke angka
                            $nilai_angka = 0;
                            switch ($nilai_huruf) {
                                case 'A': $nilai_angka = 4.0; break;
                                case 'A-': $nilai_angka = 3.7; break;
                                case 'B+': $nilai_angka = 3.5; break;
                                case 'B': $nilai_angka = 3.0; break;
                                case 'B-': $nilai_angka = 2.7; break;
                                case 'C+': $nilai_angka = 2.5; break;
                                case 'C': $nilai_angka = 2.0; break;
                                case 'C-': $nilai_angka = 1.7; break;
                                case 'D+': $nilai_angka = 1.5; break;
                                case 'D': $nilai_angka = 1.0; break;
                                case 'E': $nilai_angka = 0.0; break;
                            }
                            
                            // Insert nilai
                            $nilai_stmt = $conn->prepare("INSERT INTO nilai (mahasiswa_id, matakuliah_id, nilai_huruf, nilai_angka, semester) VALUES (?, ?, ?, ?, ?)");
                            $nilai_stmt->bind_param("iisdi", $mahasiswa_id, $mk_id, $nilai_huruf, $nilai_angka, $semester);
                            $nilai_stmt->execute();
                            
                            // Hitung untuk IPK
                            $mk_sks_query = $conn->query("SELECT sks FROM matakuliah WHERE id = $mk_id");
                            $mk_sks = $mk_sks_query->fetch_assoc()['sks'];
                            $total_sks += $mk_sks;
                            $total_nilai += ($nilai_angka * $mk_sks);
                            $jumlah_mk++;
                        }
                    }
                }
                
                // Update total SKS dan IPK
                $ipk = $total_sks > 0 ? $total_nilai / $total_sks : 0;
                $update_stmt = $conn->prepare("UPDATE mahasiswa SET total_sks = ?, ipk = ? WHERE id = ?");
                $update_stmt->bind_param("idi", $total_sks, $ipk, $mahasiswa_id);
                $update_stmt->execute();
                
                $success = "Registrasi berhasil! Silakan login dengan email dan password Anda.";
            } else {
                $error = "Gagal melakukan registrasi. Silakan coba lagi.";
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DEL Portal Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .logo-circle i {
            font-size: 40px;
            color: #667eea;
        }
        
        .register-body {
            padding: 40px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: transform 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .section-title {
            color: #667eea;
            font-weight: 700;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .nilai-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border: 2px solid #e9ecef;
        }
        
        .nilai-item:hover {
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <div class="logo-circle">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="mb-2">Registrasi Mahasiswa Baru</h3>
                    <p class="mb-0">DEL Portal Kampus</p>
                </div>
                
                <div class="register-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                            <br><a href="index.php" class="alert-link">Klik di sini untuk login</a>
                        </div>
                    <?php else: ?>
                    
                    <form method="POST" action="">
                        <h5 class="section-title">
                            <i class="fas fa-user me-2"></i>Data Pribadi
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" required placeholder="Masukkan nama lengkap">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nim" required placeholder="Contoh: 2021001">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required placeholder="nama@kampus.ac.id">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" required placeholder="Minimal 6 karakter">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirm" required placeholder="Ulangi password">
                            </div>
                        </div>
                        
                        <h5 class="section-title">
                            <i class="fas fa-graduation-cap me-2"></i>Data Akademik
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                                <select class="form-select" name="prodi" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Ilmu Komputer">Ilmu Komputer</option>
                                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                                    <option value="Teknik Komputer">Teknik Komputer</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" name="semester" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                </select>
                            </div>
                        </div>
                        
                        <h5 class="section-title">
                            <i class="fas fa-file-alt me-2"></i>Nilai Mata Kuliah <small class="text-muted">(Opsional)</small>
                        </h5>
                        
                        <div id="nilai-container">
                            <?php foreach ($matakuliah_list as $mk): ?>
                            <div class="nilai-item">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="matakuliah[]" value="<?= $mk['id'] ?>" id="mk<?= $mk['id'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <strong><?= htmlspecialchars($mk['kode']) ?></strong>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label mb-0" for="mk<?= $mk['id'] ?>">
                                            <?= htmlspecialchars($mk['nama']) ?>
                                        </label>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <span class="badge bg-secondary"><?= $mk['sks'] ?> SKS</span>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select form-select-sm" name="nilai_huruf[<?= $mk['id'] ?>]">
                                            <option value="">-</option>
                                            <option value="A">A (4.0)</option>
                                            <option value="A-">A- (3.7)</option>
                                            <option value="B+">B+ (3.5)</option>
                                            <option value="B">B (3.0)</option>
                                            <option value="B-">B- (2.7)</option>
                                            <option value="C+">C+ (2.5)</option>
                                            <option value="C">C (2.0)</option>
                                            <option value="C-">C- (1.7)</option>
                                            <option value="D+">D+ (1.5)</option>
                                            <option value="D">D (1.0)</option>
                                            <option value="E">E (0.0)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-register w-100">
                                <i class="fas fa-user-plus me-2"></i> Daftar Sekarang
                            </button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <span class="text-muted">Sudah punya akun?</span>
                            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                Login di sini
                            </a>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
