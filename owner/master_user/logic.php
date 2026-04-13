<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_user');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // ==========================================
    // BAGIAN 1: MANAJEMEN AKUN LOGIN (USERS) (100% ASLI)
    // ==========================================
    if ($action === 'get_roles') {
        $stmt = $pdo->query("SELECT role_slug, role_name FROM roles ORDER BY role_name ASC");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'read_users') {
        $sql = "
            SELECT u.id, u.name, u.username, u.role as role_slug, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role = r.role_slug 
            ORDER BY u.id DESC
        ";
        $stmt = $pdo->query($sql);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'save_user') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);
        $username = strtolower(trim($_POST['username'])); 
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'];

        if (empty($name) || empty($username) || empty($role)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama, Username, dan Jabatan wajib diisi!']); exit;
        }

        if (empty($id)) {
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
        if ($_SESSION['role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak! Hanya Owner Utama yang dapat menghapus akun.']); exit;
        }
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
    // BAGIAN 2: MANAJEMEN KARYAWAN DAPUR DENGAN PIN & LOKASI
    // ==========================================
    if ($action === 'read_employees') {
        // PERBAIKAN: JOIN dengan tabel kitchens untuk menampilkan nama dapur
        $sql = "
            SELECT e.id, e.name, e.kitchen_id, e.created_at, k.name as kitchen_name 
            FROM employees e 
            LEFT JOIN kitchens k ON e.kitchen_id = k.id 
            ORDER BY e.name ASC
        ";
        $stmt = $pdo->query($sql);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    if ($action === 'save_employee') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);
        $kitchen_id = $_POST['kitchen_id'] ?? '';
        $pin = $_POST['pin'] ?? '';

        if (empty($name) || empty($kitchen_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama Karyawan dan Lokasi Dapur wajib diisi!']); exit;
        }

        if (empty($id)) {
            // INSERT BARU (Wajib ada PIN 4 angka)
            if(empty($pin) || !preg_match('/^\d{4}$/', $pin)) {
                echo json_encode(['status' => 'error', 'message' => 'PIN otorisasi wajib diisi dengan 4 Angka!']); exit;
            }
            $stmt = $pdo->prepare("INSERT INTO employees (name, kitchen_id, pin) VALUES (?, ?, ?)");
            $stmt->execute([$name, $kitchen_id, $pin]);
            echo json_encode(['status' => 'success', 'message' => 'Karyawan berhasil didaftarkan beserta PIN-nya!']);
        } else {
            // EDIT
            if(!empty($pin)) {
                if(!preg_match('/^\d{4}$/', $pin)) {
                    echo json_encode(['status' => 'error', 'message' => 'PIN harus 4 angka!']); exit;
                }
                $stmt = $pdo->prepare("UPDATE employees SET name=?, kitchen_id=?, pin=? WHERE id=?");
                $stmt->execute([$name, $kitchen_id, $pin, $id]);
            } else {
                // Jika PIN dikosongkan saat edit, update data lain saja
                $stmt = $pdo->prepare("UPDATE employees SET name=?, kitchen_id=? WHERE id=?");
                $stmt->execute([$name, $kitchen_id, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Data Karyawan berhasil diperbarui!']);
        }
        exit;
    }

    if ($action === 'delete_employee') {
        if ($_SESSION['role'] !== 'owner') {
            echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak! Hanya Owner Utama yang dapat menghapus data karyawan.']); exit;
        }
        $id = $_POST['id'] ?? '';
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