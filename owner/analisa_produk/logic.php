<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner', 'auditor']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    // FITUR BARU: Tarik data Master Gudang untuk Dropdown
    if ($action === 'init_filter') {
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'warehouses' => $warehouses]);
        exit;
    }

    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $warehouse_id = $_GET['warehouse_id'] ?? '';

        // Query Dasar untuk Produksi
        $prod_where = "p.status IN ('masuk_gudang', 'expired') AND DATE(p.created_at) >= ? AND DATE(p.created_at) <= ?";
        $prod_params = [$start_date, $end_date];

        // Query Dasar untuk Barang Keluar (Terbuang)
        $out_where = "o.reason IN ('Expired', 'Rusak') AND DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?";
        $out_params = [$start_date, $end_date];

        // Jika Filter Gudang Aktif, tambahkan kondisi ke masing-masing sub-query
        if (!empty($warehouse_id)) {
            $prod_where .= " AND p.warehouse_id = ?";
            $prod_params[] = $warehouse_id;

            // Numpang ke tabel productions (prod) untuk mencari gudang asal produk keluar
            $out_where .= " AND prod.warehouse_id = ?";
            $out_params[] = $warehouse_id;
        }

        // Gabungkan semua parameter array
        $final_params = array_merge($prod_params, $out_params);

        // Menggabungkan 2 Query (Produksi Valid & Penarikan Basi/Rusak)
        $sql = "
            SELECT 
                pr.id,
                pr.name as produk,
                COALESCE(prod_data.qty, 0) as total_produksi,
                COALESCE(out_data.qty, 0) as total_terbuang
            FROM products pr
            LEFT JOIN (
                SELECT d.product_id, SUM(d.quantity) as qty
                FROM production_details d
                JOIN productions p ON d.production_id = p.id
                WHERE $prod_where
                GROUP BY d.product_id
            ) prod_data ON pr.id = prod_data.product_id
            LEFT JOIN (
                SELECT o.product_id, SUM(o.quantity) as qty
                FROM product_outs o
                LEFT JOIN productions prod ON o.origin_invoice = prod.invoice_no
                WHERE $out_where
                GROUP BY o.product_id
            ) out_data ON pr.id = out_data.product_id
            WHERE prod_data.qty > 0 OR out_data.qty > 0
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($final_params);
        $raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Kalkulasi Persentase di PHP agar tidak membebani Server SQL (Mencegah Divide by Zero Error)
        $final_data = [];
        $sum_produksi_all = 0;
        $sum_terbuang_all = 0;

        foreach ($raw_data as $row) {
            $prod = (int)$row['total_produksi'];
            $buang = (int)$row['total_terbuang'];
            
            // Rumus Persentase Loss Rate
            $loss_rate = ($prod > 0) ? round(($buang / $prod) * 100, 1) : ($buang > 0 ? 100 : 0);
            
            $final_data[] = [
                'produk' => $row['produk'],
                'produksi' => $prod,
                'terbuang' => $buang,
                'loss_rate' => $loss_rate
            ];

            $sum_produksi_all += $prod;
            $sum_terbuang_all += $buang;
        }

        // Urutkan array berdasarkan Persentase Kerugian (Paling tinggi di atas)
        usort($final_data, function($a, $b) {
            return $b['loss_rate'] <=> $a['loss_rate'];
        });

        // Hitung Rata-rata Kerugian Global Bulan Ini
        $global_loss = ($sum_produksi_all > 0) ? round(($sum_terbuang_all / $sum_produksi_all) * 100, 1) : 0;

        echo json_encode([
            'status' => 'success',
            'data' => $final_data,
            'summary' => [
                'total_produksi' => $sum_produksi_all,
                'total_terbuang' => $sum_terbuang_all,
                'loss_rate' => $global_loss
            ]
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>