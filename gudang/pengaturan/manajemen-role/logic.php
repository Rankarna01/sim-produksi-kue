<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('manage_roles');

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN SEMUA ROLE GUDANG
    if ($action === 'read') {
        $sql = "
            SELECT r.*, 
                   (SELECT COUNT(*) FROM gudang_role_permissions rp WHERE rp.role_id = r.id) as total_perms
            FROM gudang_roles r
            ORDER BY r.id ASC
        ";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. AMBIL DETAIL ROLE & PERMISSIONS-NYA
    if ($action === 'get_detail') {
        $id = $_GET['id'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM gudang_roles WHERE id = ?");
        $stmt->execute([$id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtPerms = $pdo->prepare("SELECT permission_slug FROM gudang_role_permissions WHERE role_id = ?");
        $stmtPerms->execute([$id]);
        $role['permissions'] = $stmtPerms->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['status' => 'success', 'data' => $role]);
        exit;
    }

    // 3. SIMPAN / UPDATE ROLE GUDANG
    if ($action === 'save') {
        $id = $_POST['role_id'] ?? '';
        $name = trim($_POST['role_name'] ?? '');
        $slug = strtolower(trim($_POST['role_slug'] ?? ''));
        $permissions = $_POST['permissions'] ?? [];

        if (empty($name) || empty($slug)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan Slug Jabatan wajib diisi!']); exit;
        }

        $pdo->beginTransaction();

        if (empty($id)) {
            $cek = $pdo->prepare("SELECT id FROM gudang_roles WHERE role_slug = ?");
            $cek->execute([$slug]);
            if($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Slug sudah digunakan oleh jabatan lain!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO gudang_roles (role_name, role_slug) VALUES (?, ?)");
            $stmt->execute([$name, $slug]);
            $id = $pdo->lastInsertId();
            $msg = "Jabatan baru berhasil dibuat!";
        } else {
            if ($id == 1) { $slug = 'admin_gudang'; } // Proteksi Admin Utama
            
            $stmt = $pdo->prepare("UPDATE gudang_roles SET role_name = ?, role_slug = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $id]);
            $msg = "Hak akses jabatan diperbarui!";
        }

        // Sinkronisasi Permissions
        $pdo->prepare("DELETE FROM gudang_role_permissions WHERE role_id = ?")->execute([$id]);
        
        if (!empty($permissions)) {
            $stmtPerm = $pdo->prepare("INSERT INTO gudang_role_permissions (role_id, permission_slug) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $stmtPerm->execute([$id, $perm]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

    // 4. HAPUS ROLE GUDANG
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        // PENTING: Ambil slug role-nya dulu untuk dicek
        $cekRole = $pdo->prepare("SELECT role_slug FROM gudang_roles WHERE id = ?");
        $cekRole->execute([$id]);
        $role_slug = $cekRole->fetchColumn();

        // ============================================================
        // PROTEKSI ROLE INTI (Hanya bisa di-edit, dilarang di-hapus)
        // ============================================================
        $protected_roles = ['owner_gudang', 'owner_produksi', 'admin_gudang', 'admin_produksi'];
        
        if (in_array($role_slug, $protected_roles)) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Jabatan Inti Master (Owner/Admin) dilarang dihapus demi keamanan sistem.']); 
            exit;
        }
        // ============================================================
        
        // Cek apakah role_slug ini sedang dipakai oleh user di tabel users
        $cekUser = $pdo->prepare("SELECT id FROM users WHERE role = ?");
        $cekUser->execute([$role_slug]);
        if($cekUser->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Jabatan ini tidak bisa dihapus karena masih ada User aktif yang menggunakannya!']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM gudang_roles WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Jabatan berhasil dihapus!']);
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>