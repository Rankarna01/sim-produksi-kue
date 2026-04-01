<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

$action = $_GET['action'] ?? '';

try {
    // RUTE BARU: Tarik data Gudang untuk Dropdown Filter
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';
    $warehouse_id = $_GET['warehouse_id'] ?? '';
    
    $whereClause = "WHERE 1=1";
    $params = [];

    if (!empty($start_date)) {
        $whereClause .= " AND DATE(p.created_at) >= ?";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $whereClause .= " AND DATE(p.created_at) <= ?";
        $params[] = $end_date;
    }
    if (!empty($status)) {
        $whereClause .= " AND p.status = ?";
        $params[] = $status;
    }
    if (!empty($warehouse_id)) {
        $whereClause .= " AND p.warehouse_id = ?";
        $params[] = $warehouse_id;
    }

    if ($action === 'export_excel') {
        $sqlExcel = "
            SELECT p.created_at, p.invoice_no, COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, d.quantity, p.status, w.name as gudang 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            JOIN warehouses w ON p.warehouse_id = w.id
            $whereClause
            ORDER BY p.created_at DESC
        ";
        $stmtExcel = $pdo->prepare($sqlExcel);
        $stmtExcel->execute($params);
        $dataExcel = $stmtExcel->fetchAll();

        $filename = "Laporan_Produksi_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Tanggal Produksi', 'Invoice', 'Karyawan', 'Nama Produk', 'Jumlah (Pcs)', 'Status', 'Gudang Tujuan']);
        
        $no = 1;
        foreach ($dataExcel as $row) {
            $st = $row['status'];
            $status_indo = ($st === 'pending') ? 'Pending' : (($st === 'masuk_gudang') ? 'Selesai' : (($st === 'ditolak') ? 'Ditolak (Revisi)' : 'Expired'));

            fputcsv($output, [
                $no++,
                $row['created_at'],
                $row['invoice_no'],
                $row['karyawan'],
                $row['produk'],
                $row['quantity'],
                $status_indo,
                $row['gudang']
            ]);
        }
        
        fclose($output);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        // 1. HITUNG PAGINATION
        $countStmt = $pdo->prepare("SELECT COUNT(d.id) FROM productions p JOIN production_details d ON p.id = d.production_id $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // 2. HITUNG REKAPITULASI (SUMMARY CARDS)
        $sumStmt = $pdo->prepare("
            SELECT 
                SUM(d.quantity) as total_all,
                SUM(CASE WHEN p.status = 'masuk_gudang' THEN d.quantity ELSE 0 END) as total_masuk,
                SUM(CASE WHEN p.status IN ('ditolak', 'expired') THEN d.quantity ELSE 0 END) as total_gagal
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            $whereClause
        ");
        $sumStmt->execute($params);
        $summary = $sumStmt->fetch(PDO::FETCH_ASSOC);

        // 3. AMBIL DATA TABEL (DENGAN NAMA EMPLOYEES BARU)
        $sql = "
            SELECT p.created_at, p.invoice_no, COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, d.quantity, p.status, w.name as gudang 
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN users u ON p.user_id = u.id
            LEFT JOIN employees e ON p.employee_id = e.id
            JOIN warehouses w ON p.warehouse_id = w.id
            $whereClause
            ORDER BY p.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'summary' => [
                'total' => $summary['total_all'] ?? 0,
                'masuk' => $summary['total_masuk'] ?? 0,
                'gagal' => $summary['total_gagal'] ?? 0
            ],
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_data' => $total_data
        ]);
        exit;
    }

} catch (PDOException $e) {
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>