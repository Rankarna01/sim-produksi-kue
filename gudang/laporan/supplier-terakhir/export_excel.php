<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Supplier_" . date('Ymd_His') . ".xls");

$supplier_id = $_GET['supplier_id'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE po.status = 'received'";
$params = [];

if ($supplier_id !== 'semua') { $whereClause .= " AND po.supplier_id = ?"; $params[] = $supplier_id; }
if ($filter_date === 'harian') { $whereClause .= " AND DATE(po.updated_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(po.updated_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) { $whereClause .= " AND s.name LIKE ?"; $params[] = "%$search%"; }

$sql = "
    SELECT s.name as supplier_name, po.po_no, po.updated_at, po.total_amount,
           ms.material_name, pod.qty, pod.price, ms.unit
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    JOIN purchase_order_details pod ON po.id = pod.po_id
    JOIN materials_stocks ms ON pod.material_id = ms.id
    $whereClause
    ORDER BY s.name ASC, po.updated_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="8"><h3>LAPORAN DETAIL PEMBELIAN SUPPLIER</h3></th></tr>
        <tr><th colspan="8">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">Nama Supplier</th>
            <th style="background-color: #f4f4f4;">Tgl Transaksi Selesai</th>
            <th style="background-color: #f4f4f4;">Nomor PO</th>
            <th style="background-color: #f4f4f4;">Nama Barang</th>
            <th style="background-color: #f4f4f4;">Qty Beli</th>
            <th style="background-color: #f4f4f4;">Satuan</th>
            <th style="background-color: #f4f4f4;">Harga Satuan (Rp)</th>
            <th style="background-color: #f4f4f4;">Total Faktur PO (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): ?>
        <tr>
            <td><strong><?= htmlspecialchars($row['supplier_name']) ?></strong></td>
            <td><?= date('d/m/Y H:i', strtotime($row['updated_at'])) ?></td>
            <td><?= $row['po_no'] ?></td>
            <td><?= htmlspecialchars($row['material_name']) ?></td>
            <td><?= floatval($row['qty']) ?></td>
            <td><?= $row['unit'] ?></td>
            <td><?= floatval($row['price']) ?></td>
            <td><?= floatval($row['total_amount']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>