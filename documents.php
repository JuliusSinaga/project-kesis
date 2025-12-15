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

// Ambil dokumen user
$doc_stmt = $conn->prepare("SELECT * FROM dokumen WHERE mahasiswa_id = ? ORDER BY created_at DESC");
$doc_stmt->bind_param("i", $user_id);
$doc_stmt->execute();
$dokumen = $doc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Upload dokumen - VULNERABLE!
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dokumen'])) {
    $jenis = $_POST['jenis_dokumen'] ?? 'Lainnya';
    $upload_dir = 'documents/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // VULNERABILITY: Insecure file upload - no validation
    $file_name = $_FILES['dokumen']['name'];
    $file_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $file_path)) {
        $insert_stmt = $conn->prepare("INSERT INTO dokumen (mahasiswa_id, nama_file, file_path, jenis_dokumen) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("isss", $user_id, $file_name, $file_path, $jenis);
        $insert_stmt->execute();
        
        header('Location: documents.php?msg=success');
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen - DEL Portal</title>
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
        .doc-container {
            max-width: 1000px;
            margin: 30px auto;
        }
        .doc-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .vulnerability-badge {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-university"></i> DEL Portal
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="profile.php">Profile</a>
                <a class="nav-link active" href="documents.php">Dokumen</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container doc-container">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Dokumen berhasil diupload!
        </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="doc-card">
            <h4 class="mb-3">
                <i class="fas fa-cloud-upload-alt"></i> Upload Dokumen
                <span class="vulnerability-badge ms-2">
                    <i class="fas fa-bug"></i> VULNERABLE: Insecure File Upload
                </span>
            </h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Dokumen</label>
                        <select class="form-select" name="jenis_dokumen">
                            <option>KTP</option>
                            <option>KK</option>
                            <option>Ijazah</option>
                            <option>Transkrip Nilai</option>
                            <option>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">File Dokumen</label>
                        <input type="file" class="form-control" name="dokumen" required>
                        <small class="text-danger">⚠️ Tidak ada validasi - bisa upload .php, .exe!</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload
                </button>
            </form>
        </div>

        <!-- Document List -->
        <div class="doc-card">
            <h4 class="mb-3">
                <i class="fas fa-folder-open"></i> Dokumen Saya
                <span class="vulnerability-badge ms-2">
                    <i class="fas fa-bug"></i> VULNERABLE: Path Traversal
                </span>
            </h4>
            
            <?php if (count($dokumen) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Jenis Dokumen</th>
                            <th>Nama File</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dokumen as $doc): ?>
                        <tr>
                            <td><i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($doc['jenis_dokumen']) ?></td>
                            <td><?= htmlspecialchars($doc['nama_file']) ?></td>
                            <td><?= date('d M Y H:i', strtotime($doc['created_at'])) ?></td>
                            <td>
                                <a href="download.php?file=<?= urlencode($doc['file_path']) ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted text-center py-4">Belum ada dokumen yang diupload</p>
            <?php endif; ?>
        </div>

        <!-- Path Traversal Demo -->
        <div class="doc-card bg-warning bg-opacity-10">
            <h5 class="text-danger"><i class="fas fa-bug"></i> Path Traversal Demo</h5>
            <p>Coba akses file sensitive dengan URL:</p>
            <ul>
                <li><code>download.php?file=config.php</code> - Lihat konfigurasi database</li>
                <li><code>download.php?file=../database.sql</code> - Download struktur database</li>
                <li><code>download.php?file=../../windows/system32/drivers/etc/hosts</code> - Akses system files</li>
            </ul>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
