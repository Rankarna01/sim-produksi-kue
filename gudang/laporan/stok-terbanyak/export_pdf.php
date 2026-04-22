<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

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

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Stok Terbanyak</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; text-align: center; }
        .text-blue { color: #2563eb; font-weight: bold; text-align: center; font-size: 14px; }
        .text-center { text-align: center; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#2563eb; color:white; border:none; border-radius:5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN STOK TERBANYAK</h2>
        <p>Periode Update: <?= $periode_teks ?> | Tipe Data: <?= strtoupper($status) ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">ID / SKU</th>
                <th style="width: 30%">Nama Barang</th>
                <th style="width: 15%">Stok Aktual</th>
                <th style="width: 10%">Satuan</th>
                <th style="width: 20%">Kategori / Lokasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $idx => $row): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td style="font-family: monospace;"><?= $row['sku_code'] ?></td>
                    <td><strong><?= $row['material_name'] ?></strong></td>
                    <td class="text-blue"><?= floatval($row['stock']) ?></td>
                    <td class="text-center"><?= $row['unit'] ?></td>
                    <td><?= $row['category_name'] ?: '-' ?> <br><small>Rak: <?= $row['rack_name'] ?: '-' ?></small></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">Tidak ada data stok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>