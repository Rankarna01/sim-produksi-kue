<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_po');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $status_po = $_GET['status_po'] ?? 'semua';
        $status_pay = $_GET['status_pay'] ?? 'semua';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        // Filter Status PO
        if ($status_po !== 'semua') {
            $whereClause .= " AND p.status = ?";
            $params[] = $status_po;
        }

        // Filter Status Pembayaran
        if ($status_pay !== 'semua') {
            $whereClause .= " AND p.payment_status = ?";
            $params[] = $status_pay;
        }

        // Filter Tanggal
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(p.created_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(p.created_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $whereClause .= " AND (p.po_no LIKE ? OR s.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Count Total
        $countSql = "SELECT COUNT(p.id) FROM purchase_orders p LEFT JOIN suppliers s ON p.supplier_id = s.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Fetch Data PO
        $sql = "
            SELECT p.*, s.name as supplier_name, u.name as admin_name 
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.id
            JOIN users u ON p.created_by = u.id
            $whereClause 
            ORDER BY p.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch Data Detail Items untuk masing-masing PO
        foreach ($pos as &$po) {
            $stmtItem = $pdo->prepare("SELECT ms.material_name, pod.qty, pod.price FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?");
            $stmtItem->execute([$po['id']]);
            $po['items'] = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'status' => 'success', 
            'data' => $pos,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>