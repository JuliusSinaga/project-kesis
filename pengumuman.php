<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getConnection();

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ambil ID pengumuman
$pengumuman_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil detail pengumuman
if ($pengumuman_id > 0) {
    $pengumuman_stmt = $conn->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $pengumuman_stmt->bind_param("i", $pengumuman_id);
    $pengumuman_stmt->execute();
    $pengumuman_result = $pengumuman_stmt->get_result();
    
    if ($pengumuman_result->num_rows === 0) {
        header('Location: dashboard.php');
        exit;
    }
    
    $pengumuman = $pengumuman_result->fetch_assoc();
} else {
    // Ambil semua pengumuman
    $all_pengumuman_query = "SELECT * FROM pengumuman ORDER BY tanggal DESC";
    $all_pengumuman_result = $conn->query($all_pengumuman_query);
    $all_pengumuman = [];
    while ($row = $all_pengumuman_result->fetch_assoc()) {
        $all_pengumuman[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pengumuman_id > 0 ? htmlspecialchars($pengumuman['judul']) : 'Pengumuman Kampus' ?> - DEL Portal</title>
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
        
        .pengumuman-container {
            max-width: 900px;
            margin: 30px auto;
        }
        
        .pengumuman-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .pengumuman-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            color: white;
            text-align: center;
        }
        
        .pengumuman-body {
            padding: 40px;
        }
        
        .pengumuman-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
            color: #6c757d;
        }
        
        .pengumuman-content {
            font-size: 16px;
            line-height: 1.8;
            color: #495057;
        }
        
        .list-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 5px solid #667eea;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .list-card:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .badge-date {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
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
                        <a class="nav-link" href="dashboard.php">
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
                        <a class="nav-link active" href="pengumuman.php">
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

    <div class="container pengumuman-container">
        <?php if ($pengumuman_id > 0): ?>
            <!-- Detail Pengumuman -->
            <div class="pengumuman-card">
                <div class="pengumuman-header">
                    <i class="fas fa-bullhorn fa-3x mb-3"></i>
                    <h2 class="mb-0"><?= htmlspecialchars($pengumuman['judul']) ?></h2>
                </div>
                
                <div class="pengumuman-body">
                    <div class="pengumuman-meta">
                        <div>
                            <i class="fas fa-calendar-alt me-2"></i>
                            <strong><?= date('d F Y', strtotime($pengumuman['tanggal'])) ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-clock me-2"></i>
                            <?= date('H:i', strtotime($pengumuman['created_at'])) ?> WIB
                        </div>
                    </div>
                    
                    <div class="pengumuman-content">
                        <?= nl2br(htmlspecialchars($pengumuman['isi'])) ?>
                    </div>
                    
                    <div class="mt-4 pt-4 border-top">
                        <a href="pengumuman.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pengumuman
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- List Semua Pengumuman -->
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3><i class="fas fa-bullhorn me-2" style="color: #667eea;"></i>Semua Pengumuman Kampus</h3>
                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                
                <?php if (count($all_pengumuman) > 0): ?>
                    <?php foreach ($all_pengumuman as $item): ?>
                    <a href="pengumuman.php?id=<?= $item['id'] ?>" class="list-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-bullhorn me-2" style="color: #667eea;"></i>
                                <?= htmlspecialchars($item['judul']) ?>
                            </h5>
                            <span class="badge-date">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d M Y', strtotime($item['tanggal'])) ?>
                            </span>
                        </div>
                        <p class="text-muted mb-2">
                            <?= substr(htmlspecialchars($item['isi']), 0, 200) ?>...
                        </p>
                        <div class="text-primary">
                            <small><i class="fas fa-arrow-right me-1"></i>Baca Selengkapnya</small>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="pengumuman-card">
                        <div class="pengumuman-body text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada pengumuman</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
