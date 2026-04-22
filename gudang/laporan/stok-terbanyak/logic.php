<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_stok_terbanyak');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $status = $_GET['status'] ?? 'active';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        // Filter Status (Arsip/Non-Arsip)
        if ($status !== 'semua') {
            $whereClause .= " AND ms.status = ?";
            $params[] = $status;
        }

        // Filter Tanggal (Berdasarkan Update Terakhir / Pergerakan)
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(ms.updated_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(ms.updated_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR ms.sku_code LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Hitung Total Data
        $countSql = "SELECT COUNT(ms.id) FROM materials_stocks ms LEFT JOIN material_categories c ON ms.category_id = c.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Ambil Data diurutkan dari Stok Terbanyak (DESC)
        $sql = "
            SELECT ms.sku_code, ms.material_name, ms.stock, ms.unit, 
                   c.name as category_name, r.name as rack_name 
            FROM materials_stocks ms
            LEFT JOIN material_categories c ON ms.category_id = c.id
            LEFT JOIN racks r ON ms.rack_id = r.id
            $whereClause 
            ORDER BY ms.stock DESC 
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