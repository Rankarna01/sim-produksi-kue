<?php
// login_logic.php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    // Validasi input kosong
    if (empty(trim($username))) {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak boleh kosong!']);
        exit;
    }

    // Cari user berdasarkan username menggunakan Prepared Statement
    $stmt = $pdo->prepare("SELECT id, name, username, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Login berhasil (Hanya cek username sesuai permintaan)
        // Set Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Tentukan arah redirect berdasarkan role
        $redirect_url = '/sim-produksi-kue/'; 
        if ($user['role'] === 'owner') {
            $redirect_url .= 'owner/dashboard/';
        } elseif ($user['role'] === 'produksi') {
            $redirect_url .= 'produksi/input_produksi/';
        } elseif ($user['role'] === 'admin') {
            $redirect_url .= 'admin/scan_barcode/';
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Login berhasil!', 
            'redirect' => $redirect_url
        ]);
    } else {
        // Username tidak ditemukan
        echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan di sistem!']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>