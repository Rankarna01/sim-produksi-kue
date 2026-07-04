<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_user');

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. Pastikan kolom warehouse_id ada di tabel users_pos
    try {
        $pdo->exec("ALTER TABLE users_pos ADD COLUMN warehouse_id INT NULL AFTER role_id");
    } catch (Exception $e) {
        // Abaikan jika kolom sudah ada
    }

    if ($action === 'init_data') {
        // Ambil daftar role POS
        $roles = $pdo->query("SELECT id, role_name FROM roles_pos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        // Ambil daftar Store / Gudang
        $stores = $pdo->query("SELECT id, code, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'roles' => $roles, 'stores' => $stores]);
        exit;
    }

    if ($action === 'read') {
        $sql = "
            SELECT u.id, u.name, u.username, u.role_id, u.warehouse_id,
                   r.role_name, w.name as store_name, w.code as store_code
            FROM users_pos u
            LEFT JOIN roles_pos r ON u.role_id = r.id
            LEFT JOIN warehouses w ON u.warehouse_id = w.id
            ORDER BY u.id DESC
        ";
        $stmt = $pdo->query($sql);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'save') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role_id = $_POST['role_id'] ?? 2; // Default Kasir
        $warehouse_id = $_POST['warehouse_id'] ?? null;

        if (empty($name) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Lengkap dan Username wajib diisi!']); exit;
        }

        if ($warehouse_id === "") {
            $warehouse_id = null;
        }

        if (empty($id)) {
            // Tambah baru
            if (empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi untuk akun kasir baru!']); exit;
            }
            $cek = $pdo->prepare("SELECT id FROM users_pos WHERE username = ?");
            $cek->execute([$username]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username kasir sudah digunakan!']); exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users_pos (name, username, password, role_id, warehouse_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $username, $hashed, $role_id, $warehouse_id]);
            echo json_encode(['status' => 'success', 'message' => 'Akun kasir outlet berhasil dibuat!']);
        } else {
            // Edit akun
            $cek = $pdo->prepare("SELECT id FROM users_pos WHERE username = ? AND id != ?");
            $cek->execute([$username, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username kasir sudah digunakan akun lain!']); exit;
            }

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users_pos SET name = ?, username = ?, password = ?, role_id = ?, warehouse_id = ? WHERE id = ?");
                $stmt->execute([$name, $username, $hashed, $role_id, $warehouse_id, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users_pos SET name = ?, username = ?, role_id = ?, warehouse_id = ? WHERE id = ?");
                $stmt->execute([$name, $username, $role_id, $warehouse_id, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Data kasir outlet berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users_pos WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Akun kasir berhasil dihapus!']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
