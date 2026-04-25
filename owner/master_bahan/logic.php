<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('master_bahan');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_kitchen_id = $_SESSION['kitchen_id'] ?? null;

try {
    switch ($action) {
        
        case 'read':
            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }

            if ($warehouse_id == 1) {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? OR warehouse_id IS NULL ORDER BY id DESC");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM materials WHERE warehouse_id = ? ORDER BY id DESC");
            }
            $stmt->execute([$warehouse_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'read_pilar':
            // Ambil data bahan dari gudang utama beserta unit dasarnya
            $stmt = $pdo->query("SELECT id, material_name, stock, unit FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'read_requests':
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            $warehouse_id = $_GET['warehouse_id'] ?? 1;
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }

            $status = $_GET['status'] ?? 'semua';
            $where = "warehouse_id = :wid";
            $params = [':wid' => $warehouse_id];
            
            if ($status !== 'semua') {
                if ($status === 'berhasil') {
                    $where .= " AND status IN ('berhasil', 'approved')";
                } else {
                    $where .= " AND status = :status";
                    $params[':status'] = $status;
                }
            }

            // Hitung dari Tabel Header
            $stmtCount = $pdo->prepare("SELECT COUNT(id) FROM material_requests_header WHERE $where");
            $stmtCount->execute($params);
            $total_pages = ceil($stmtCount->fetchColumn() / $limit);

            $sql = "
                SELECT h.*, 
                       (SELECT COUNT(id) FROM material_requests WHERE header_id = h.id) as total_item 
                FROM material_requests_header h 
                WHERE $where 
                ORDER BY h.created_at DESC LIMIT $limit OFFSET $offset
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode([
                'status' => 'success', 
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'current_page' => $page,
                'total_pages' => $total_pages
            ]);
            break;

        case 'read_request_detail':
            $header_id = $_GET['header_id'];
            $stmt = $pdo->prepare("
                SELECT r.qty_requested, m.material_name, m.unit 
                FROM material_requests r
                JOIN materials_stocks m ON r.material_id = m.id
                WHERE r.header_id = ?
            ");
            $stmt->execute([$header_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'submit_request':
            $warehouse_id = $_POST['warehouse_id']; 
            if ($user_kitchen_id !== null) { $warehouse_id = $user_kitchen_id; }
            $user_id = $_SESSION['user_id'] ?? 1;

            $pilar_ids = $_POST['pilar_id'] ?? [];
            $req_qtys = $_POST['qty'] ?? [];
            $req_units = $_POST['req_unit'] ?? [];

            if (count($pilar_ids) === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Pilih minimal 1 bahan!']); exit;
            }

            $pdo->beginTransaction();

            // 1. Buat Header Transaksi
            $req_no = "REQ-" . date('ymd') . "-" . strtoupper(substr(uniqid(), -4));
            $stmtHead = $pdo->prepare("INSERT INTO material_requests_header (request_no, warehouse_id, user_id, status) VALUES (?, ?, ?, 'menunggu')");
            $stmtHead->execute([$req_no, $warehouse_id, $user_id]);
            $header_id = $pdo->lastInsertId();

            // 2. Loop detail bahan
            for ($i = 0; $i < count($pilar_ids); $i++) {
                $p_id = $pilar_ids[$i];
                $qty = (float)$req_qtys[$i];
                $unit = strtolower(trim($req_units[$i]));

                if (empty($p_id) || $qty <= 0) continue;

                $stmtCek = $pdo->prepare("SELECT stock, unit, material_name FROM materials_stocks WHERE id = ?");
                $stmtCek->execute([$p_id]);
                $pilar = $stmtCek->fetch();

                if (!$pilar) continue;

                // Konversi Satuan
                $base_unit = strtolower(trim($pilar['unit']));
                $final_qty = $qty;

                if ($unit !== 'default' && $unit !== $base_unit) {
                    if ($base_unit === 'kg' && $unit === 'gram') $final_qty = $qty / 1000;
                    elseif ($base_unit === 'liter' && $unit === 'ml') $final_qty = $qty / 1000;
                    elseif ($base_unit === 'lusin' && $unit === 'pcs') $final_qty = $qty / 12;
                    else {
                        $pdo->rollBack();
                        echo json_encode(['status' => 'error', 'message' => "Satuan {$unit} tidak didukung untuk dikonversi ke {$base_unit} pada bahan {$pilar['material_name']}"]); exit;
                    }
                }

                // Insert Detail Request (Simpan $final_qty agar seragam dengan gudang pusat)
                $stmtDet = $pdo->prepare("INSERT INTO material_requests (header_id, request_no, warehouse_id, user_id, material_id, qty_requested, status) VALUES (?, ?, ?, ?, ?, ?, 'menunggu')");
                $stmtDet->execute([$header_id, $req_no, $warehouse_id, $user_id, $p_id, $final_qty]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => "Pengajuan Multi-Item berhasil dikirim!"]);
            break;

    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>