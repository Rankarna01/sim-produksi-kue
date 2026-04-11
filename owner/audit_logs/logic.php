<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkPermission('audit_logs');

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $period = $_GET['period'] ?? 'all';
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';

        $dateFilter = "";
        if ($period === 'today') $dateFilter = "DATE(waktu) = CURDATE()";
        elseif ($period === 'week') $dateFilter = "YEARWEEK(waktu, 1) = YEARWEEK(CURDATE(), 1)";
        elseif ($period === 'month') $dateFilter = "MONTH(waktu) = MONTH(CURDATE()) AND YEAR(waktu) = YEAR(CURDATE())";
        elseif ($period === 'year') $dateFilter = "YEAR(waktu) = YEAR(CURDATE())";
        elseif ($period === 'custom' && !empty($start) && !empty($end)) $dateFilter = "DATE(waktu) BETWEEN '$start' AND '$end'";

        $where = !empty($dateFilter) ? "WHERE $dateFilter" : "";

        // QUERY UNION: Sekarang semua tabel sudah punya created_at
        $queryBase = "
            SELECT * FROM (
                -- TRANSAKSI PRODUKSI
                SELECT created_at as waktu, user_id, 'PRODUKSI' as menu, 
                       CONCAT('Produksi: ', invoice_no, ' [', UPPER(status), ']') as tindakan
                FROM productions

                UNION ALL

                -- STOK OPNAME
                SELECT created_at as waktu, user_id, 'STOK OPNAME' as menu, 
                       CONCAT('Penyesuaian No: ', IFNULL(opname_no, 'Manual')) as tindakan
                FROM material_opnames

                UNION ALL

                -- MASTER BAHAN
                SELECT created_at as waktu, 1 as user_id, 'MASTER BAHAN' as menu, 
                       CONCAT('Bahan Baru/Update: ', name, ' (', code, ')') as tindakan
                FROM materials

                UNION ALL

                -- MASTER PRODUK
                SELECT created_at as waktu, 1 as user_id, 'MASTER PRODUK' as menu, 
                       CONCAT('Produk Baru/Update: ', name) as tindakan
                FROM products
            ) AS combined_logs
            $where
        ";

        // Hitung total untuk pagination
        $total_stmt = $pdo->query("SELECT COUNT(*) FROM ($queryBase) AS total");
        $total_records = $total_stmt->fetchColumn();
        $total_pages = ceil($total_records / $limit);

        // Ambil data dengan join user agar nama pegawai muncul
        $sql = "
            SELECT log.*, u.name as pegawai, u.role 
            FROM ($queryBase) AS log
            LEFT JOIN users u ON log.user_id = u.id
            ORDER BY waktu DESC 
            LIMIT $limit OFFSET $offset
        ";
        
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success', 
            'data' => $data,
            'total_pages' => $total_pages,
            'current_page' => $page
        ]);
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}