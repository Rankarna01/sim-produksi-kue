<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin', 'produksi', 'owner', 'auditor']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// 1. Cari tahu Admin Produksi ini dari dapur mana (jika dia role produksi)
$userKitchenId = null;
if ($user_role === 'produksi') {
    $stmtUser = $pdo->prepare("SELECT kitchen_id FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userKitchenId = $stmtUser->fetchColumn();
}

try {
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses, 'kitchens' => $kitchens]);
        exit;
    }

    $status = $_GET['status'] ?? 'pending';
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $warehouse_id = $_GET['warehouse_id'] ?? '';
    $kitchen_id = $_GET['kitchen_id'] ?? ''; // Filter Baru: Dapur
    $is_print = $_GET['is_print'] ?? 'false';
    
    // Logika Tab Ditolak/Dibatalkan
    if ($status === 'ditolak') {
        $whereClause = "WHERE p.status IN ('ditolak', 'dibatalkan')";
        $params = [];
    } else {
        $whereClause = "WHERE p.status = ?";
        $params = [$status];
    }

    // PROTEKSI MULTI-TENANT: Jika Produksi, kunci datanya!
    if ($userKitchenId) {
        $whereClause .= " AND e.kitchen_id = ?";
        $params[] = $userKitchenId;
    } 
    // Jika bukan produksi, tapi memfilter dapur
    else if (!empty($kitchen_id)) {
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
    if (!empty($warehouse_id)) {
        $whereClause .= " AND p.warehouse_id = ?";
        $params[] = $warehouse_id;
    }

    // ============================================
    // ROUTE 1: EXPORT EXCEL (SEMUA DATA)
    // ============================================
    if ($action === 'export_excel') {
        $sql = "
            SELECT p.created_at, p.invoice_no, COALESCE(e.name, u.name) as karyawan, k.name as asal_dapur, 
                   pr.name as produk, d.quantity, p.status 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
            $whereClause
            ORDER BY p.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "Data_" . strtoupper($status) . "_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Waktu', 'No. Invoice', 'Asal Dapur', 'Karyawan', 'Nama Produk', 'Jumlah (Pcs)', 'Status Aktual']);
        
        $no = 1;
        foreach ($data as $row) {
            $st_aktual = strtoupper($row['status']);
            if($row['status'] === 'masuk_gudang') $st_aktual = 'SELESAI';
            
            fputcsv($output, [
                $no++,
                $row['created_at'],
                $row['invoice_no'],
                $row['asal_dapur'] ?? '-',
                $row['karyawan'],
                $row['produk'],
                $row['quantity'],
                $st_aktual
            ]);
        }
        fclose($output);
        exit;
    }

    // ============================================
    // ROUTE 2: BACA DATA (PAGINATION / PRINT PDF)
    // ============================================
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->prepare("
            SELECT COUNT(d.id) 
            FROM productions p 
            JOIN production_details d ON p.id = d.production_id 
            LEFT JOIN employees e ON p.employee_id = e.id
            $whereClause
        ");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $limitClause = ($is_print === 'true') ? "" : "LIMIT $limit OFFSET $offset";
        
        $sql = "
            SELECT p.created_at, p.invoice_no, COALESCE(e.name, u.name) as karyawan, k.name as asal_dapur, 
                   pr.name as produk, d.quantity, p.status 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            LEFT JOIN kitchens k ON e.kitchen_id = k.id
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

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>