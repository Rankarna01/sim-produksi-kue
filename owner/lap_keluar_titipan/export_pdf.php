<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

$periode = $_GET['periode'] ?? 'bulan_ini';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$reason = $_GET['reason'] ?? 'semua';

$whereClause = "WHERE 1=1";
$params = [];

if ($periode === 'bulan_ini') {
    $whereClause .= " AND MONTH(k.created_at) = MONTH(CURDATE()) AND YEAR(k.created_at) = YEAR(CURDATE())";
    $txt_per = "Bulan Ini";
} elseif ($periode === 'hari_ini') {
    $whereClause .= " AND DATE(k.created_at) = CURDATE()";
    $txt_per = "Hari Ini";
} elseif ($periode === 'custom' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(k.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
    $txt_per = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
} else {
    $txt_per = "Semua Waktu";
}

if ($reason !== 'semua') {
    $whereClause .= " AND k.reason = ?";
    $params[] = $reason;
}

$sql = "SELECT k.*, t.nama_barang, t.nama_umkm, u.name as admin_name 
        FROM barang_titipan_keluar k 
        JOIN barang_titipan t ON k.titipan_id = t.id 
        JOIN users u ON k.user_id = u.id 
        $whereClause ORDER BY k.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keluar Titipan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .text-center { text-align: center; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; background:#dc2626; color:white; border:none; border-radius:5px; cursor:pointer;">Cetak PDF</button>
    <div class="header">
        <h2>LAPORAN KELUAR BARANG TITIPAN (UMKM)</h2>
        <p>Periode: <?= $txt_per ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="15%">Waktu</th>
                <th width="15%">No Penarikan</th>
                <th width="20%">Produk & UMKM</th>
                <th class="text-center" width="10%">QTY OUT</th>
                <th width="15%">Alasan</th>
                <th width="20%">Catatan / Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data as $idx => $row): ?>
            <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                <td><?= $row['out_no'] ?></td>
                <td><b><?= $row['nama_barang'] ?></b><br>UMKM: <?= $row['nama_umkm'] ?></td>
                <td class="text-center" style="color:red; font-weight:bold;">-<?= $row['qty'] ?></td>
                <td class="text-center"><?= $row['reason'] ?></td>
                <td><?= $row['notes'] ?: '-' ?><br><small>[<?= $row['admin_name'] ?>]</small></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($data)): ?>
                <tr><td colspan="7" class="text-center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>