<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Barang_Keluar_" . date('Ymd_His') . ".xls");

$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($filter_date === 'harian') { $whereClause .= " AND DATE(bk.created_at) = CURDATE()"; } 
elseif ($filter_date === 'mingguan') { $whereClause .= " AND YEARWEEK(bk.created_at, 1) = YEARWEEK(CURDATE(), 1)"; } 
elseif ($filter_date === 'bulanan') { $whereClause .= " AND YEAR(bk.created_at) = YEAR(CURDATE()) AND MONTH(bk.created_at) = MONTH(CURDATE())"; } 
elseif ($filter_date === 'tahunan') { $whereClause .= " AND YEAR(bk.created_at) = YEAR(CURDATE())"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(bk.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}

if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR bk.transaction_no LIKE ? OR bk.notes LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT bk.*, ms.material_name, ms.unit, u.name as admin_name 
        FROM barang_keluar bk JOIN materials_stocks ms ON bk.material_id = ms.id JOIN users u ON bk.user_id = u.id
        $whereClause ORDER BY bk.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="7"><h3>LAPORAN BARANG KELUAR GUDANG</h3></th>
        </tr>
        <tr>
            <th colspan="7">Waktu Tarik: <?= date('d M Y H:i:s') ?></th>
        </tr>
        <tr>
            <th style="background-color: #f4f4f4;">Waktu</th>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Jumlah Keluar</th>
            <th style="background-color: #f4f4f4;">Satuan</th>
            <th style="background-color: #f4f4f4;">Tujuan/Ref</th>
            <th style="background-color: #f4f4f4;">Catatan / Alasan</th>
            <th style="background-color: #f4f4f4;">Admin Input</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= $row['material_name'] ?></td>
            <td style="color: #e11d48; font-weight: bold;">-<?= floatval($row['qty']) ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= $row['transaction_no'] ?></td>
            <td><?= $row['notes'] ?: '-' ?></td>
            <td><?= $row['admin_name'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>