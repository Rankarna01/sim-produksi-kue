<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // FITUR BARU: Tarik data Master Store & Dapur untuk Dropdown Filter
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses, 'kitchens' => $kitchens]);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;
        
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? ''; 
        $kitchen_id = $_GET['kitchen_id'] ?? ''; // Filter Dapur
        $is_print = $_GET['is_print'] ?? 'false';

        // Hanya tarik status pending (Belum di validasi/masuk store)
        $whereClause = "WHERE p.status = 'pending'";
        $params = [];

        // Tambahkan filter dinamis
        if (!empty($start_date)) {
            $whereClause .= " AND DATE(p.created_at) >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $whereClause .= " AND DATE(p.created_at) <= ?";
            $params[] = $end_date;
        }
        if (!empty($warehouse_id)) {
            $whereClause .= " AND p.warehouse_id = ?";
            $params[] = $warehouse_id;
        }
        if (!empty($kitchen_id)) {
            $whereClause .= " AND e.kitchen_id = ?";
            $params[] = $kitchen_id;
        }

        // PERBAIKAN: Menambahkan JOIN employees ke Count Statement agar filter Dapur tidak error
        $countSql = "
            SELECT COUNT(d.id) 
            FROM productions p 
            JOIN production_details d ON p.id = d.production_id 
            LEFT JOIN employees e ON p.employee_id = e.id
            $whereClause
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $limitClause = ($is_print === 'true') ? "" : "LIMIT $limit OFFSET $offset";
        
        // PERBAIKAN: Menarik data Asal Dapur (k.name)
        $sql = "
            SELECT p.created_at, p.invoice_no, w.name as gudang, k.name as asal_dapur, 
                   COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, d.quantity 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            LEFT JOIN warehouses w ON p.warehouse_id = w.id
            $whereClause
            ORDER BY p.created_at DESC 
            $limitClause
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>