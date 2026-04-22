<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_supplier');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'init') {
        $suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'suppliers' => $suppliers]);
        exit;
    }

    // 1. TAMPILKAN RANGKUMAN (GROUP BY SUPPLIER)
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $supplier_id = $_GET['supplier_id'] ?? 'semua';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE po.status = 'received'";
        $params = [];

        if ($supplier_id !== 'semua') {
            $whereClause .= " AND po.supplier_id = ?";
            $params[] = $supplier_id;
        }

        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(po.updated_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(po.updated_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        if (!empty($search)) {
            $whereClause .= " AND s.name LIKE ?";
            $params[] = "%$search%";
        }

        // Ambil Data Rangkuman per Supplier
        $sql = "
            SELECT 
                s.id as supplier_id, 
                s.name as supplier_name,
                COUNT(po.id) as total_transaksi,
                SUM(po.total_amount) as total_pembelian,
                MAX(po.updated_at) as transaksi_terakhir
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            $whereClause
            GROUP BY s.id, s.name
            ORDER BY total_pembelian DESC
            LIMIT $limit OFFSET $offset
        ";
        
        // Hitung total halaman (dari subquery)
        $countSql = "SELECT COUNT(DISTINCT po.supplier_id) FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.id $whereClause";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $total_data = $stmtCount->fetchColumn();
        $total_pages = ceil($total_data / $limit);

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

    // 2. TAMPILKAN DETAIL PO PER SUPPLIER UNTUK MODAL
    if ($action === 'get_detail') {
        $supplier_id = $_GET['supplier_id'] ?? '';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        $whereClause = "WHERE po.supplier_id = ? AND po.status = 'received'";
        $params = [$supplier_id];

        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(po.updated_at) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(po.updated_at) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        $sql = "SELECT id as po_id, po_no, updated_at, total_amount FROM purchase_orders po $whereClause ORDER BY updated_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $pos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil detail barang untuk tiap PO
        foreach ($pos as &$po) {
            $sqlItem = "SELECT ms.material_name, pod.qty, pod.price, ms.unit FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?";
            $stmtItem = $pdo->prepare($sqlItem);
            $stmtItem->execute([$po['po_id']]);
            $po['items'] = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['status' => 'success', 'data' => $pos]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>