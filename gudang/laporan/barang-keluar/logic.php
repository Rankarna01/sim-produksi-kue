<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_barang_keluar');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        // FILTER TANGGAL LENGKAP
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(bk.created_at) = CURDATE()";
        } elseif ($filter_date === 'mingguan') {
            $whereClause .= " AND YEARWEEK(bk.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($filter_date === 'bulanan') {
            $whereClause .= " AND YEAR(bk.created_at) = YEAR(CURDATE()) AND MONTH(bk.created_at) = MONTH(CURDATE())";
        } elseif ($filter_date === 'tahunan') {
            $whereClause .= " AND YEAR(bk.created_at) = YEAR(CURDATE())";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(bk.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // PENCARIAN
        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR bk.transaction_no LIKE ? OR bk.notes LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Hitung Total Data
        $countSql = "SELECT COUNT(bk.id) FROM barang_keluar bk LEFT JOIN materials_stocks ms ON bk.material_id = ms.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Ambil Data
        $sql = "
            SELECT bk.*, ms.material_name, ms.unit, u.name as admin_name 
            FROM barang_keluar bk
            JOIN materials_stocks ms ON bk.material_id = ms.id
            JOIN users u ON bk.user_id = u.id
            $whereClause 
            ORDER BY bk.created_at DESC 
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