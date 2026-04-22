<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE po.status = 'received' AND pod.price > 0";
$params = [];

if ($filter_date === 'harian') { $whereClause .= " AND DATE(po.updated_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(po.updated_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT ms.id as material_id, ms.material_name, c.name as category_name, s.id as supplier_id, s.name as supplier_name, pod.price, po.updated_at as received_date FROM purchase_order_details pod JOIN purchase_orders po ON pod.po_id = po.id JOIN materials_stocks ms ON pod.material_id = ms.id LEFT JOIN material_categories c ON ms.category_id = c.id JOIN suppliers s ON po.supplier_id = s.id $whereClause ORDER BY po.updated_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$comparison = [];
foreach ($results as $row) {
    $mat_id = $row['material_id']; $sup_id = $row['supplier_id'];
    if (!isset($comparison[$mat_id])) { $comparison[$mat_id] = ['material_name' => $row['material_name'], 'category_name' => $row['category_name'] ?: 'Umum', 'suppliers' => []]; }
    if (!isset($comparison[$mat_id]['suppliers'][$sup_id])) {
        $comparison[$mat_id]['suppliers'][$sup_id] = ['supplier_name' => $row['supplier_name'], 'price' => (float)$row['price'], 'date' => $row['received_date']];
    }
}

$final_data = [];
foreach ($comparison as &$item) {
    $prices = array_column($item['suppliers'], 'price');
    $min = min($prices); $max = max($prices); $avg = count($prices)>0 ? array_sum($prices) / count($prices) : 0;
    $spread = $min > 0 ? (($max - $min) / $min) * 100 : 0;
    usort($item['suppliers'], function($a, $b) { return $a['price'] <=> $b['price']; });

    $item['min_price'] = $min; $item['max_price'] = $max; $item['avg_price'] = $avg; $item['spread'] = round($spread, 2);
    $item['best_supplier'] = $item['suppliers'][0]['supplier_name'];
    $item['best_date'] = date('d/m/Y', strtotime($item['suppliers'][0]['date']));
    $final_data[] = $item;
}

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Perbandingan Harga</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .text-green { color: #16a34a; font-weight: bold; font-size: 13px;}
        .text-orange { color: #f97316; font-weight: bold;}
        .box { border: 1px solid #e2e8f0; padding: 4px; margin-bottom: 4px; background: #f8fafc; border-radius: 3px; font-size: 10px;}
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#dc2626; color:white; border:none; border-radius:5px;">Cetak PDF Lengkap</button>
    <div class="header">
        <h2>ANALISIS HARGA PASAR & SUPPLIER</h2>
        <p>Periode: <?= $periode_teks ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%">Barang</th>
                <th style="width: 25%">Rekomendasi (Termurah)</th>
                <th style="width: 20%">Statistik & Spread</th>
                <th style="width: 35%">Daftar Harga Supplier</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($final_data) > 0): ?>
                <?php foreach($final_data as $row): ?>
                <tr>
                    <td><strong><?= $row['material_name'] ?></strong><br><small style="color:#64748b;"><?= $row['category_name'] ?></small></td>
                    <td>
                        <div class="text-green">Rp <?= number_format($row['min_price'],0,',','.') ?></div>
                        <div><?= $row['best_supplier'] ?></div>
                        <small style="color:#94a3b8;">Riwayat: <?= $row['best_date'] ?></small>
                    </td>
                    <td>
                        <table style="border:none; width:100%; margin:0; font-size:10px;">
                            <tr><td style="border:none; padding:2px;">Max:</td><td style="border:none; padding:2px; text-align:right; font-weight:bold;">Rp <?= number_format($row['max_price'],0,',','.') ?></td></tr>
                            <tr><td style="border:none; padding:2px;">Avg:</td><td style="border:none; padding:2px; text-align:right; font-weight:bold;">Rp <?= number_format($row['avg_price'],0,',','.') ?></td></tr>
                            <tr><td style="border:none; padding:2px;">Spread:</td><td style="border:none; padding:2px; text-align:right;" class="text-orange"><?= $row['spread'] ?>%</td></tr>
                        </table>
                    </td>
                    <td>
                        <?php foreach($row['suppliers'] as $sup): ?>
                            <div class="box">
                                <strong><?= $sup['supplier_name'] ?></strong>: Rp <?= number_format($sup['price'],0,',','.') ?> 
                                <span style="color:#94a3b8;">(<?= date('d/m/y', strtotime($sup['date'])) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center;">Tidak ada data historis pembelian.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>