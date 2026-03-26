<?php
// config/auth.php
session_start();

// Fungsi untuk mengecek hak akses berdasarkan role
function checkRole($allowed_roles = []) {
    // 1. Cek apakah user sudah login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Jika belum login, tendang ke halaman login
        header("Location: /sim-produksi-kue/index.php");
        exit();
    }

    // 2. Cek apakah role user saat ini ada di dalam daftar role yang diizinkan
    $user_role = $_SESSION['role'];
    if (!in_array($user_role, $allowed_roles)) {
        // Jika role tidak sesuai, tampilkan error 403 Forbidden
        http_response_code(403);
        die("
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h1 style='color:red;'>403 - Akses Ditolak</h1>
                <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <a href='javascript:history.back()'>Kembali</a>
            </div>
        ");
    }
}
?>