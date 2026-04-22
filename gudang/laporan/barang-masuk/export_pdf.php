<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$source = $_GET['source'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($source !== 'semua') { $whereClause .= " AND bm.source = ?"; $params[] = $source; }
if ($filter_date === 'harian') { $whereClause .= " AND DATE(bm.created_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(bm.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR bm.transaction_no LIKE ? OR s.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT bm.*, ms.material_name, ms.unit, s.name as supplier_name, u.name as admin_name 
        FROM barang_masuk bm JOIN materials_stocks ms ON bm.material_id = ms.id JOIN users u ON bm.user_id = u.id LEFT JOIN suppliers s ON bm.supplier_id = s.id 
        $whereClause ORDER BY bm.created_at DESC";
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
    <meta charset="UTF-8"><title>Laporan Barang Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { w-full; border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-green { color: #16a34a; font-weight: bold; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN BARANG MASUK GUDANG</h2>
        <p>Periode: <?= $periode_teks ?> | Sumber: <?= strtoupper($source) ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Waktu</th><th>Barang</th><th>Jumlah</th><th>Sumber</th><th>Supplier</th><th>Keterangan</th><th>User</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $row): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><?= $row['material_name'] ?></td>
                    <td class="text-green">+<?= floatval($row['qty']) ?> <?= $row['unit'] ?></td>
                    <td><?= $row['source'] === 'PO' ? $row['transaction_no'] : 'Manual' ?></td>
                    <td><?= $row['supplier_name'] ?: '-' ?></td>
                    <td><?= $row['notes'] ?: '-' ?></td>
                    <td><?= $row['admin_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>