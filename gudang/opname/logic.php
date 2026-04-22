<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('data_opname');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. TAMPILKAN LIST DATA OPNAME GUDANG
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($status !== 'semua') {
            $whereClause .= " AND o.status = ?";
            $params[] = $status;
        }
        if (!empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(o.opname_date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        if (!empty($search)) {
            $whereClause .= " AND (o.opname_no LIKE ? OR u.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // PANGGIL TABEL BARU: gudang_stok_opnames
        $countStmt = $pdo->prepare("SELECT COUNT(o.id) FROM gudang_stok_opnames o JOIN users u ON o.created_by = u.id $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Fetch Data dengan subquery total_items
        $sql = "
            SELECT o.*, u.name as pic_name,
                   (SELECT COUNT(id) FROM gudang_stok_opname_details WHERE opname_id = o.id) as total_items
            FROM gudang_stok_opnames o
            JOIN users u ON o.created_by = u.id
            $whereClause 
            ORDER BY o.opname_date DESC 
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

    // 2. TAMPILKAN DETAIL ITEM OPNAME UNTUK MODAL
    if ($action === 'get_detail') {
        $opname_id = $_GET['id'] ?? '';
        
        // PANGGIL TABEL BARU: gudang_stok_opname_details
        $sql = "
            SELECT od.*, ms.material_name, ms.sku_code, ms.unit 
            FROM gudang_stok_opname_details od
            JOIN materials_stocks ms ON od.material_id = ms.id
            WHERE od.opname_id = ?
            ORDER BY ms.material_name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$opname_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>