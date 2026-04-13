<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Hanya user dengan izin master_user (Owner) yang bisa akses logic ini
checkPermission('master_user');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. BACA DATA SEMUA JABATAN (READ)
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

    // 3. SIMPAN DATA (TAMBAH / EDIT)
    if ($action === 'save') {
        $mode = $_POST['mode']; // 'add' atau 'edit'
        $old_slug = $_POST['old_slug'] ?? '';
        $role_name = trim($_POST['role_name']);
        
        // Sanitasi Slug: Huruf kecil, angka, dan underscore saja
        $role_slug = strtolower(trim($_POST['role_slug']));
        $role_slug = preg_replace('/[^a-z0-9_]/', '', $role_slug);
        
        $permissions = $_POST['permissions'] ?? [];

        if (empty($role_name) || empty($role_slug)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Jabatan dan Kode Slug wajib diisi!']); exit;
        }

        $pdo->beginTransaction();

        if ($mode === 'add') {
            // Cek jika slug sudah ada
            $cek = $pdo->prepare("SELECT id FROM roles WHERE role_slug = ?");
            $cek->execute([$role_slug]);
            if ($cek->rowCount() > 0) {
                $pdo->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'Kode Slug sudah digunakan jabatan lain!']); exit;
            }

            $stmt = $pdo->prepare("INSERT INTO roles (role_slug, role_name) VALUES (?, ?)");
            $stmt->execute([$role_slug, $role_name]);

        } else if ($mode === 'edit') {
            // Update Nama & Slug Jabatan
            $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, role_slug = ? WHERE role_slug = ?");
            $stmt->execute([$role_name, $role_slug, $old_slug]);

            // PENTING: Hapus SEMUA hak akses lama berdasarkan old_slug agar tidak duplikat/nyangkut
            $del = $pdo->prepare("DELETE FROM role_permissions WHERE role_slug = ?");
            $del->execute([$old_slug]);
        }

        // Insert Hak Akses (Permissions) Baru
        if (!empty($permissions)) {
            $insertPerm = $pdo->prepare("INSERT INTO role_permissions (role_slug, permission_name) VALUES (?, ?)");
            foreach ($permissions as $perm) {
                // Di sinilah keajaibannya, akses_dapur_1, dll otomatis tersimpan!
                $insertPerm->execute([$role_slug, $perm]);
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Data Jabatan dan Hak Akses berhasil diperbarui!']);
        exit;
    }

    // 4. HAPUS DATA JABATAN
    if ($action === 'delete') {
        $slug = $_POST['slug'];

        // PROTEKSI: Daftar Jabatan Inti yang dilarang dihapus
        $protected_roles = ['owner', 'admin', 'produksi', 'auditor', 'gudang_pilar', 'otorisasi'];
        if (in_array($slug, $protected_roles)) {
            echo json_encode(['status' => 'error', 'message' => 'Peringatan! Jabatan Inti Sistem tidak boleh dihapus.']); exit;
        }

        // PROTEKSI: Cek apakah masih ada User aktif yang menggunakan jabatan ini
        $cekUser = $pdo->prepare("SELECT id FROM users WHERE role = ?");
        $cekUser->execute([$slug]);
        if ($cekUser->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal! Masih ada akun Pengguna (User) yang menggunakan jabatan ini.']); exit;
        }

        $pdo->beginTransaction();
        
        // Hapus Izinnya dulu
        $delPerm = $pdo->prepare("DELETE FROM role_permissions WHERE role_slug = ?");
        $delPerm->execute([$slug]);

        // Hapus Jabatan
        $stmt = $pdo->prepare("DELETE FROM roles WHERE role_slug = ?");
        $stmt->execute([$slug]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Jabatan berhasil dihapus dari sistem!']);
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>