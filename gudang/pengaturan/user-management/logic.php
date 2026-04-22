<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('manage_users'); 

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // 1. GET ROLES UNTUK DROPDOWN
    if ($action === 'get_roles') {
        $stmt = $pdo->query("SELECT role_slug, role_name FROM gudang_roles ORDER BY role_name ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $roles]);
        exit;
    }

    // 2. TAMPILKAN USER (Hanya User Gudang)
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $search = $_GET['search'] ?? '';
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE u.role IN (SELECT role_slug FROM gudang_roles)";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (u.name LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $countStmt = $pdo->prepare("SELECT COUNT(u.id) FROM users u $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Join ke gudang_roles untuk mendapatkan Nama Jabatan
        $sql = "
            SELECT u.id, u.name, u.username, u.role, u.status, gr.role_name 
            FROM users u
            JOIN gudang_roles gr ON u.role = gr.role_slug
            $whereClause 
            ORDER BY u.name ASC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }

    // 3. SIMPAN / UPDATE USER
    if ($action === 'save') {
        $id = $_POST['user_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($username) || empty($role)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama, Username, dan Jabatan wajib diisi!']); exit;
        }

        // Cek duplikat Username di SELURUH tabel users
        $cekUser = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $cekUser->execute([$username, $id]);
        if ($cekUser->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh akun lain!']); exit;
        }

        if (empty($id)) {
            // INSERT BARU (Password Wajib)
            if (empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi untuk user baru!']); exit;
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $username, $hashed_password, $role, $status]);
            $msg = "User baru berhasil ditambahkan!";
        } else {
            // UPDATE (Password Opsional)
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, password = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $username, $hashed_password, $role, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $username, $role, $status, $id]);
            }
            $msg = "Data user berhasil diperbarui!";
        }

        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;
    }

    // 4. HAPUS USER
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Akun user berhasil dihapus!']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>