<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Stok_Opname_Gudang_" . date('Ymd_His') . ".xls");

$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE so.status = 'approved'";
$params = [];

if ($filter_date === 'harian') { $whereClause .= " AND DATE(so.opname_date) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(so.opname_date) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}

if (!empty($search)) {
    $whereClause .= " AND (so.opname_no LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

// Gunakan tabel gudang_stok_opnames dan gudang_stok_opname_details
$sql = "
    SELECT so.opname_no, so.opname_date as tgl_opname, u.name as admin_name,
           sod.system_stock, sod.physical_stock, sod.difference, sod.notes,
           ms.material_name, ms.unit
    FROM gudang_stok_opnames so
    JOIN users u ON so.created_by = u.id
    JOIN gudang_stok_opname_details sod ON so.id = sod.opname_id
    JOIN materials_stocks ms ON sod.material_id = ms.id
    $whereClause
    ORDER BY so.opname_date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="9"><h3>LAPORAN STOK OPNAME GUDANG</h3></th></tr>
        <tr><th colspan="9">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">Nomor Opname</th>
            <th style="background-color: #f4f4f4;">Tanggal Opname</th>
            <th style="background-color: #f4f4f4;">Admin / Auditor</th>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Satuan</th>
            <th style="background-color: #f4f4f4;">Qty Sistem</th>
            <th style="background-color: #f4f4f4;">Qty Fisik (Aktual)</th>
            <th style="background-color: #f4f4f4;">Selisih</th>
            <th style="background-color: #f4f4f4;">Catatan</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td style="color: #1d4ed8; font-weight: bold;"><?= $row['opname_no'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['tgl_opname'])) ?></td>
            <td><?= $row['admin_name'] ?></td>
            <td><?= $row['material_name'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= floatval($row['system_stock']) ?></td>
            <td><?= floatval($row['physical_stock']) ?></td>
            <?php 
                $diff = floatval($row['difference']);
                $color = $diff > 0 ? '#16a34a' : ($diff < 0 ? '#e11d48' : '#000');
            ?>
            <td style="color: <?= $color ?>; font-weight: bold; background-color:#f8fafc;"><?= $diff > 0 ? "+".$diff : $diff ?></td>
            <td><?= $row['notes'] ?: '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>