<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // ==========================================
    // BAGIAN 1: MANAJEMEN AKUN LOGIN (USERS)
    // ==========================================
    if ($action === 'read_users') {
        $stmt = $pdo->query("SELECT id, name, username, role FROM users ORDER BY id DESC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save_user') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);
        $username = strtolower(trim($_POST['username'])); 
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'];

        if (empty($name) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan Username wajib diisi!']); exit;
        }

        if (empty($id)) {
            // TAMBAH
            if (empty($password)) {
                echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi untuk akun baru!']); exit;
            }
            
            $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $cek->execute([$username]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah terpakai!']); exit;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $username, $hashed, $role]);
            echo json_encode(['status' => 'success', 'message' => 'Akun berhasil ditambahkan!']);
        } else {
            // EDIT
            $cek = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $cek->execute([$username, $id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username sudah dipakai oleh orang lain!']); exit;
            }

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, password=?, role=? WHERE id=?");
                $stmt->execute([$name, $username, $hashed, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, role=? WHERE id=?");
                $stmt->execute([$name, $username, $role, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Akun berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete_user') {
        $id = $_POST['id'] ?? '';
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun yang sedang Anda gunakan!']); exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Akun berhasil dihapus!']);
        exit;
    }


    // ==========================================
    // BAGIAN 2: MANAJEMEN DAFTAR KARYAWAN DAPUR (EMPLOYEES)
    // ==========================================
    if ($action === 'read_employees') {
        $stmt = $pdo->query("SELECT id, name, created_at FROM employees ORDER BY name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'save_employee') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Karyawan wajib diisi!']); exit;
        }

        if (empty($id)) {
            $stmt = $pdo->prepare("INSERT INTO employees (name) VALUES (?)");
            $stmt->execute([$name]);
            echo json_encode(['status' => 'success', 'message' => 'Nama Karyawan berhasil didaftarkan!']);
        } else {
            $stmt = $pdo->prepare("UPDATE employees SET name=? WHERE id=?");
            $stmt->execute([$name, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Nama Karyawan berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete_employee') {
        $id = $_POST['id'] ?? '';
        
        // Pengecekan agar tidak terjadi error relasi database jika karyawan sudah pernah dipakai
        $cek_p = $pdo->prepare("SELECT id FROM productions WHERE employee_id = ? LIMIT 1");
        $cek_p->execute([$id]);
        if($cek_p->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Karyawan ini sudah tercatat dalam riwayat laporan produksi.']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Data Karyawan berhasil dihapus!']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>