<?php
// login_logic.php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty(trim($username)) || empty(trim($password))) {
        echo json_encode(['status' => 'error', 'message' => 'Username dan Password wajib diisi!']);
        exit;
    }

    // PERBAIKAN 1: Gunakan SELECT * agar kolom baru seperti 'kitchen_id' otomatis terbaca
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $login_success = false;

        // 1. Cek apakah password cocok dengan Hash Bcrypt
        if (password_verify($password, $user['password'])) {
            $login_success = true;
        } 
        // 2. Trik Transisi: Jika password masih teks biasa
        else if ($password === $user['password']) {
            $login_success = true;
            // Otomatis ubah password menjadi Hash Bcrypt
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);
        }

        if ($login_success) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // PERBAIKAN 2: Simpan ID Dapur ke session jika akun tersebut dihubungkan ke Dapur
            if (isset($user['kitchen_id'])) {
                $_SESSION['kitchen_id'] = $user['kitchen_id'];
            }

            // Deteksi Localhost atau cPanel agar routing dinamis
            $is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
            $base = $is_localhost ? '/sim-produksi-kue/' : '/';
            
            // ==========================================
            // PERBAIKAN 3: Routing Dinamis Berdasarkan Role
            // ==========================================
            if ($user['role'] === 'produksi') {
                $redirect_url = $base . 'produksi/input_produksi/';
            } 
            elseif ($user['role'] === 'admin') {
                $redirect_url = $base . 'admin/scan_barcode/';
            } 
            elseif ($user['role'] === 'gudang_pilar') {
                // SEKAT BARU: Diarahkan ke folder khusus gudang pilar
                $redirect_url = $base . 'gudang/dashboard/';
            } 
            elseif ($user['role'] === 'owner' || $user['role'] === 'auditor') {
                // Role eksekutif yang memantau dashboard owner
                $redirect_url = $base . 'owner/dashboard/'; 
            } 
            else {
                // PERBAIKAN 4: Default fallback untuk Role Dinamis (Role kustom buatan Owner)
                // Kita arahkan ke layout utama RotiKu (owner/dashboard). 
                // Sistem RBAC akan otomatis hanya menampilkan menu yang dicentang untuk role tersebut.
                $redirect_url = $base . 'owner/dashboard/'; 
            }

            echo json_encode(['status' => 'success', 'redirect' => $redirect_url]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password yang Anda masukkan salah!']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan!']);
    }
}
?>