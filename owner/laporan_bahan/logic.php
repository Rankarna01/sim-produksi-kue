<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('laporan_bahan');

$action = $_GET['action'] ?? '';

try {
    // FITUR BARU: Tarik data Master Dapur untuk Dropdown
    if ($action === 'init_filter') {
        $kitchens = $pdo->query("SELECT id, name FROM kitchens ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'kitchens' => $kitchens]);
        exit;
    }

    // Ambil Parameter Filter
    $search = $_GET['search'] ?? '';
    $status_stok = $_GET['status_stok'] ?? '';
    $kitchen_id = $_GET['kitchen_id'] ?? '';
    $is_print = $_GET['is_print'] ?? 'false'; 

    $whereClause = "WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $whereClause .= " AND m.name LIKE ?";
        $params[] = "%$search%";
    }
    if ($status_stok === 'habis') {
        $whereClause .= " AND m.stock <= 0";
    } elseif ($status_stok === 'menipis') {
        $whereClause .= " AND m.stock > 0 AND m.stock <= 10";
    } elseif ($status_stok === 'aman') {
        $whereClause .= " AND m.stock > 10";
    }
    // Filter Dapur
    if (!empty($kitchen_id)) {
        $whereClause .= " AND m.warehouse_id = ?"; // Note: m.warehouse_id ini di table materials aslinya menyimpan ID Dapur
        $params[] = $kitchen_id;
    }

    // ============================================
    // ROUTE 1: EXCEL EXPORT (DOWNLOAD FILE)
    // ============================================
    if ($action === 'export_excel') {
        $sql = "
            SELECT m.id, m.name, m.stock, m.unit, k.name as kitchen_name 
            FROM materials m
            LEFT JOIN kitchens k ON m.warehouse_id = k.id
            $whereClause
            ORDER BY m.name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "Laporan_Stok_Bahan_Baku_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Nama Bahan Baku', 'Lokasi Dapur', 'Sisa Stok', 'Satuan', 'Kondisi']);
        
        $no = 1;
        foreach ($data as $row) {
            $kondisi = 'Aman';
            if ($row['stock'] <= 0) $kondisi = 'Habis';
            elseif ($row['stock'] <= 10) $kondisi = 'Menipis';

            fputcsv($output, [
                $no++,
                $row['name'],
                $row['kitchen_name'] ?? 'Belum Diatur',
                $row['stock'],
                $row['unit'],
                $kondisi
            ]);
        }
        
        fclose($output);
        exit;
    }

    // ============================================
    // ROUTE 2: BACA DATA JSON (PAGINATION)
    // ============================================
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10; 
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(m.id) FROM materials m LEFT JOIN kitchens k ON m.warehouse_id = k.id $whereClause";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total_data = $countStmt->fetchColumn();
        $total_pages = ceil($total_data / $limit);

        // Jika perintahnya untuk print PDF, hilangkan limitnya
        $limitClause = ($is_print === 'true') ? "" : "LIMIT $limit OFFSET $offset";
        
        $sql = "
            SELECT m.id, m.name, m.stock, m.unit, k.name as kitchen_name 
            FROM materials m
            LEFT JOIN kitchens k ON m.warehouse_id = k.id
            $whereClause
            ORDER BY m.name ASC
            $limitClause
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
    if ($action === 'read' || $action === 'init_filter') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>