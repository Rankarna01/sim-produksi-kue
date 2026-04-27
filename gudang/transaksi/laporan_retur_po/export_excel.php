<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_po');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Retur_PO_" . date('Ymd_His') . ".xls");

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? 'semua';

$whereClause = "WHERE 1=1";
$params = [];

if (!empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(r.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if ($status !== 'semua') {
    $whereClause .= " AND r.status = ?";
    $params[] = $status;
}

$sql = "
    SELECT r.*, p.po_no, s.name as supplier_name, ms.material_name, ms.unit,
           (r.qty_return * r.price) as total_potongan
    FROM po_returns r
    JOIN purchase_orders p ON r.po_id = p.id
    JOIN suppliers s ON p.supplier_id = s.id
    JOIN materials_stocks ms ON r.material_id = ms.id
    $whereClause ORDER BY r.created_at ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <tr><th colspan="8"><h3>LAPORAN HISTORI RETUR PO & PEMOTONGAN TAGIHAN</h3></th></tr>
    <tr><th colspan="8">Waktu Cetak: <?= date('d M Y H:i:s') ?></th></tr>
    <tr>
        <th style="background-color:#f4f4f4">No</th>
        <th style="background-color:#f4f4f4">Tanggal & Waktu</th>
        <th style="background-color:#f4f4f4">Nomor PO</th>
        <th style="background-color:#f4f4f4">Supplier</th>
        <th style="background-color:#f4f4f4">Nama Produk</th>
        <th style="background-color:#f4f4f4">Qty Retur</th>
        <th style="background-color:#f4f4f4">Potongan (Rp)</th>
        <th style="background-color:#f4f4f4">Status</th>
    </tr>
    <?php $total = 0; foreach($data as $idx => $row): $total += $row['total_potongan']; ?>
    <tr>
        <td><?= $idx + 1 ?></td>
        <td><?= date('d/m/Y H:i:s', strtotime($row['created_at'])) ?></td>
        <td><?= $row['po_no'] ?></td>
        <td><?= $row['supplier_name'] ?></td>
        <td><?= $row['material_name'] ?></td>
        <td style="color:red;">-<?= floatval($row['qty_return']) ?> <?= $row['unit'] ?></td>
        <td style="color:red;">-<?= $row['total_potongan'] ?></td>
        <td><?= strtoupper($row['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <th colspan="6" style="text-align:right">TOTAL POTONGAN:</th>
        <th style="color:red; font-weight:bold;">-<?= $total ?></th>
        <th></th>
    </tr>
</table>