<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data mahasiswa dari database
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ambil data pengumuman
$pengumuman_query = "SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 6";
$pengumuman_result = $conn->query($pengumuman_query);
$pengumuman = [];
while ($row = $pengumuman_result->fetch_assoc()) {
    $pengumuman[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DEL Portal Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: gradientShift 15s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            box-shadow: 0 4px 20px rgba(30, 60, 114, 0.3);
            animation: slideDown 0.8s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .navbar-brand i {
            margin-right: 10px;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e57c2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
            border-radius: 0 0 50px 50px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .welcome-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            z-index: 1;
        }
        
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            height: 100%;
            position: relative;
            overflow: hidden;
            animation: cardSlideUp 0.8s ease-out;
        }
        
        @keyframes cardSlideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
            transition: 0.8s;
        }
        
        .info-card:hover::before {
            left: 100%;
        }
        
        .info-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(42, 82, 152, 0.3);
        }
        
        .info-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .icon-purple {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .icon-success {
            background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%);
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .icon-warning {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .btn-khs {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
        }
        
        .btn-khs:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 30px rgba(42, 82, 152, 0.5);
        }
        
        .announcement-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 5px solid #2a5298;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .announcement-card:hover {
            box-shadow: 0 8px 25px rgba(42, 82, 152, 0.2);
            transform: translateX(15px);
            border-left-color: #1e3c72;
        }
        
        .badge-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="khs.php?id=<?= $user_id ?>">
                            <i class="fas fa-file-alt"></i> Lihat Nilai
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="documents.php">
                            <i class="fas fa-folder"></i> Dokumen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pengumuman.php">
                            <i class="fas fa-bullhorn"></i> Pengumuman
                        </a>
                    </li>
                    <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="fas fa-shield-alt"></i> Admin
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold mb-3">
                            <i class="fas fa-hand-wave"></i> Selamat Datang, <?= htmlspecialchars($user['nama']) ?>!
                        </h1>
                        <p class="lead mb-0">
                            <i class="fas fa-id-card me-2"></i> NIM: <?= htmlspecialchars($user['nim']) ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i> <?= htmlspecialchars($user['prodi']) ?> - Semester <?= $user['semester'] ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div style="font-size: 80px; opacity: 0.3;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <!-- Info Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="info-card">
                    <div class="icon icon-purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2">Indeks Prestasi Kumulatif</h3>
                    <h2 class="display-4 fw-bold mb-0" style="color: #667eea;"><?= number_format($user['ipk'], 2) ?></h2>
                    <p class="text-muted mb-0">dari skala 4.00</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="info-card">
                    <div class="icon icon-success">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2">Total SKS</h3>
                    <h2 class="display-4 fw-bold mb-0" style="color: #11998e;"><?= $user['total_sks'] ?></h2>
                    <p class="text-muted mb-0">SKS telah ditempuh</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="info-card">
                    <div class="icon icon-warning">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="h5 fw-bold mb-2">Status Mahasiswa</h3>
                    <h2 class="display-6 fw-bold mb-0" style="color: #f5576c;"><?= htmlspecialchars($user['status']) ?></h2>
                    <p class="text-muted mb-0">Semester <?= $user['semester'] ?></p>
                </div>
            </div>
        </div>

        <!-- CTA Button -->
        <div class="text-center mb-5">
            <a href="khs.php?id=<?= $user_id ?>" class="btn btn-primary btn-khs btn-lg">
                <i class="fas fa-file-alt me-2"></i> Lihat Kartu Hasil Studi
            </a>
        </div>

        <!-- Pengumuman -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0 fw-bold">
                        <i class="fas fa-bullhorn" style="color: #667eea;"></i> Pengumuman Kampus
                    </h3>
                    <a href="pengumuman.php" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list me-2"></i>Lihat Semua
                    </a>
                </div>
            </div>
            
            <?php foreach ($pengumuman as $item): ?>
            <div class="col-md-6 mb-3">
                <a href="pengumuman.php?id=<?= $item['id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="announcement-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($item['judul']) ?></h5>
                            <span class="badge badge-custom"><?= date('d M Y', strtotime($item['tanggal'])) ?></span>
                        </div>
                        <p class="text-muted mb-2">
                            <?= substr(htmlspecialchars($item['isi']), 0, 100) ?>...
                        </p>
                        <div class="text-primary">
                            <small><i class="fas fa-arrow-right me-1"></i>Baca Selengkapnya</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">© 2025 DEL Portal Kampus. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 text-muted">
                        <i class="fas fa-shield-alt text-danger"></i> 
                        <small>Vulnerability Demo - Educational Purpose Only</small>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
