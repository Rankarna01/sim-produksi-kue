<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_barang_masuk');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $source = $_GET['source'] ?? 'semua';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        // Filter Sumber (Manual / PO)
        if ($source !== 'semua') {
            $whereClause .= " AND bm.source = ?";
            $params[] = $source;
        }

        // Filter Tanggal
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(bm.created_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(bm.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR bm.transaction_no LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Count Total
        $countSql = "SELECT COUNT(bm.id) FROM barang_masuk bm LEFT JOIN materials_stocks ms ON bm.material_id = ms.id LEFT JOIN suppliers s ON bm.supplier_id = s.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Fetch Data
        $sql = "
            SELECT bm.*, ms.material_name, ms.unit, s.name as supplier_name, u.name as admin_name 
            FROM barang_masuk bm
            JOIN materials_stocks ms ON bm.material_id = ms.id
            JOIN users u ON bm.user_id = u.id
            LEFT JOIN suppliers s ON bm.supplier_id = s.id
            $whereClause 
            ORDER BY bm.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>