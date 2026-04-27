<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Keluar_Titipan_" . date('Ymd_His') . ".xls");

// --- COPY PASTE $whereClause DAN $sql PERSIS SEPERTI DI export_pdf.php ---
$periode = $_GET['periode'] ?? 'bulan_ini';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$reason = $_GET['reason'] ?? 'semua';

$whereClause = "WHERE 1=1";
$params = [];

if ($periode === 'bulan_ini') {
    $whereClause .= " AND MONTH(k.created_at) = MONTH(CURDATE()) AND YEAR(k.created_at) = YEAR(CURDATE())";
} elseif ($periode === 'hari_ini') {
    $whereClause .= " AND DATE(k.created_at) = CURDATE()";
} elseif ($periode === 'custom' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(k.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
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
<table border="1">
    <tr><th colspan="7"><h3>LAPORAN KELUAR BARANG TITIPAN UMKM</h3></th></tr>
    <tr><th colspan="7">Waktu Cetak: <?= date('d M Y H:i:s') ?></th></tr>
    <tr>
        <th style="background-color:#f4f4f4">No</th>
        <th style="background-color:#f4f4f4">Tanggal & Waktu</th>
        <th style="background-color:#f4f4f4">No Transaksi</th>
        <th style="background-color:#f4f4f4">Nama Produk (UMKM)</th>
        <th style="background-color:#f4f4f4">QTY Ditarik/Expired</th>
        <th style="background-color:#f4f4f4">Alasan</th>
        <th style="background-color:#f4f4f4">Admin / Catatan</th>
    </tr>
    <?php foreach($data as $idx => $row): ?>
    <tr>
        <td><?= $idx + 1 ?></td>
        <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
        <td><?= $row['out_no'] ?></td>
        <td><?= $row['nama_barang'] ?> (<?= $row['nama_umkm'] ?>)</td>
        <td style="color:red; font-weight:bold;">-<?= $row['qty'] ?></td>
        <td><?= $row['reason'] ?></td>
        <td><?= $row['admin_name'] ?> - <?= $row['notes'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>