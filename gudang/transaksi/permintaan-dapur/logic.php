<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
header('Content-Type: application/json');
checkPermission('trx_permintaan_dapur');

$action = $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN DATA
    if ($action === 'read') {
        $status = $_GET['status'] ?? 'semua';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; // Permintaanmu: 10 Baris 1 Pagination
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
            SELECT mr.*, ms.material_name, ms.unit, ms.stock as stok_gudang, k.name as nama_dapur, u.name as nama_staff
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

    // 2. PROSES TERIMA (APPROVE)
    if ($action === 'approve') {
        $id = $_POST['id'] ?? '';
        $qty_approved = (float)($_POST['qty_approved'] ?? 0);
        $material_id = $_POST['material_id'] ?? '';

        if (empty($id) || $qty_approved <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Jumlah disetujui tidak valid!']); exit;
        }

        // Mulai Transaksi Database agar aman (Stok gudang & Status berubah bersamaan)
        $pdo->beginTransaction();

        try {
            // Cek stok gudang saat ini
            $cekStok = $pdo->prepare("SELECT stock FROM materials_stocks WHERE id = ?");
            $cekStok->execute([$material_id]);
            $stokTersedia = $cekStok->fetchColumn();

            if ($stokTersedia < $qty_approved) {
                throw new Exception("Stok Gudang Pilar tidak cukup! Sisa stok hanya: $stokTersedia");
            }

            // Update Status Request jadi Diproses
            $stmt = $pdo->prepare("UPDATE material_requests SET status = 'diproses', qty_approved = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$qty_approved, $id]);

            // Potong Stok di Gudang Pilar
            $stmtStok = $pdo->prepare("UPDATE materials_stocks SET stock = stock - ? WHERE id = ?");
            $stmtStok->execute([$qty_approved, $material_id]);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Permintaan berhasil diproses & Stok Gudang telah dipotong.']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 3. PROSES TOLAK (REJECT)
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