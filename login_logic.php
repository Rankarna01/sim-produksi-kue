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

    $stmt = $pdo->prepare("SELECT id, name, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        $login_success = false;

        // 1. Cek apakah password cocok dengan Hash Bcrypt
        if (password_verify($password, $user['password'])) {
            $login_success = true;
        } 
        // 2. Trik Transisi: Jika password di database belum di-hash (masih teks biasa spt "123456")
        else if ($password === $user['password']) {
            $login_success = true;
            // Otomatis ubah password jadul tersebut menjadi Hash Bcrypt agar aman
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);
        }

        if ($login_success) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Deteksi Localhost atau cPanel agar routing dinamis
            $is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
            $redirect_url = $is_localhost ? '/sim-produksi-kue/' : '/';
            
            // ==========================================
            // PERBAIKAN: Routing Dinamis RBAC
            // ==========================================
            if ($user['role'] === 'produksi') {
                $redirect_url .= 'produksi/input_produksi/';
            } elseif ($user['role'] === 'admin') {
                $redirect_url .= 'admin/scan_barcode/';
            } else {
                // SEMUA ROLE LAIN (Owner, Auditor, Supervisor, Kasir, dll) 
                // akan otomatis diarahkan ke pintu utama ini.
                $redirect_url .= 'owner/dashboard/'; 
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