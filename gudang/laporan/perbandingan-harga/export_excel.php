<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Analisis_Harga_Supplier_" . date('Ymd_His') . ".xls");

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
?>
<table border="1">
    <thead>
        <tr><th colspan="8"><h3>ANALISIS HARGA PASAR & SUPPLIER (GUDANG)</h3></th></tr>
        <tr><th colspan="8">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Kategori</th>
            <th style="background-color: #dcfce7;">Harga Termurah (Rp)</th>
            <th style="background-color: #dcfce7;">Supplier Termurah</th>
            <th style="background-color: #f4f4f4;">Harga Termahal (Rp)</th>
            <th style="background-color: #f4f4f4;">Rata-rata Harga (Rp)</th>
            <th style="background-color: #f4f4f4;">Spread (%)</th>
            <th style="background-color: #f4f4f4;">Data Semua Penawaran (Supplier:Harga)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($final_data as $row): 
            $all_offers = [];
            foreach($row['suppliers'] as $sup) {
                $all_offers[] = $sup['supplier_name'] . " (" . floatval($sup['price']) . ")";
            }
        ?>
        <tr>
            <td><?= $row['material_name'] ?></td>
            <td><?= $row['category_name'] ?></td>
            <td style="color: #16a34a; font-weight: bold;"><?= floatval($row['min_price']) ?></td>
            <td><?= $row['best_supplier'] ?></td>
            <td><?= floatval($row['max_price']) ?></td>
            <td><?= floatval($row['avg_price']) ?></td>
            <td style="color: #ea580c; font-weight: bold;"><?= $row['spread'] ?>%</td>
            <td><?= implode(" | ", $all_offers) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>