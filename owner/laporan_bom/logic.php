<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('laporan_bom');

$action = $_GET['action'] ?? '';

try {
    $search = $_GET['search'] ?? '';

    // PERBAIKAN KRUSIAL: Join ke tabel materials_stocks (Gudang Pilar) 
    // karena master resep (BOM) sekarang mengacu ke sana.
    $sql = "
        SELECT p.name AS product_name, ms.material_name AS material_name, b.quantity_needed, b.unit_used 
        FROM bom b
        JOIN products p ON b.product_id = p.id
        JOIN materials_stocks ms ON b.material_id = ms.id
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR ms.material_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= " ORDER BY p.name ASC, ms.material_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ============================================
    // ROUTE 1: EXCEL EXPORT (FLAT FORMAT)
    // ============================================
    if ($action === 'export_excel') {
        $filename = "Laporan_Resep_BOM_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($output, ['No', 'Nama Produk', 'Bahan Baku Induk', 'Takaran', 'Satuan']);
        
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
    // ROUTE 2: BACA DATA JSON (GROUPED FORMAT)
    // ============================================
    if ($action === 'read') {
        // Logika Pengelompokan Data per Produk
        $grouped_data = [];
        foreach ($data as $row) {
            $prod = $row['product_name'];
            if (!isset($grouped_data[$prod])) {
                $grouped_data[$prod] = [
                    'product_name' => $prod,
                    'materials' => []
                ];
            }
            $grouped_data[$prod]['materials'][] = [
                'material_name' => $row['material_name'],
                'quantity_needed' => $row['quantity_needed'],
                'unit_used' => $row['unit_used']
            ];
        }

        header('Content-Type: application/json');
        // Re-index array agar format JSON-nya rapi (List of Objects)
        echo json_encode(['status' => 'success', 'data' => array_values($grouped_data)]);
        exit;
    }

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>