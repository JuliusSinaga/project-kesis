<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Proses login dari database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $conn = getConnection();
        
        // Cari user berdasarkan email
        $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['nim'] = $user['nim'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Email atau password salah!";
            }
        } else {
            $error = "Email atau password salah!";
        }
        
        $conn->close();
    } else {
        $error = "Email dan Password harus diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEL Portal Kampus - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: gradientShift 10s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.4);
            overflow: hidden;
            animation: slideUp 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e57c2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
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
        
        .logo-circle {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .logo-circle i {
            font-size: 50px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 14px 20px;
            border: 2px solid #e3f2fd;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            background: #f8f9fa;
        }
        
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.25rem rgba(42, 82, 152, 0.15);
            background: white;
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 12px 0 0 12px;
            color: white;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e57c2 100%);
            background-size: 200% auto;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.4s;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: 0.4s;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(42, 82, 152, 0.4);
            background-position: right center;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .footer-text {
            text-align: center;
            color: white;
            margin-top: 30px;
            font-size: 14px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .demo-info {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            color: white;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,0.2);
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        a {
            transition: all 0.3s;
        }
        
        a:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="login-container mx-auto">
                    <div class="login-card">
                        <div class="login-header">
                            <div class="logo-circle">
                                <i class="fas fa-university"></i>
                            </div>
                            <h3 class="mb-2">DEL Portal Kampus</h3>
                            <p class="mb-0">Sistem Informasi Akademik</p>
                        </div>
                        
                        <div class="login-body">
                            <h5 class="text-center mb-4" style="color: #333;">Selamat Datang!</h5>
                            
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label class="form-label" style="color: #666; font-weight: 500;">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" name="email" placeholder="nama@kampus.ac.id" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label" style="color: #666; font-weight: 500;">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label" for="remember" style="color: #666; font-size: 14px;">
                                        Ingat saya
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                                </button>
                                
                                <div class="text-center mt-3">
                                    <a href="#" style="color: #667eea; text-decoration: none; font-size: 14px;">
                                        <i class="fas fa-question-circle"></i> Lupa Password?
                                    </a>
                                </div>
                                
                                <div class="text-center mt-3 pt-3 border-top">
                                    <span class="text-muted">Belum punya akun?</span>
                                    <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                        Daftar Sekarang
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="demo-info">
                        <div class="text-center mb-2">
                            <i class="fas fa-info-circle"></i> <strong>Demo Login</strong>
                        </div>
                        <small>
                            Email: <strong>budi@kampus.ac.id</strong> | Password: <strong>password123</strong><br>
                            Atau <a href="register.php" style="color: white; text-decoration: underline; font-weight: 600;">KLIK DI SINI untuk daftar akun baru</a><br>
                            ⚠️ Aplikasi ini mengandung celah keamanan IDOR untuk tujuan pembelajaran.
                        </small>
                    </div>
                    
                    <div class="footer-text">
                        <p class="mb-0">© 2025 DEL Portal Kampus. All rights reserved.</p>
                        <small>Powered by PHP & Bootstrap 5</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
