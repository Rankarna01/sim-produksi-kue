<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['admin']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        // Ambil nilai filter dari URL (jika ada)
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        // Query dasar
 $sql = "
            SELECT p.created_at as updated_at, pr.name as produk, d.quantity, w.name as gudang, p.invoice_no
            FROM productions p
            JOIN production_details d ON p.id = d.production_id
            JOIN products pr ON d.product_id = pr.id
            JOIN warehouses w ON p.warehouse_id = w.id
            WHERE p.status = 'masuk_gudang'
        ";
        
        $params = [];

        // Tambahkan filter tanggal secara dinamis
        if (!empty($start_date)) {
            $sql .= " AND DATE(p.created_at) >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $sql .= " AND DATE(p.created_at) <= ?";
            $params[] = $end_date;
        }

        // Urutkan dari yang paling baru di-scan
        $sql .= " ORDER BY p.created_at DESC LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Action tidak ditemukan']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>