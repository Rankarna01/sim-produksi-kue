<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_user');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. BACA DATA (READ)
    if ($action === 'read') {
        $sql = "
            SELECT r.id, r.role_slug, r.role_name, COUNT(rp.id) as total_akses 
            FROM roles r 
            LEFT JOIN role_permissions rp ON r.role_slug = rp.role_slug 
            GROUP BY r.role_slug 
            ORDER BY r.id ASC
        ";
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. AMBIL AKSES SPESIFIK (UNTUK MODAL EDIT)
    if ($action === 'get_permissions') {
        $slug = $_GET['slug'];
        
        $stmtRole = $pdo->prepare("SELECT role_name, role_slug FROM roles WHERE role_slug = ?");
        $stmtRole->execute([$slug]);
        $role = $stmtRole->fetch(PDO::FETCH_ASSOC);

        $stmtPerm = $pdo->prepare("SELECT permission_name FROM role_permissions WHERE role_slug = ?");
        $stmtPerm->execute([$slug]);
        $permissions = $stmtPerm->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['status' => 'success', 'role' => $role, 'permissions' => $permissions]);
        exit;
    }

    // 3. SIMPAN DATA (CREATE / UPDATE)
    if ($action === 'save') {
        $mode = $_POST['mode']; // 'add' atau 'edit'
        $old_slug = $_POST['old_slug'] ?? '';
        $role_name = trim($_POST['role_name']);
        
        // Bersihkan slug (Hanya boleh huruf kecil, angka, dan underscore)
        $role_slug = strtolower(trim($_POST['role_slug']));
        $role_slug = preg_replace('/[^a-z0-9_]/', '', $role_slug);
        
        $permissions = $_POST['permissions'] ?? [];

        if (empty($role_name) || empty($role_slug)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan Kode Slug wajib diisi!']); exit;
        }

        $pdo->beginTransaction();

        if ($mode === 'add') {
            // Cek duplikat slug
            $cek = $pdo->prepare("SELECT id FROM roles WHERE role_slug = ?");
            $cek->execute([$role_slug]);
            if ($cek->rowCount() > 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Kode Slug sudah digunakan oleh jabatan lain!']); exit;
            }

            // Insert Role Baru
            $stmt = $pdo->prepare("INSERT INTO roles (role_slug, role_name) VALUES (?, ?)");
            $stmt->execute([$role_slug, $role_name]);

        } else if ($mode === 'edit') {
            // Update Role Lama
            $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, role_slug = ? WHERE role_slug = ?");
            $stmt->execute([$role_name, $role_slug, $old_slug]);

            // Hapus semua hak akses lama untuk ditimpa yang baru
            $del = $pdo->prepare("DELETE FROM role_permissions WHERE role_slug = ?");
            $del->execute([$role_slug]);
        }

        // Insert Hak Akses (Permissions) Baru
        if (!empty($permissions)) {
            $insertPerm = $pdo->prepare("INSERT INTO role_permissions (role_slug, permission_name) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                $insertPerm->execute([$role_slug, $perm]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Jabatan dan Hak Akses berhasil disimpan!']);
        exit;
    }

    // 4. HAPUS DATA (DELETE)
    if ($action === 'delete') {
        $slug = $_POST['slug'];

        // PROTEKSI: Jangan biarkan jabatan inti dihapus!
        $protected_roles = ['owner', 'admin', 'produksi', 'auditor'];
        if (in_array($slug, $protected_roles)) {
            echo json_encode(['status' => 'error', 'message' => 'Peringatan! Jabatan Inti Sistem tidak boleh dihapus.']); exit;
        }

        // PROTEKSI 2: Cek apakah masih ada User yang pakai jabatan ini
        $cekUser = $pdo->prepare("SELECT id FROM users WHERE role = ?");
        $cekUser->execute([$slug]);
        if ($cekUser->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Masih ada akun Pengguna (User) yang terdaftar dengan jabatan ini.']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM roles WHERE role_slug = ?");
        $stmt->execute([$slug]);

        echo json_encode(['status' => 'success', 'message' => 'Jabatan berhasil dihapus!']);
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>