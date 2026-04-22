<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('monitoring_rak');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // 1. DAFTAR SEMUA RAK & TOTAL BARANGNYA
    if ($action === 'read_racks') {
        $search = $_GET['search'] ?? '';
        $where = "WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND r.name LIKE ?";
            $params[] = "%$search%";
        }

        // PERBAIKAN: Kembalikan ke rack_id sesuai database aslimu!
        $sql = "
            SELECT r.id, r.name, 
                   COUNT(m.id) as total_items, 
                   IFNULL(SUM(m.stock), 0) as total_stock
            FROM racks r
            LEFT JOIN materials_stocks m ON r.id = m.rack_id AND m.status = 'active'
            $where
            GROUP BY r.id
            ORDER BY r.name ASC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 2. DETAIL BARANG DI DALAM RAK TERTENTU
    if ($action === 'read_detail') {
        $rack_id = $_GET['rack_id'] ?? '';
        
        // PERBAIKAN: Kembalikan ke rack_id
        $sql = "
            SELECT m.sku_code, m.material_name, m.stock, m.unit, m.expiry_date, c.name as category_name
            FROM materials_stocks m
            LEFT JOIN material_categories c ON m.category_id = c.id
            WHERE m.rack_id = ? AND m.status = 'active'
            ORDER BY m.material_name ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rack_id]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // 3. FITUR SCANNER: Cari ID Rak berdasarkan input Teks persis
    if ($action === 'scan_rack') {
        $code = trim($_GET['code'] ?? '');
        // Bersihkan prefix RAK- jika scanner membaca barcode yg digenerate pakai RAK-
        $cleanCode = str_replace('RAK-', '', strtoupper($code));
        
        $stmt = $pdo->prepare("SELECT id, name FROM racks WHERE UPPER(name) = ? LIMIT 1");
        $stmt->execute([$cleanCode]);
        $rack = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rack) {
            echo json_encode(['status' => 'success', 'data' => $rack]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Rak tidak ditemukan!']);
        }
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error Database: ' . $e->getMessage()]);
}
?>