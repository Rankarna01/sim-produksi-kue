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

    // SELECT * agar semua kolom terbaca
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $login_success = false;

        // 1. Cek Bcrypt
        if (password_verify($password, $user['password'])) {
            $login_success = true;
        } 
        // 2. Transisi Plain Text ke Bcrypt
        else if ($password === $user['password']) {
            $login_success = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);
        }

        if ($login_success) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role']; // Ini menyimpan slug role-nya (cth: admin_gudang, produksi)
            
            if (isset($user['kitchen_id'])) {
                $_SESSION['kitchen_id'] = $user['kitchen_id'];
            }

            // Routing
            $is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
            $base = $is_localhost ? '/sim-produksi-kue/' : '/';
            
            // ==========================================
            // ROUTING DINAMIS BERDASARKAN ROLE (SLUG)
            // ==========================================
            if ($user['role'] === 'produksi') {
                $redirect_url = $base . 'produksi/input_produksi/';
            } 
            elseif ($user['role'] === 'admin') {
                $redirect_url = $base . 'admin/scan_barcode/';
            } 
            // CEK JIKA ROLE ADALAH MILIK GUDANG (Role dinamis yg dibuat di Gudang Roles)
            // Asumsi: Semua role gudang (admin_gudang, spv_gudang) akan diarahkan ke dashboard gudang
            elseif (strpos($user['role'], 'gudang') !== false || $user['role'] === 'admin_gudang') {
                $redirect_url = $base . 'gudang/dashboard/';
            } 
            elseif ($user['role'] === 'owner' || $user['role'] === 'auditor') {
                $redirect_url = $base . 'owner/dashboard/'; 
            } 
            else {
                // Fallback jika role tidak spesifik
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