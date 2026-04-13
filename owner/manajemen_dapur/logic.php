<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Gunakan master_gudang agar tidak terjadi 403 Error
checkPermission('manajemen_dapur');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'read':
            $stmt = $pdo->query("SELECT * FROM kitchens ORDER BY id DESC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name']);
            $location = trim($_POST['location']);

            if (empty($name)) {
                echo json_encode(['status' => 'error', 'message' => 'Nama Dapur wajib diisi!']); exit;
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
            $id = $_POST['id'] ?? '';
            
            // Proteksi: Cek apakah dapur ini masih digunakan di tabel materials (Stok Dapur)
            $cek = $pdo->prepare("SELECT id FROM materials WHERE warehouse_id = ?");
            $cek->execute([$id]);
            if ($cek->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus! Masih ada stok bahan baku yang terikat pada dapur ini.']); exit;
            }

            $stmt = $pdo->prepare("DELETE FROM kitchens WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Dapur berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>