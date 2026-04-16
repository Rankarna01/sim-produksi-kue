<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin', 'owner', 'auditor']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Cek apakah yang login adalah Admin Store yang terikat dengan 1 Store tertentu
$userWarehouseId = null;
if ($user_role === 'admin') {
    // Karena di tahap sebelumnya kamu belum memberikan kode untuk menambah warehouse_id di master_user,
    // aku asumsikan admin Store (kasir/depan) masih bersifat global atau nanti akan disesuaikan.
    // Untuk saat ini, kita tarik jika dia punya warehouse_id di tabel users (Bisa diimplementasi nanti)
    $stmtUser = $pdo->prepare("SELECT warehouse_id FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $result = $stmtUser->fetchColumn();
    if ($result) $userWarehouseId = $result;
}

try {
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses, 'kitchens' => $kitchens]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? '';
        $kitchen_id = $_GET['kitchen_id'] ?? '';

        $whereClause = "WHERE p.status = 'masuk_gudang'";
        $params = [];

        // Proteksi Tenant Isolation: Admin Store hanya melihat datanya sendiri
        if ($userWarehouseId) {
            $whereClause .= " AND p.warehouse_id = ?";
            $params[] = $userWarehouseId;
        } 
        else if (!empty($warehouse_id)) {
            $whereClause .= " AND p.warehouse_id = ?";
            $params[] = $warehouse_id;
        }

        if (!empty($kitchen_id)) {
            $whereClause .= " AND e.kitchen_id = ?";
            $params[] = $kitchen_id;
        }
        if (!empty($start_date)) {
            $whereClause .= " AND DATE(p.created_at) >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $whereClause .= " AND DATE(p.created_at) <= ?";
            $params[] = $end_date;
        }

        $sql = "
            SELECT p.created_at as updated_at, pr.name as produk, d.quantity, p.invoice_no,
                   w.name as gudang, k.name as asal_dapur
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            LEFT JOIN warehouses w ON p.warehouse_id = w.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            $whereClause
            ORDER BY p.created_at DESC LIMIT 100
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>