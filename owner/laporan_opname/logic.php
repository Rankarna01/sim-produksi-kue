<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

$action = $_GET['action'] ?? '';

try {
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    
    $whereClause = "WHERE 1=1";
    $params = [];

    if (!empty($start_date)) {
        $whereClause .= " AND DATE(mo.created_at) >= ?";
        $params[] = $start_date;
    }
    if (!empty($end_date)) {
        $whereClause .= " AND DATE(mo.created_at) <= ?";
        $params[] = $end_date;
    }

    if ($action === 'export_excel') {
        $sqlExcel = "
            SELECT mo.created_at, mo.opname_no, m.code, m.name as material_name, 
                   mo.system_stock, mo.actual_stock, mo.difference, m.unit, mo.reason, u.name as petugas
            FROM material_opnames mo
            JOIN materials m ON mo.material_id = m.id
            JOIN users u ON mo.user_id = u.id
            $whereClause
            ORDER BY mo.created_at DESC
        ";
        $stmtExcel = $pdo->prepare($sqlExcel);
        $stmtExcel->execute($params);
        $dataExcel = $stmtExcel->fetchAll();

        $filename = "Laporan_Opname_" . date('Ymd_His') . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // Support Excel

        fputcsv($output, ['No', 'Tanggal', 'Waktu', 'No. Dokumen', 'Kode Bahan', 'Nama Bahan Baku', 'Stok Sistem', 'Stok Fisik', 'Selisih', 'Satuan', 'Catatan', 'Petugas']);
        
        $no = 1;
        foreach ($dataExcel as $row) {
            $tgl = date('d-m-Y', strtotime($row['created_at']));
            $wkt = date('H:i', strtotime($row['created_at']));

            fputcsv($output, [
                $no++,
                $tgl,
                $wkt,
                $row['opname_no'],
                $row['code'],
                $row['material_name'],
                $row['system_stock'],
                $row['actual_stock'],
                $row['difference'],
                $row['unit'],
                $row['reason'],
                $row['petugas']
            ]);
        }
        fclose($output);
        exit;
    }

    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15; 
        $offset = ($page - 1) * $limit;

        $countStmt = $pdo->prepare("SELECT COUNT(mo.id) FROM material_opnames mo $whereClause");
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        $sql = "
            SELECT mo.id, mo.opname_no, mo.system_stock, mo.actual_stock, mo.difference, mo.reason, mo.created_at,
                   m.name as material_name, m.code, m.unit, u.name as petugas
            FROM material_opnames mo
            JOIN materials m ON mo.material_id = m.id
            JOIN users u ON mo.user_id = u.id
            $whereClause
            ORDER BY mo.created_at DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>