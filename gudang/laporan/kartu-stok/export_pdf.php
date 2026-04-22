<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('lap_kartu_stok');

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

$periode_text = (!empty($start_date) && !empty($end_date) && $filter_date === 'periode') ? date('d/m/Y', strtotime($start_date)) . ' s.d ' . date('d/m/Y', strtotime($end_date)) : 'Semua Waktu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Kartu Stok Gudang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; background:#dc2626; color:white; border:none; border-radius:5px; cursor:pointer;">Cetak PDF</button>
    <div class="header">
        <h2>LAPORAN KARTU STOK (PERGERAKAN BARANG)</h2>
        <p>Periode: <?= $periode_text ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:12%">Tanggal</th>
                <th style="width:20%">Barang</th>
                <th style="width:10%">Tipe Transaksi</th>
                <th style="width:20%">Keterangan / Ref</th>
                <th class="text-center" style="width:8%">IN</th>
                <th class="text-center" style="width:8%">OUT</th>
                <th class="text-center" style="width:10%">SALDO</th>
                <th style="width:12%">Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $row): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($row['material_name']) ?></strong></td>
                    <td class="text-center font-bold"><?= $row['tipe'] ?></td>
                    <td><?= htmlspecialchars($row['notes'] ?: '-') ?> <br><small>[<?= $row['ref'] ?>]</small></td>
                    <td class="text-center" style="color:green;"><?= $row['masuk'] > 0 ? floatval($row['masuk']) : '-' ?></td>
                    <td class="text-center" style="color:red;"><?= $row['keluar'] > 0 ? floatval($row['keluar']) : '-' ?></td>
                    <td class="text-center font-bold" style="background:#f8fafc;"><?= floatval($row['saldo']) ?> <?= $row['unit'] ?></td>
                    <td><?= $row['admin_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">Tidak ada pergerakan barang.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>