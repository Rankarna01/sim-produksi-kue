<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_po');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'read') {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 15; 
    $offset = ($page - 1) * $limit;

    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? 'semua';

    $whereClause = "WHERE 1=1";
    $params = [];

    if (!empty($start_date) && !empty($end_date)) {
        $whereClause .= " AND DATE(r.created_at) BETWEEN ? AND ?";
        $params[] = $start_date; 
        $params[] = $end_date;
    }
    if ($status !== 'semua') {
        $whereClause .= " AND r.status = ?";
        $params[] = $status;
    }

    $countSql = "SELECT COUNT(r.id) FROM po_returns r $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total_data = $countStmt->fetchColumn();
    $total_pages = ceil($total_data / $limit);

    $sql = "
        SELECT r.*, p.po_no, s.name as supplier_name, ms.material_name, ms.unit, u.name as admin_name,
               (r.qty_return * r.price) as total_potongan
        FROM po_returns r
        JOIN purchase_orders p ON r.po_id = p.id
        JOIN suppliers s ON p.supplier_id = s.id
        JOIN materials_stocks ms ON r.material_id = ms.id
        LEFT JOIN users u ON r.created_by = u.id
        $whereClause
        ORDER BY r.created_at DESC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data, 'total_pages' => $total_pages, 'current_page' => $page]);
    exit;
}
?>