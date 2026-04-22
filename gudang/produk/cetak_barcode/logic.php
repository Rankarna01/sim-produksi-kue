<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('cetak_barcode');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. Tarik Data Dropdown Produk & Rak
    if ($action === 'init_data') {
        
        // Tarik data barang dan gabungkan dengan tabel 'racks'
        $materials = $pdo->query("
            SELECT m.id, m.sku_code, m.material_name, m.unit, c.name as category_name, 
                   r.name as rack_code, r.name as rack_name
            FROM materials_stocks m
            LEFT JOIN material_categories c ON m.category_id = c.id
            LEFT JOIN racks r ON m.lokasi_rak_id = r.id 
            WHERE m.status = 'active'
            ORDER BY m.material_name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Tarik data khusus untuk dropdown Rak
        $racks = $pdo->query("
            SELECT id, name as kode_rak, name as nama_rak 
            FROM racks 
            ORDER BY name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'materials' => $materials, 'racks' => $racks]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>