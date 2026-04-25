<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_kartu_stok');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Kartu_Stok_Gudang_" . date('Ymd_His') . ".xls");

$material_id = $_GET['material_id'] ?? '';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($material_id)) { $whereClause .= " AND t.material_id = ?"; $params[] = $material_id; }
if ($filter_date === 'harian') { $whereClause .= " AND DATE(t.created_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(t.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR t.ref LIKE ? OR t.notes LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$unionQuery = "
            SELECT 'IN' as tipe, created_at, material_id, qty as masuk, 0 as keluar, notes, transaction_no as ref, user_id FROM barang_masuk
            UNION ALL
            SELECT 'OUT' as tipe, created_at, material_id, 0 as masuk, qty as keluar, notes, transaction_no as ref, user_id FROM barang_keluar
            UNION ALL
            SELECT 'IN (Opname)' as tipe, o.opname_date as created_at, od.material_id, od.difference as masuk, 0 as keluar, od.notes, o.opname_no as ref, o.created_by as user_id 
            FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.difference > 0 AND o.status = 'approved'
            UNION ALL
            SELECT 'OUT (Opname)' as tipe, o.opname_date as created_at, od.material_id, 0 as masuk, ABS(od.difference) as keluar, od.notes, o.opname_no as ref, o.created_by as user_id 
            FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.difference < 0 AND o.status = 'approved'
        ";

$sql = "
            SELECT t.*, ms.material_name, ms.unit, u.name as admin_name,
            (
                COALESCE((SELECT SUM(qty) FROM barang_masuk WHERE material_id = t.material_id AND created_at <= t.created_at), 0) -
                COALESCE((SELECT SUM(qty) FROM barang_keluar WHERE material_id = t.material_id AND created_at <= t.created_at), 0) +
                COALESCE((SELECT SUM(difference) FROM gudang_stok_opname_details od JOIN gudang_stok_opnames o ON od.opname_id = o.id WHERE od.material_id = t.material_id AND o.status = 'approved' AND o.opname_date <= t.created_at), 0)
            ) as saldo
            FROM ($unionQuery) t
            JOIN materials_stocks ms ON t.material_id = ms.id
            JOIN users u ON t.user_id = u.id
            $whereClause 
            ORDER BY t.created_at DESC 
        ";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="9"><h3>LAPORAN KARTU STOK GUDANG</h3></th></tr>
        <tr><th colspan="9">Waktu Cetak: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">Tanggal</th>
            <th style="background-color: #f4f4f4;">Barang</th>
            <th style="background-color: #e5e7eb;">Satuan</th>
            <th style="background-color: #f4f4f4;">Tipe</th>
            <th style="background-color: #f4f4f4;">Referensi Dokumen</th>
            <th style="background-color: #dcfce7;">QTY MASUK</th>
            <th style="background-color: #fee2e2;">QTY KELUAR</th>
            <th style="background-color: #f1f5f9;">SALDO AKHIR</th>
            <th style="background-color: #f4f4f4;">Staff</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= htmlspecialchars($row['material_name']) ?></td>
            <td style="font-weight:bold; text-align:center;"><?= htmlspecialchars($row['unit']) ?></td>
            <td><?= $row['tipe'] ?></td>
            <td><?= htmlspecialchars($row['notes'] ?: '-') ?> (<?= $row['ref'] ?>)</td>
            <td style="color: green; text-align:center;"><?= $row['masuk'] > 0 ? floatval($row['masuk']) : '' ?></td>
            <td style="color: red; text-align:center;"><?= $row['keluar'] > 0 ? floatval($row['keluar']) : '' ?></td>
            <td style="font-weight: bold; text-align:center;"><?= floatval($row['saldo']) ?></td>
            <td><?= $row['admin_name'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>