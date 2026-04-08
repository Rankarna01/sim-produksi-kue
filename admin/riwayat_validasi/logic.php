<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // FITUR BARU: Tarik data Master Gudang untuk Dropdown Filter
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? '';

        $sql = "
            SELECT p.created_at as updated_at, pr.name as produk, d.quantity, w.name as gudang, p.invoice_no
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN warehouses w ON p.warehouse_id = w.id
            WHERE p.status = 'masuk_gudang'
        ";
        
        $params = [];

        if (!empty($start_date)) {
            $sql .= " AND DATE(p.created_at) >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $sql .= " AND DATE(p.created_at) <= ?";
            $params[] = $end_date;
        }
        
        // TAMBAHAN BARU: Filter Berdasarkan Gudang
        if (!empty($warehouse_id)) {
            $sql .= " AND p.warehouse_id = ?";
            $params[] = $warehouse_id;
        }

        // Urutkan dari yang paling baru di-scan
        $sql .= " ORDER BY p.created_at DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>