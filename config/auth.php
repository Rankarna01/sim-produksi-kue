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

// Panggil database
require_once __DIR__ . '/database.php';

// --- FUNGSI PENCATAT LOG KE DATABASE ---
function addLog($action, $menu, $description) {
    global $pdo;
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, menu, description, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $menu, $description, $ip]);
    } catch (PDOException $e) {
        // Abaikan jika gagal agar tidak menghentikan aplikasi
    }
}

function getUserPermissions() {
    global $pdo;
    if (!isset($_SESSION['role'])) return [];
    try {
        $role_slug = $_SESSION['role'];
        $stmt = $pdo->prepare("SELECT permission_name FROM role_permissions WHERE role_slug = ?");
        $stmt->execute([$role_slug]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) { return []; }
}

function checkRole($allowed_roles = []) {
    $user_role = $_SESSION['role'];
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h1 style='color:red;'>403 - Akses Ditolak</h1><a href='javascript:history.back()'>Kembali</a></div>");
    }
}

function checkPermission($permission_name) {
    $perms = getUserPermissions();
    if (!in_array($permission_name, $perms)) {
        http_response_code(403);
        die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h1 style='color:red;'>403 - Akses Terkunci</h1><a href='javascript:history.back()'>Kembali</a></div>");
    }
}

function hasPermission($permission_name) {
    $perms = getUserPermissions();
    return in_array($permission_name, $perms);
}

// --- MESIN OTOMATIS MONITORING (AUTO LOGGING) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $current_file = basename($_SERVER['PHP_SELF']);
    
    // Hanya record jika filenya logic.php (tempat transaksi terjadi)
    if ($current_file === 'logic.php') {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'Aksi Tidak Dikenal';
        
        // Deteksi Nama Menu dari Folder
        $folder_path = explode('/', dirname($_SERVER['PHP_SELF']));
        $menu_raw = end($folder_path);
        $menu_clean = ucwords(str_replace('_', ' ', $menu_raw));
        
        // Daftar action yang tidak perlu dicatat (biar DB gak penuh)
        $ignored_actions = ['read', 'get_materials', 'get_details', 'init_filter', 'get_permissions', 'read_history'];
        
        if (!in_array($action, $ignored_actions)) {
            // Ambil data POST (kecuali password) untuk detail log
            $data_copy = $_POST;
            if(isset($data_copy['password'])) $data_copy['password'] = '******';
            
            $json_data = json_encode($data_copy);
            $desc = "Eksekusi [$action] di menu [$menu_clean]. Data: $json_data";
            
            addLog($action, $menu_clean, $desc);
        }
    }
}
?>