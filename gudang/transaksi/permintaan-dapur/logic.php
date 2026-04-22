<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
header('Content-Type: application/json');
checkPermission('trx_permintaan_dapur');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // 1. TAMPILKAN DATA (Tidak ada perubahan, tetap sama)
    if ($action === 'read') {
        $status = $_GET['status'] ?? 'semua';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereClause = "";
        $params = [];
        
        if ($status !== 'semua') {
            $whereClause = "WHERE mr.status = :status";
            $params[':status'] = $status;
        }

        $countSql = "SELECT COUNT(*) FROM material_requests mr $whereClause";
        $totalStmt = $pdo->prepare($countSql);
        $totalStmt->execute($params);
        $total_pages = ceil($totalStmt->fetchColumn() / $limit);

        $sql = "
            SELECT mr.*, ms.material_name, ms.sku_code, ms.unit, ms.stock as stok_gudang, k.name as nama_dapur, u.name as nama_staff
            FROM material_requests mr
            JOIN materials_stocks ms ON mr.material_id = ms.id
            JOIN kitchens k ON mr.warehouse_id = k.id
            LEFT JOIN users u ON mr.user_id = u.id
            $whereClause
            ORDER BY mr.created_at DESC
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

    // 2. PROSES TERIMA (APPROVE) -> POTONG GUDANG & TAMBAH DAPUR
    if ($action === 'approve') {
        $id = $_POST['id'] ?? '';
        $qty_approved = (float)($_POST['qty_approved'] ?? 0);
        $material_id = $_POST['material_id'] ?? ''; // Ini ID barang di materials_stocks

        if (empty($id) || $qty_approved <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Jumlah disetujui tidak valid!']); exit;
        }

        $pdo->beginTransaction();

        try {
            // A. Cek detail request dan stok gudang pilar saat ini
            $cekRequest = $pdo->prepare("
                SELECT mr.warehouse_id, ms.material_name, ms.sku_code, ms.unit, ms.stock 
                FROM material_requests mr 
                JOIN materials_stocks ms ON mr.material_id = ms.id 
                WHERE mr.id = ?
            ");
            $cekRequest->execute([$id]);
            $reqData = $cekRequest->fetch(PDO::FETCH_ASSOC);

            if (!$reqData) throw new Exception("Data permintaan tidak valid atau barang terhapus!");
            if ($reqData['stock'] < $qty_approved) {
                throw new Exception("Stok Gudang Pilar tidak cukup! Sisa stok hanya: " . $reqData['stock'] . " " . $reqData['unit']);
            }

            // B. Update Status Request jadi Diproses
            $stmt = $pdo->prepare("UPDATE material_requests SET status = 'diproses', qty_approved = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$qty_approved, $id]);

            // C. Potong Stok di Gudang Pilar (materials_stocks)
            $stmtStok = $pdo->prepare("UPDATE materials_stocks SET stock = stock - ? WHERE id = ?");
            $stmtStok->execute([$qty_approved, $material_id]);

            // D. TRANSFER STOK KE DAPUR (Tabel `materials`)
            // Cek apakah barang (berdasarkan sku_code) sudah ada di dapur yang merequest
            $kitchen_id = $reqData['warehouse_id'];
            $sku = $reqData['sku_code'];
            $nama_bahan = $reqData['material_name'];
            $satuan = $reqData['unit'];

            $cekDapur = $pdo->prepare("SELECT id FROM materials WHERE code = ? AND warehouse_id = ? LIMIT 1");
            $cekDapur->execute([$sku, $kitchen_id]);
            $idBahanDapur = $cekDapur->fetchColumn();

            if ($idBahanDapur) {
                // Jika barang sudah ada di master dapur, tinggal TAMBAH stoknya
                $updDapur = $pdo->prepare("UPDATE materials SET stock = stock + ? WHERE id = ?");
                $updDapur->execute([$qty_approved, $idBahanDapur]);
            } else {
                // Jika barang belum ada di dapur, INSERT jadi barang baru dengan stok awal = qty_approved
                $insDapur = $pdo->prepare("INSERT INTO materials (code, name, unit, stock, min_stock, warehouse_id) VALUES (?, ?, ?, ?, 0, ?)");
                $insDapur->execute([$sku, $nama_bahan, $satuan, $qty_approved, $kitchen_id]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil disetujui! Stok Gudang dipotong, dan Stok Dapur bertambah.']);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 3. PROSES TOLAK (REJECT) (Tidak ada perubahan)
    if ($action === 'reject') {
        $id = $_POST['id'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE material_requests SET status = 'ditolak', processed_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['status' => 'success', 'message' => 'Permintaan telah ditolak.']);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>