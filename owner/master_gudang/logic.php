<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $pdo->query("SELECT * FROM warehouses ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);

            if (empty($code) || empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode dan Nama Gudang wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                // TAMBAH
                $cek = $pdo->prepare("SELECT id FROM warehouses WHERE code = ?");
                $cek->execute([$code]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Gudang sudah digunakan!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO warehouses (code, name) VALUES (?, ?)");
                $stmt->execute([$code, $name]);
                echo json_encode(['status' => 'success', 'message' => 'Gudang berhasil ditambahkan!']);
            } else {
                // EDIT
                $cek = $pdo->prepare("SELECT id FROM warehouses WHERE code = ? AND id != ?");
                $cek->execute([$code, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Gudang sudah digunakan gudang lain!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE warehouses SET code=?, name=? WHERE id=?");
                $stmt->execute([$code, $name, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Gudang berhasil diperbarui!']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM warehouses WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Gudang berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>