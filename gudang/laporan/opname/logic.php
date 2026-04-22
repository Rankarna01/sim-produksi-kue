<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_stok_opname');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; // Tampilkan 10 Kartu Opname per halaman
        $offset = ($page - 1) * $limit;

        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE so.status = 'approved'"; // Hanya tampilkan yang sudah approved
        $params = [];

        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(so.opname_date) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(so.opname_date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        if (!empty($search)) {
            $whereClause .= " AND (so.opname_no LIKE ? OR u.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Count Total Header Opname (Pakai tabel baru Gudang)
        $countSql = "SELECT COUNT(so.id) FROM gudang_stok_opnames so JOIN users u ON so.created_by = u.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Fetch Header Opname
        $sql = "
            SELECT so.*, u.name as admin_name 
            FROM gudang_stok_opnames so
            JOIN users u ON so.created_by = u.id
            $whereClause 
            ORDER BY so.opname_date DESC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $opnames = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Details untuk setiap Opname
        foreach ($opnames as &$op) {
            $sqlDet = "
                SELECT sod.*, ms.material_name, ms.unit 
                FROM gudang_stok_opname_details sod 
                JOIN materials_stocks ms ON sod.material_id = ms.id 
                WHERE sod.opname_id = ?
            ";
            $stmtDet = $pdo->prepare($sqlDet);
            $stmtDet->execute([$op['id']]);
            $op['details'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'status' => 'success', 
            'data' => $opnames,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>