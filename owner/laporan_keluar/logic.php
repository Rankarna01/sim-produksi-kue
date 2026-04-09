<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('laporan_keluar');

$action = $_GET['action'] ?? '';

try {
    // FITUR BARU: Tarik data Master Gudang untuk Dropdown Filter
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $reason = $_GET['reason'] ?? '';
    $warehouse_id = $_GET['warehouse_id'] ?? '';
    
    $whereClause = "WHERE 1=1";
    $params = [];

    if (!empty($start_date)) {
        $whereClause .= " AND DATE(o.created_at) >= ?";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $whereClause .= " AND DATE(o.created_at) <= ?";
        $params[] = $end_date;
    }
    if (!empty($reason)) {
        $whereClause .= " AND o.reason = ?";
        $params[] = $reason;
    }
    
    // PERBAIKAN: Filter Berdasarkan Gudang menggunakan tabel productions (prod)
    if (!empty($warehouse_id)) {
        $whereClause .= " AND prod.warehouse_id = ?";
        $params[] = $warehouse_id;
    }

    if ($action === 'export_excel') {
        // PERBAIKAN JOIN: Numpang lewat tabel productions untuk mencari warehouse_id
        $sqlExcel = "
            SELECT o.created_at, w.name as gudang, o.invoice_no, o.origin_invoice, 
                   COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, o.quantity, o.reason, o.notes
            FROM product_outs o
            JOIN products pr ON o.product_id = pr.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN employees e ON o.employee_id = e.id
            LEFT JOIN productions prod ON o.origin_invoice = prod.invoice_no
            LEFT JOIN warehouses w ON prod.warehouse_id = w.id
            $whereClause
            ORDER BY o.created_at DESC
        ";
        $stmtExcel = $pdo->prepare($sqlExcel);
        $stmtExcel->execute($params);
        $dataExcel = $stmtExcel->fetchAll();

        $filename = "Laporan_Produk_Keluar_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Waktu Ditarik', 'Lokasi Gudang', 'ID Penarikan', 'Invoice Produksi Asal', 'Petugas', 'Nama Produk', 'Jumlah Dibuang (Pcs)', 'Alasan', 'Catatan']);
        
        $no = 1;
        foreach ($dataExcel as $row) {
            fputcsv($output, [
                $no++,
                $row['created_at'],
                $row['gudang'] ?? 'Gudang Utama',
                $row['invoice_no'],
                $row['origin_invoice'],
                $row['karyawan'],
                $row['produk'],
                $row['quantity'],
                $row['reason'],
                $row['notes']
            ]);
        }
        
        fclose($output);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        // PERBAIKAN: Tambahkan LEFT JOIN prod saat menghitung jumlah baris (count)
        $countSql = "
            SELECT COUNT(o.id) 
            FROM product_outs o
            LEFT JOIN productions prod ON o.origin_invoice = prod.invoice_no
            $whereClause
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // PERBAIKAN JOIN: Numpang lewat tabel productions untuk mencari warehouse_id
        $sql = "
            SELECT o.created_at, w.name as gudang, o.invoice_no, o.origin_invoice, 
                   COALESCE(e.name, u.name) as karyawan, 
                   pr.name as produk, o.quantity, o.reason, o.notes
            FROM product_outs o
            JOIN products pr ON o.product_id = pr.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN employees e ON o.employee_id = e.id
            LEFT JOIN productions prod ON o.origin_invoice = prod.invoice_no
            LEFT JOIN warehouses w ON prod.warehouse_id = w.id
            $whereClause
            ORDER BY o.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        header('Content-Type: application/json');
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
    if ($action === 'read' || $action === 'init_filter') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>