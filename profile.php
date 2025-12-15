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

$success = '';
$error = '';

// VULNERABILITY: CSRF - Tidak ada CSRF token protection!
// VULNERABILITY: Insecure File Upload - Tidak ada validasi file type!
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Update profil
    if (isset($_POST['update_profile'])) {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        
        $update_stmt = $conn->prepare("UPDATE mahasiswa SET nama = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $nama, $email, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['nama'] = $nama;
            $_SESSION['email'] = $email;
            $success = "Profile berhasil diupdate!";
        }
    }
    
    // Upload foto profil - VULNERABLE!
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
        $upload_dir = 'uploads/';
        
        // Buat folder jika belum ada
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // VULNERABILITY: Tidak ada validasi extension/MIME type!
        // User bisa upload .php, .exe, dll
        $file_name = $_FILES['foto_profil']['name'];
        $file_path = $upload_dir . $file_name;
        
        // VULNERABILITY: Tidak ada sanitasi nama file
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $file_path)) {
            // Update database
            $foto_stmt = $conn->prepare("UPDATE mahasiswa SET foto_profil = ? WHERE id = ?");
            $foto_stmt->bind_param("si", $file_name, $user_id);
            $foto_stmt->execute();
            
            $success = "Foto profil berhasil diupload!";
            $user['foto_profil'] = $file_name;
        } else {
            $error = "Gagal upload file!";
        }
    }
    
    // Ganti password
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (password_verify($old_password, $user['password'])) {
            if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pass_stmt = $conn->prepare("UPDATE mahasiswa SET password = ? WHERE id = ?");
                $pass_stmt->bind_param("si", $hashed, $user_id);
                $pass_stmt->execute();
                $success = "Password berhasil diubah!";
            } else {
                $error = "Password baru tidak cocok atau kurang dari 6 karakter!";
            }
        } else {
            $error = "Password lama salah!";
        }
    }
    
    // Refresh data user
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - DEL Portal Kampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            box-shadow: 0 4px 20px rgba(30, 60, 114, 0.3);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .profile-container {
            max-width: 900px;
            margin: 30px auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            color: white;
            text-align: center;
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .vulnerability-badge {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
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
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user"></i> Profile
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

    <div class="container profile-container">
        <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-card">
            <div class="profile-header">
                <img src="uploads/<?= htmlspecialchars($user['foto_profil']) ?>" 
                     alt="Profile" 
                     class="profile-photo"
                     onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['nama']) ?>&size=150&background=667eea&color=fff'">
                <h3 class="mt-3 mb-1"><?= htmlspecialchars($user['nama']) ?></h3>
                <p class="mb-0"><?= htmlspecialchars($user['nim']) ?> | <?= htmlspecialchars($user['prodi']) ?></p>
            </div>
            
            <div class="profile-body">
                <!-- Upload Foto -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="fas fa-camera"></i> Upload Foto Profil
                        <span class="vulnerability-badge ms-2">
                            <i class="fas fa-bug"></i> VULNERABLE: Insecure File Upload
                        </span>
                    </h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control" name="foto_profil" accept="*">
                            <small class="text-muted">⚠️ Tidak ada validasi file type - bisa upload file berbahaya!</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Foto
                        </button>
                    </form>
                </div>

                <!-- Update Profile -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="fas fa-edit"></i> Update Profile
                        <span class="vulnerability-badge ms-2">
                            <i class="fas fa-bug"></i> VULNERABLE: No CSRF Protection
                        </span>
                    </h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="form-section">
                    <h5 class="mb-3">
                        <i class="fas fa-key"></i> Ganti Password
                        <span class="vulnerability-badge ms-2">
                            <i class="fas fa-bug"></i> VULNERABLE: No CSRF Protection
                        </span>
                    </h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-lock"></i> Ubah Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
