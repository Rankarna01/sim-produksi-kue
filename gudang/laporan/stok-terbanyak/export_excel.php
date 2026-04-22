<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Stok_Terbanyak_" . date('Ymd_His') . ".xls");

$status = $_GET['status'] ?? 'active';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($status !== 'semua') { $whereClause .= " AND ms.status = ?"; $params[] = $status; }
if ($filter_date === 'harian') { $whereClause .= " AND DATE(ms.updated_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(ms.updated_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR ms.sku_code LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT ms.sku_code, ms.material_name, ms.stock, ms.unit, c.name as category_name, r.name as rack_name FROM materials_stocks ms LEFT JOIN material_categories c ON ms.category_id = c.id LEFT JOIN racks r ON ms.rack_id = r.id $whereClause ORDER BY ms.stock DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="6"><h3>LAPORAN STOK TERBANYAK (GUDANG)</h3></th></tr>
        <tr><th colspan="6">Status Data: <?= strtoupper($status) ?></th></tr>
        <tr><th colspan="6">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">No</th>
            <th style="background-color: #f4f4f4;">Kode SKU / ID</th>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Stok Aktual</th>
            <th style="background-color: #f4f4f4;">Satuan</th>
            <th style="background-color: #f4f4f4;">Lokasi Kategori / Rak</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $idx => $row): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td style="font-family: monospace;"><?= $row['sku_code'] ?></td>
            <td><?= $row['material_name'] ?></td>
            <td style="color: #2563eb; font-weight: bold;"><?= floatval($row['stock']) ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['category_name'] ?: '-' ?> / <?= $row['rack_name'] ?: '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>