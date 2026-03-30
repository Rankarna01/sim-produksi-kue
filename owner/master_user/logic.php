<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $pdo->query("SELECT id, name, username, role FROM users ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name']);
            $username = strtolower(trim($_POST['username'])); // Username wajib huruf kecil
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'];

            if (empty($name) || empty($username)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama dan Username wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                // TAMBAH USER BARU
                if (empty($password)) {
                    echo json_encode(['status' => 'error', 'message' => 'Password wajib diisi untuk akun baru!']);
                    exit;
                }
                
                $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $cek->execute([$username]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Username sudah terpakai!']);
                    exit;
                }

                // Proses Hashing Password dengan Bcrypt
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $username, $hashed_password, $role]);
                echo json_encode(['status' => 'success', 'message' => 'Akun berhasil ditambahkan!']);
            } else {
                // EDIT USER
                $cek = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $cek->execute([$username, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Username sudah dipakai oleh orang lain!']);
                    exit;
                }

                if (!empty($password)) {
                    // Jika password diisi, update password beserta data lainnya
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, password=?, role=? WHERE id=?");
                    $stmt->execute([$name, $username, $hashed_password, $role, $id]);
                } else {
                    // Jika password kosong, jangan ubah passwordnya
                    $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, role=? WHERE id=?");
                    $stmt->execute([$name, $username, $role, $id]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Akun berhasil diperbarui!']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            
            // Proteksi: Owner tidak boleh menghapus akunnya sendiri
            if ($id == $_SESSION['user_id']) {
                echo json_encode(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun yang sedang Anda gunakan!']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Akun berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>