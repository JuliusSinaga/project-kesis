<?php
session_start();
require_once 'config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// VULNERABILITY: Path Traversal - Tidak ada sanitasi path!
$file = $_GET['file'] ?? '';

if (empty($file)) {
    die("File parameter required!");
}

// VULNERABLE: Direct file access tanpa validasi
// User bisa akses file apapun dengan: ?file=../../config.php
$file_path = $file;

// Cek file exists
if (!file_exists($file_path)) {
    die("File tidak ditemukan: " . htmlspecialchars($file_path));
}

// VULNERABILITY: Information Disclosure via error messages
// Error message menampilkan full path

// Download file
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
