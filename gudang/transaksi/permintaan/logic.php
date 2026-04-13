<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $status = $_GET['status'] ?? 'semua';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClause = "";
        $params = [];
        
        // Pengamanan Query menggunakan parameter
        if ($status !== 'semua') {
            $whereClause = "WHERE mr.status = :status";
            $params[':status'] = $status;
        }

        // Hitung Total Data untuk Pagination
        $countSql = "SELECT COUNT(*) FROM material_requests mr $whereClause";
        $totalStmt = $pdo->prepare($countSql);
        $totalStmt->execute($params);
        $total_pages = ceil($totalStmt->fetchColumn() / $limit);

        // Ambil Data - PERBAIKAN: JOIN ke tabel kitchens (k)
        $sql = "
            SELECT mr.*, ms.material_name, ms.unit, k.name as nama_dapur, u.name as nama_staff
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
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>