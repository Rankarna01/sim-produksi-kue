<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // --- TAMBAHAN: AMBIL DATA SATUAN ---
        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'read':
            $stmt = $pdo->query("SELECT * FROM materials ORDER BY id DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $unit = $_POST['unit'];
            
            $raw_stock = $_POST['stock'] ?? 0;
            $raw_min_stock = $_POST['min_stock'] ?? 0;
            $stock = (float) str_replace(',', '.', $raw_stock);
            $min_stock = (float) str_replace(',', '.', $raw_min_stock);

            if (empty($code) || empty($name) || empty($unit)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode, Nama, dan Satuan wajib diisi!']);
                exit;
            }

            if (empty($id)) {
                $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ?");
                $cek->execute([$code]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Bahan sudah digunakan!']);
                    exit;
                }

                $stmt = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$code, $name, $unit, $stock, $min_stock]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil ditambahkan!']);
            } else {
                $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND id != ?");
                $cek->execute([$code, $id]);
                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Bahan sudah digunakan bahan lain!']);
                    exit;
                }

                $stmt = $pdo->prepare("UPDATE materials SET code=?, name=?, unit=?, stock=?, min_stock=? WHERE id=?");
                $stmt->execute([$code, $name, $unit, $stock, $min_stock, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil diperbarui!']);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil dihapus!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>