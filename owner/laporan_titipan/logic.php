<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    // Mode Export Excel khusus
} else {
    header('Content-Type: application/json');
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    if ($action === 'read' || $action === 'export_excel') {
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $warehouse_id = $_GET['warehouse_id'] ?? '';
        $status_filter = $_GET['status'] ?? '';
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20; 
        $offset = ($page - 1) * $limit;

        $where = "WHERE 1=1"; 
        $params = [];

        if (!empty($start_date)) { $where .= " AND DATE(p.created_at) >= ?"; $params[] = $start_date; }
        if (!empty($end_date)) { $where .= " AND DATE(p.created_at) <= ?"; $params[] = $end_date; }
        if (!empty($warehouse_id)) { $where .= " AND p.warehouse_id = ?"; $params[] = $warehouse_id; }
        if (!empty($status_filter)) { $where .= " AND p.status = ?"; $params[] = $status_filter; }

        $sql_select = "
            SELECT p.created_at, p.invoice_no, p.status, w.name as store_name, 
                   b.nama_barang, b.nama_umkm, d.quantity, 
                   b.harga_modal, b.harga_jual,
                   (d.quantity * b.harga_modal) as total_modal,
                   (d.quantity * b.harga_jual) as total_omset,
                   (d.quantity * (b.harga_jual - b.harga_modal)) as profit
            FROM titipan_productions p
            JOIN titipan_production_details d ON p.id = d.titipan_production_id
            JOIN barang_titipan b ON d.titipan_id = b.id
            LEFT JOIN warehouses w ON p.warehouse_id = w.id
            $where
        ";

        // EXPORT EXCEL MODE
        if ($action === 'export_excel') {
            $stmt = $pdo->prepare("$sql_select ORDER BY p.created_at ASC");
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=Laporan_Titipan_" . date('Ymd') . ".xls");
            
            echo "<table border='1'>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Invoice</th><th>Status</th><th>Store Tujuan</th><th>Nama Barang</th><th>UMKM</th><th>Qty</th><th>Harga Modal</th><th>Harga Jual</th><th>Total Omset</th><th>Total Profit</th></tr>";
            $no = 1;
            foreach($data as $row) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "<td>" . $row['invoice_no'] . "</td>";
                echo "<td>" . strtoupper($row['status']) . "</td>";
                echo "<td>" . $row['store_name'] . "</td>";
                echo "<td>" . $row['nama_barang'] . "</td>";
                echo "<td>" . $row['nama_umkm'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "<td>" . $row['harga_modal'] . "</td>";
                echo "<td>" . $row['harga_jual'] . "</td>";
                echo "<td>" . $row['total_omset'] . "</td>";
                echo "<td>" . $row['profit'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            exit;
        }

        // HANYA MENGHITUNG YANG STATUSNYA 'received' UNTUK SUMMARY CARD
        $sql_summary = "
            SELECT 
                SUM(CASE WHEN sub.status = 'received' THEN sub.quantity ELSE 0 END) as sum_qty, 
                SUM(CASE WHEN sub.status = 'received' THEN sub.total_omset ELSE 0 END) as sum_omset, 
                SUM(CASE WHEN sub.status = 'received' THEN sub.profit ELSE 0 END) as sum_profit 
            FROM ($sql_select) as sub
        ";
        $stmtSum = $pdo->prepare($sql_summary);
        $stmtSum->execute($params);
        $summary = $stmtSum->fetch(PDO::FETCH_ASSOC);

        // Menghitung Pagination
        $sql_count = "SELECT COUNT(*) FROM ($sql_select) as sub";
        $stmtCount = $pdo->prepare($sql_count);
        $stmtCount->execute($params);
        $total_rows = $stmtCount->fetchColumn();
        $total_pages = ceil($total_rows / $limit);

        // Ambil Data Tabel
        $sql_data = "$sql_select ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
        $stmtData = $pdo->prepare($sql_data);
        $stmtData->execute($params);
        $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data, 
            'summary' => [
                'qty' => (int)$summary['sum_qty'],
                'omset' => (int)$summary['sum_omset'],
                'profit' => (int)$summary['sum_profit']
            ],
            'pagination' => [
                'page' => $page,
                'total_pages' => $total_pages,
                'total_rows' => $total_rows,
                'limit' => $limit
            ]
        ]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
}
?>