<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

$action = $_GET['action'] ?? '';

try {
    // Ambil Parameter Filter
    $search = $_GET['search'] ?? '';

    // Siapkan Query Dinamis (Join 3 Tabel)
    $sql = "
        SELECT p.name AS product_name, m.name AS material_name, b.quantity_needed, b.unit_used 
        FROM bom b
        JOIN products p ON b.product_id = p.id
        JOIN materials m ON b.material_id = m.id
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($search)) {
        // Cari di Nama Produk ATAU Nama Bahan Baku
        $sql .= " AND (p.name LIKE ? OR m.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Urutkan berdasarkan Produk, lalu Bahan Baku
    $sql .= " ORDER BY p.name ASC, m.name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // ============================================
    // ROUTE 1: EXCEL EXPORT (DOWNLOAD FILE)
    // ============================================
    if ($action === 'export_excel') {
        $filename = "Laporan_Resep_BOM_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Nama Produk', 'Bahan Baku', 'Takaran', 'Satuan']);
        
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['product_name'],
                $row['material_name'],
                $row['quantity_needed'],
                $row['unit_used']
            ]);
        }
        
        fclose($output);
        exit;
    }

    // ============================================
    // ROUTE 2: BACA DATA JSON
    // ============================================
    if ($action === 'read') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
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