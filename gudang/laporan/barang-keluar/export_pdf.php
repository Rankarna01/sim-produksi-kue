<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

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

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
if($filter_date === 'bulanan') $periode_teks = date('F Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Barang Keluar</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-red { color: #e11d48; font-weight: bold; text-align: center;}
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background: #e11d48; color: white; border: none; border-radius: 5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN BARANG KELUAR GUDANG</h2>
        <p>Periode: <?= $periode_teks ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:12%">Waktu</th>
                <th style="width:25%">Barang</th>
                <th style="width:10%; text-align:center;">Jumlah</th>
                <th style="width:15%">Tujuan/Ref</th>
                <th style="width:10%">Karyawan</th>
                <th style="width:18%">Keterangan</th>
                <th style="width:10%">User</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $row): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><?= $row['material_name'] ?></td>
                    <td class="text-red">-<?= floatval($row['qty']) ?> <?= $row['unit'] ?></td>
                    <td><?= $row['transaction_no'] ?></td>
                    <td>-</td>
                    <td><?= $row['notes'] ?: '-' ?></td>
                    <td><?= $row['admin_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data laporan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>