<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_pembayaran_po');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. INIT DROPDOWN METODE PEMBAYARAN
    if ($action === 'init') {
        $methods = $pdo->query("SELECT id, name FROM payment_methods ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'methods' => $methods]);
        exit;
    }

    // 2. BACA DATA LAPORAN
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $method_id = $_GET['method'] ?? 'semua';
        $status_po = $_GET['status_po'] ?? 'semua';
        $filter_date = $_GET['filter_date'] ?? 'semua';
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $search = $_GET['search'] ?? '';

        $whereClause = "WHERE 1=1";
        $params = [];

        // Filter Metode
        if ($method_id !== 'semua') {
            $whereClause .= " AND pp.payment_method_id = ?";
            $params[] = $method_id;
        }

        // Filter Status Lunas / Belum Lunas
        if ($status_po === 'paid') {
            $whereClause .= " AND po.payment_status = 'paid'";
        } elseif ($status_po === 'unpaid_partial') {
            $whereClause .= " AND po.payment_status IN ('unpaid', 'partial')";
        }

        // Filter Tanggal (Berdasarkan payment_date)
        if ($filter_date === 'harian') {
            $whereClause .= " AND DATE(pp.payment_date) = CURDATE()";
        } elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
            $whereClause .= " AND DATE(pp.payment_date) BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }

        // Search
        if (!empty($search)) {
            $whereClause .= " AND (po.po_no LIKE ? OR s.name LIKE ? OR pp.notes LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Base Query Joins
        $joins = "FROM purchase_payments pp 
                  JOIN purchase_orders po ON pp.po_id = po.id 
                  JOIN suppliers s ON po.supplier_id = s.id 
                  JOIN payment_methods pm ON pp.payment_method_id = pm.id 
                  JOIN users u ON pp.user_id = u.id";

        // Hitung Total Data untuk Pagination
        $countStmt = $pdo->prepare("SELECT COUNT(pp.id) $joins $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Hitung Grand Total Nominal
        $sumStmt = $pdo->prepare("SELECT SUM(pp.amount) $joins $whereClause");
        $sumStmt->execute($params);
        $grand_total = $sumStmt->fetchColumn() ?: 0;

        // Fetch Data Pagination (Menarik po.payment_status juga)
        $sql = "
            SELECT pp.*, po.po_no, po.payment_status, s.name as supplier_name, pm.name as method_name, u.name as admin_name 
            $joins
            $whereClause 
            ORDER BY pp.payment_date DESC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'grand_total' => $grand_total,
            'current_page' => $page,
            'total_pages' => $total_pages
        ]);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>