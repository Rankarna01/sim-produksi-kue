<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_PO_" . date('Ymd_His') . ".xls");

$status_po = $_GET['status_po'] ?? 'semua';
$status_pay = $_GET['status_pay'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($status_po !== 'semua') { $whereClause .= " AND p.status = ?"; $params[] = $status_po; }
if ($status_pay !== 'semua') { $whereClause .= " AND p.payment_status = ?"; $params[] = $status_pay; }

if ($filter_date === 'harian') { $whereClause .= " AND DATE(p.created_at) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(p.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}

if (!empty($search)) {
    $whereClause .= " AND (p.po_no LIKE ? OR s.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT p.*, s.name as supplier_name, u.name as admin_name FROM purchase_orders p JOIN suppliers s ON p.supplier_id = s.id JOIN users u ON p.created_by = u.id $whereClause ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="8"><h3>LAPORAN PURCHASE ORDER (PO) GUDANG</h3></th></tr>
        <tr><th colspan="8">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">ID PO</th>
            <th style="background-color: #f4f4f4;">Waktu Buat</th>
            <th style="background-color: #f4f4f4;">Waktu Terima</th>
            <th style="background-color: #f4f4f4;">Supplier</th>
            <th style="background-color: #f4f4f4;">Status PO</th>
            <th style="background-color: #f4f4f4;">Status Pembayaran</th>
            <th style="background-color: #f4f4f4;">Total Nilai (Rp)</th>
            <th style="background-color: #f4f4f4;">Dibuat Oleh</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($pos as $row): ?>
        <tr>
            <td><?= $row['po_no'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td><?= ($row['status'] === 'received' && $row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-' ?></td>
            <td><?= $row['supplier_name'] ?></td>
            <td><?= strtoupper($row['status']) ?></td>
            <td><?= strtoupper($row['payment_status']) ?></td>
            <td><?= floatval($row['total_amount']) ?></td>
            <td><?= $row['admin_name'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>