<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('manajemen_dapur');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            header('Content-Type: application/json');
            $stmt = $pdo->query("SELECT * FROM kitchens ORDER BY id DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            header('Content-Type: application/json');
            // NAMA KUNCI DIPERBAIKI
            checkPermission('edit_manajemen_dapur');

            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name']);
            $location = trim($_POST['location']);

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Dapur wajib diisi!']); 
                exit;
            }

            if (empty($id)) {
                $stmt = $pdo->prepare("INSERT INTO kitchens (name, location) VALUES (?, ?)");
                $stmt->execute([$name, $location]);
                echo json_encode(['status' => 'success', 'message' => 'Dapur berhasil ditambahkan!']);
            } else {
                $stmt = $pdo->prepare("UPDATE kitchens SET name = ?, location = ? WHERE id = ?");
                $stmt->execute([$name, $location, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Data dapur berhasil diperbarui!']);
            }
            break;

        case 'delete':
            header('Content-Type: application/json');
            // NAMA KUNCI DIPERBAIKI
            checkPermission('hapus_manajemen_dapur');

            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']);
                exit;
            }

            $cek = $pdo->prepare("SELECT id FROM materials WHERE warehouse_id = ?");
            $cek->execute([$id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Masih ada stok bahan baku yang terikat pada dapur ini.']); 
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM kitchens WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Dapur berhasil dihapus!']);
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>