<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
header('Content-Type: application/json');
checkPermission('trx_permintaan_dapur');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. TAMPILKAN DATA HEADER (INVOICE PERMINTAAN)
    if ($action === 'read') {
        $status = $_GET['status'] ?? 'semua';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereClause = "";
        $params = [];
        
        if ($status !== 'semua') {
            $whereClause = "WHERE h.status = :status";
            $params[':status'] = $status;
        }

        $countSql = "SELECT COUNT(*) FROM material_requests_header h $whereClause";
        $totalStmt = $pdo->prepare($countSql);
        $totalStmt->execute($params);
        $total_pages = ceil($totalStmt->fetchColumn() / $limit);

        // Ambil header beserta hitungan item di dalamnya
        $sql = "
            SELECT h.*, k.name as nama_dapur, u.name as nama_staff,
                   (SELECT COUNT(id) FROM material_requests WHERE header_id = h.id) as total_item
            FROM material_requests_header h
            JOIN kitchens k ON h.warehouse_id = k.id
            LEFT JOIN users u ON h.user_id = u.id
            $whereClause
            ORDER BY h.created_at DESC
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'total_pages' => $total_pages,
            'current_page' => $page
        ]);
        exit;
    }

    // 2. TAMPILKAN DETAIL BARANG DI DALAM MODAL
    if ($action === 'read_detail') {
        $header_id = $_GET['header_id'];
        
        $sql = "
            SELECT mr.*, ms.material_name, ms.sku_code, ms.unit, ms.stock as stok_gudang
            FROM material_requests mr
            JOIN materials_stocks ms ON mr.material_id = ms.id
            WHERE mr.header_id = ?
            ORDER BY ms.material_name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$header_id]);
        
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 3. PROSES TERIMA (APPROVE) PER BARANG
    if ($action === 'approve') {
        $id = $_POST['id'] ?? ''; // ID dari material_requests (Detail)
        $header_id = $_POST['header_id'] ?? '';
        $qty_approved = (float)($_POST['qty_approved'] ?? 0);
        $material_id = $_POST['material_id'] ?? ''; 

        if (empty($id) || empty($header_id) || $qty_approved <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Jumlah disetujui tidak valid!']); exit;
        }

        $pdo->beginTransaction();

        try {
            $cekRequest = $pdo->prepare("SELECT warehouse_id, status FROM material_requests WHERE id = ? FOR UPDATE");
            $cekRequest->execute([$id]);
            $reqData = $cekRequest->fetch(PDO::FETCH_ASSOC);

            if (!$reqData || $reqData['status'] !== 'menunggu') throw new Exception("Barang ini sudah diproses sebelumnya!");

            $cekPilar = $pdo->prepare("SELECT stock, unit, sku_code, material_name FROM materials_stocks WHERE id = ? FOR UPDATE");
            $cekPilar->execute([$material_id]);
            $pilarData = $cekPilar->fetch(PDO::FETCH_ASSOC);

            if ($pilarData['stock'] < $qty_approved) {
                throw new Exception("Stok Gudang Pilar tidak cukup! Sisa: " . $pilarData['stock'] . " " . $pilarData['unit']);
            }

            // A. Update Status Detail
            $pdo->prepare("UPDATE material_requests SET status = 'diproses', qty_approved = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$qty_approved, $id]);

            // B. Potong Gudang Pilar
            $pdo->prepare("UPDATE materials_stocks SET stock = stock - ? WHERE id = ?")->execute([$qty_approved, $material_id]);

            // C. Transfer ke Dapur
            $kitchen_id = $reqData['warehouse_id'];
            $cekDapur = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND warehouse_id = ?");
            $cekDapur->execute([$pilarData['sku_code'], $kitchen_id]);
            $idBahanDapur = $cekDapur->fetchColumn();

            if ($idBahanDapur) {
                $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?")->execute([$qty_approved, $idBahanDapur]);
            } else {
                $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock, warehouse_id) VALUES (?, ?, ?, ?, 0, ?)")->execute([$pilarData['sku_code'], $pilarData['material_name'], $pilarData['unit'], $qty_approved, $kitchen_id]);
            }

            // D. CEK STATUS HEADER (Apakah semua sudah selesai?)
            updateHeaderStatus($pdo, $header_id);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dikirim ke Dapur!']);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 4. PROSES TOLAK (REJECT) PER BARANG
    if ($action === 'reject') {
        $id = $_POST['id'] ?? '';
        $header_id = $_POST['header_id'] ?? '';
        
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE material_requests SET status = 'ditolak', processed_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$id]);
            
            // Cek Status Header
            updateHeaderStatus($pdo, $header_id);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Permintaan barang ditolak.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}

// Fungsi Bantuan Update Header
function updateHeaderStatus($pdo, $header_id) {
    $cekSisa = $pdo->prepare("SELECT COUNT(*) FROM material_requests WHERE header_id = ? AND status = 'menunggu'");
    $cekSisa->execute([$header_id]);
    
    // Jika tidak ada lagi barang yang menunggu di dalam invoice ini
    if ($cekSisa->fetchColumn() == 0) {
        // Cek apakah ada yang berhasil (diproses)
        $cekBerhasil = $pdo->prepare("SELECT COUNT(*) FROM material_requests WHERE header_id = ? AND status = 'diproses'");
        $cekBerhasil->execute([$header_id]);
        
        $headerStatus = ($cekBerhasil->fetchColumn() > 0) ? 'diproses' : 'ditolak';
        $pdo->prepare("UPDATE material_requests_header SET status = ? WHERE id = ?")->execute([$headerStatus, $header_id]);
    }
}
?>