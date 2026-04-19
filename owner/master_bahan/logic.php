<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_bahan');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

// AMBIL SESSION KITCHEN ID
$user_kitchen_id = $_SESSION['kitchen_id'] ?? null;

try {
    switch ($action) {
        case 'get_units':
            $stmt = $pdo->query("SELECT name FROM units ORDER BY name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'read':
            // PROTEKSI GANDA: Jika bukan owner, timpa warehouse_id dengan kitchen_id milik user
            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) {
                $warehouse_id = $user_kitchen_id; 
            }

            if ($warehouse_id == 1) {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? OR warehouse_id IS NULL ORDER BY id DESC");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? ORDER BY id DESC");
            }
            
            $stmt->execute([$warehouse_id]);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
            break;

        case 'save':
            checkPermission('edit_master_bahan');

            $id = $_POST['id'] ?? '';
            $code = strtoupper(trim($_POST['code']));
            $name = trim($_POST['name']);
            $unit = $_POST['unit'];
            
            $raw_stock = $_POST['stock'] ?? 0;
            $raw_min_stock = $_POST['min_stock'] ?? 0;
            $stock = (float) str_replace(',', '.', $raw_stock);
            $min_stock = (float) str_replace(',', '.', $raw_min_stock);

            if (empty($code) || empty($name) || empty($unit)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode, Nama, dan Satuan wajib diisi!']); exit;
            }

            if (empty($id)) {
                echo json_encode(['status' => 'error', 'message' => 'Silakan gunakan fitur Ajukan Permintaan!']); exit;
            } else {
                $stmtWh = $pdo->prepare("SELECT warehouse_id FROM materials WHERE id = ?");
                $stmtWh->execute([$id]);
                $current_wh = $stmtWh->fetchColumn();

                if ($current_wh !== null) {
                    $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND id != ? AND warehouse_id = ?");
                    $cek->execute([$code, $id, $current_wh]);
                } else {
                    $cek = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND id != ? AND warehouse_id IS NULL");
                    $cek->execute([$code, $id]);
                }

                if ($cek->rowCount() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Kode Bahan ini sudah ada di dapur yang sama!']); exit;
                }

                $stmt = $pdo->prepare("UPDATE materials SET code=?, name=?, unit=?, stock=?, min_stock=? WHERE id=?");
                $stmt->execute([$code, $name, $unit, $stock, $min_stock, $id]);
                echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil diperbarui!']);
            }
            break;

        case 'delete':
            checkPermission('hapus_master_bahan');
            $id = $_POST['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Bahan baku berhasil dihapus!']);
            break;

        case 'read_pilar':
            // PERBAIKAN: Ubah total_stock menjadi stock. Filter hanya barang yg statusnya active.
            $stmt = $pdo->query("SELECT id, material_name, stock, unit FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'read_requests':
            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) {
                $warehouse_id = $user_kitchen_id; 
            }

            $status = $_GET['status'] ?? 'semua';
            $where = "mr.warehouse_id = :wid";
            $params = [':wid' => $warehouse_id];
            
            if ($status !== 'semua') {
                $where .= " AND mr.status = :status";
                $params[':status'] = $status;
            }

            $sql = "SELECT mr.*, ms.material_name, ms.unit FROM material_requests mr JOIN materials_stocks ms ON mr.material_id = ms.id WHERE $where ORDER BY mr.created_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'submit_request':
            $warehouse_id = $_POST['warehouse_id']; 
            
            // PROTEKSI BACKEND
            if ($user_kitchen_id !== null) {
                $warehouse_id = $user_kitchen_id; 
            }

            $pilar_id = $_POST['pilar_id'];
            $qty = (float)$_POST['qty'];
            $user_id = $_SESSION['user_id'] ?? 1;
            $req_no = "REQ-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4));

            if ($qty <= 0) throw new Exception("Jumlah harus lebih dari 0!");

            // PERBAIKAN: Cek stok di gudang pilar menggunakan nama kolom `stock` yang baru
            $stmtCek = $pdo->prepare("SELECT stock, unit FROM materials_stocks WHERE id = ? AND status = 'active'");
            $stmtCek->execute([$pilar_id]);
            $pilar = $stmtCek->fetch();

            if (!$pilar) throw new Exception("Bahan pilar tidak ditemukan atau sudah diarsipkan!");
            
            // Validasi apakah jumlah yang diminta melebihi stok di gudang utama
            if ($pilar['stock'] < $qty) {
                echo json_encode(['status' => 'error', 'message' => "Stok Gudang Pilar tidak cukup! Tersisa: " . $pilar['stock'] . " " . $pilar['unit']]); 
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO material_requests (request_no, warehouse_id, user_id, material_id, qty_requested, status) VALUES (?, ?, ?, ?, ?, 'menunggu')");
            $stmt->execute([$req_no, $warehouse_id, $user_id, $pilar_id, $qty]);

            echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil diajukan!']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Action tidak valid!']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>