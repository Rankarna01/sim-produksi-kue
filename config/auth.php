<?php
// config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DETEKSI OTOMATIS: Apakah berjalan di Localhost atau cPanel?
$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
define('BASE_URL', $is_localhost ? '/sim-produksi-kue/' : '/');

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Panggil database untuk pengecekan hak akses Real-Time
require_once __DIR__ . '/database.php';

// Fungsi internal untuk menarik hak akses dari database
function getUserPermissions() {
    global $pdo;
    if (!isset($_SESSION['role'])) return [];
    
    try {
        $role_slug = $_SESSION['role'];
        // Jika owner super admin, abaikan database (optional), tapi kita baca DB saja agar fleksibel
        $stmt = $pdo->prepare("SELECT permission_name FROM role_permissions WHERE role_slug = ?");
        $stmt->execute([$role_slug]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
}

// -------------------------------------------------------------
// FUNGSI LAMA (Dipertahankan agar menu lama tidak error)
// -------------------------------------------------------------
function checkRole($allowed_roles = []) {
    $user_role = $_SESSION['role'];
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        die("
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h1 style='color:red;'>403 - Akses Ditolak</h1>
                <p>Anda tidak memiliki izin (Jabatan) untuk mengakses halaman ini.</p>
                <a href='javascript:history.back()'>Kembali</a>
            </div>
        ");
    }
}

// -------------------------------------------------------------
// FUNGSI BARU 1: Pelindung Halaman Berdasarkan Hak Akses
// (Nanti ditaruh di baris paling atas file index.php menu-menu baru)
// -------------------------------------------------------------
function checkPermission($permission_name) {
    $perms = getUserPermissions();
    if (!in_array($permission_name, $perms)) {
        http_response_code(403);
        die("
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h1 style='color:red;'>403 - Akses Terkunci</h1>
                <p>Jabatan Anda tidak memiliki Hak Akses untuk membuka fitur ini.</p>
                <a href='javascript:history.back()'>Kembali</a>
            </div>
        ");
    }
}

// -------------------------------------------------------------
// FUNGSI BARU 2: Untuk Menyembunyikan/Menampilkan Menu di Sidebar
// -------------------------------------------------------------
function hasPermission($permission_name) {
    $perms = getUserPermissions();
    return in_array($permission_name, $perms);
}
?>