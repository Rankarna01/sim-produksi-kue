<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_kartu_stok'); 

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'init') {
        $materials = $pdo->query("SELECT id, material_name, sku_code FROM materials_stocks WHERE status = 'active' ORDER BY material_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'materials' => $materials]);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $material_id = $_GET['material_id'] ?? '';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($material_id)) {
            $whereClause .= " AND t.material_id = ?";
            $params[] = $material_id;
        }

        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(t.created_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(t.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        if (!empty($search)) {
            $whereClause .= " AND (ms.material_name LIKE ? OR t.ref LIKE ? OR t.notes LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $unionQuery = "
            SELECT 'IN' as tipe, created_at, material_id, qty as masuk, 0 as keluar, notes, transaction_no as ref, user_id FROM barang_masuk
            UNION ALL
            SELECT 'OUT' as tipe, created_at, material_id, 0 as masuk, qty as keluar, notes, transaction_no as ref, user_id FROM barang_keluar
            UNION ALL
            SELECT 'IN (Opname)' as tipe, o.opname_date as created_at, od.material_id, od.difference as masuk, 0 as keluar, od.notes, o.opname_no as ref, o.created_by as user_id 
            FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.difference > 0 AND o.status = 'approved'
            UNION ALL
            SELECT 'OUT (Opname)' as tipe, o.opname_date as created_at, od.material_id, 0 as masuk, ABS(od.difference) as keluar, od.notes, o.opname_no as ref, o.created_by as user_id 
            FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.difference < 0 AND o.status = 'approved'
        ";

        $countSql = "SELECT COUNT(*) FROM ($unionQuery) t JOIN materials_stocks ms ON t.material_id = ms.id $whereClause";
        if (isset($limit)) {
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total_data = $countStmt->fetchColumn();
            $total_pages = ceil($total_data / $limit);
        }

        $sql = "
            SELECT t.*, ms.material_name, ms.unit, u.name as admin_name,
            (
                COALESCE((SELECT SUM(qty) FROM barang_masuk WHERE material_id = t.material_id AND created_at <= t.created_at), 0) -
                COALESCE((SELECT SUM(qty) FROM barang_keluar WHERE material_id = t.material_id AND created_at <= t.created_at), 0) +
                COALESCE((SELECT SUM(difference) FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.material_id = t.material_id AND o.status = 'approved' AND o.opname_date <= t.created_at), 0)
            ) as saldo
            FROM ($unionQuery) t
            JOIN materials_stocks ms ON t.material_id = ms.id
            JOIN users u ON t.user_id = u.id
            $whereClause 
            ORDER BY t.created_at DESC 
        ";
        
        if (isset($limit) && isset($offset)) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
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