<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';
checkRole(['owner']);

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

try {
    if ($action === 'read') {
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');

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
                WHERE p.status IN ('masuk_gudang', 'expired') 
                AND DATE(p.created_at) >= ? AND DATE(p.created_at) <= ?
                GROUP BY d.product_id
            ) prod_data ON pr.id = prod_data.product_id
            LEFT JOIN (
                SELECT o.product_id, SUM(o.quantity) as qty
                FROM product_outs o
                WHERE o.reason IN ('Expired', 'Rusak')
                AND DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?
                GROUP BY o.product_id
            ) out_data ON pr.id = out_data.product_id
            WHERE prod_data.qty > 0 OR out_data.qty > 0
        ";

        $stmt = $pdo->prepare($sql);
        // Parameter diisi 4 kali karena ada 2 blok BETWEEN tanggal (Untuk Query Produksi & Query Terbuang)
        $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
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