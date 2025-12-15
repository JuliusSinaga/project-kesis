<?php
require_once 'config.php';

// Generate hash untuk password123
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Hash untuk 'password123': $hash\n\n";

// Update semua password di database
$conn = getConnection();
$stmt = $conn->prepare("UPDATE mahasiswa SET password = ?");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "✅ Password berhasil diupdate untuk semua user!\n";
    echo "Semua user sekarang bisa login dengan password: password123\n\n";
    
    // Tampilkan daftar user
    $result = $conn->query("SELECT id, nim, nama, email, role FROM mahasiswa ORDER BY id");
    echo "Daftar User:\n";
    echo str_repeat("-", 80) . "\n";
    while ($row = $result->fetch_assoc()) {
        echo sprintf("ID: %d | NIM: %s | Nama: %s | Email: %s | Role: %s\n", 
            $row['id'], $row['nim'], $row['nama'], $row['email'], $row['role']);
    }
    echo str_repeat("-", 80) . "\n";
} else {
    echo "❌ Error: " . $conn->error . "\n";
}

$conn->close();
?>
