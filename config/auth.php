<?php
// config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
define('BASE_URL', $is_localhost ? '/sim-produksi-kue/' : '/');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

require_once __DIR__ . '/database.php';

function addLog($action, $menu, $description) {
    global $pdo;
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, menu, description, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $menu, $description, $ip]);
    } catch (PDOException $e) { }
}

// =========================================================================
// MODE PRO: Fungsi Penarik Hak Akses (Merge Produksi & Gudang)
// =========================================================================
function getUserPermissions() {
    global $pdo;
    if (!isset($_SESSION['role'])) return [];
    
    try {
        $role_slug = $_SESSION['role'];
        
        // 1. Tarik Hak Akses dari Sistem Produksi (Tabel Lama)
        $stmtProd = $pdo->prepare("SELECT permission_name FROM role_permissions WHERE role_slug = ?");
        $stmtProd->execute([$role_slug]);
        $permsProd = $stmtProd->fetchAll(PDO::FETCH_COLUMN);

        // 2. Tarik Hak Akses dari Sistem Gudang (Tabel Baru)
        $stmtGudang = $pdo->prepare("
            SELECT p.permission_slug 
            FROM gudang_role_permissions p
            JOIN gudang_roles r ON p.role_id = r.id
            WHERE r.role_slug = ?
        ");
        $stmtGudang->execute([$role_slug]);
        $permsGudang = $stmtGudang->fetchAll(PDO::FETCH_COLUMN);

        // 3. Gabungkan Kedua Izin & Hapus Duplikat
        $permissions = array_unique(array_merge($permsProd, $permsGudang));
        
        $_SESSION['permissions'] = $permissions; 
        return $permissions;
    } catch (PDOException $e) {
        return [];
    }
}

function checkRole($allowed_roles = []) {
    $user_role = $_SESSION['role'];
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h1 style='color:red;'>403 - Akses Ditolak</h1><a href='javascript:history.back()'>Kembali</a></div>");
    }
}

// =========================================================================
// PENGECEKAN IZIN (Dengan VIP Bypass)
// =========================================================================
function checkPermission($permission_slug) {
    $role = $_SESSION['role'] ?? '';

    // VIP BYPASS: Jika jabatannya adalah bos/admin utama, loloskan semua tanpa syarat!
    if ($role === 'owner' || $role === 'owner_gudang' || $role === 'admin_gudang') { 
        return true; 
    }

    $perms = getUserPermissions();
    if (!in_array($permission_slug, $perms)) {
        http_response_code(403);
        die("<div style='padding:50px; text-align:center; font-family:sans-serif; background:#f8fafc; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;'>
                <h1 style='color:#e11d48; font-size:48px; margin-bottom:10px; font-weight:900;'>403</h1>
                <h2 style='color:#1e293b; font-size:24px; margin-bottom:10px; font-weight:900;'>Akses Terkunci</h2>
                <p style='color:#64748b; margin-bottom:30px;'>Maaf, jabatan Anda tidak memiliki izin untuk membuka fitur [<b style='color:#e11d48;'>$permission_slug</b>].</p>
                <button onclick='history.back()' style='background:#2563eb; color:white; font-weight:bold; padding:12px 24px; border:none; border-radius:12px; cursor:pointer; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);'>Kembali Sebelumnya</button>
             </div>");
    }
}

function hasPermission($permission_slug) {
    $role = $_SESSION['role'] ?? '';
    
    // VIP BYPASS
    if ($role === 'owner' || $role === 'owner_gudang' || $role === 'admin_gudang') { 
        return true; 
    }
    
    $perms = getUserPermissions();
    return in_array($permission_slug, $perms);
}

// --- MESIN OTOMATIS MONITORING (AUTO LOGGING) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $current_file = basename($_SERVER['PHP_SELF']);
    
    if ($current_file === 'logic.php') {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'Aksi Tidak Dikenal';
        
        $folder_path = explode('/', dirname($_SERVER['PHP_SELF']));
        $menu_raw = end($folder_path);
        $menu_clean = ucwords(str_replace('_', ' ', $menu_raw));
        
        $ignored_actions = ['read', 'get_materials', 'get_details', 'init_filter', 'get_permissions', 'read_history', 'get_detail', 'read_comparison'];
        
        if (!in_array($action, $ignored_actions)) {
            $data_copy = $_POST;
            if(isset($data_copy['password'])) $data_copy['password'] = '******';
            
            $json_data = json_encode($data_copy);
            $desc = "Eksekusi [$action] di menu [$menu_clean]. Data: $json_data";
            
            addLog($action, $menu_clean, $desc);
        }
    }
}
?>