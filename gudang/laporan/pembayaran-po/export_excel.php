<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pembayaran_" . date('Ymd_His') . ".xls");

$method_id = $_GET['method'] ?? 'semua';
$status_po = $_GET['status_po'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($method_id !== 'semua') { $whereClause .= " AND pp.payment_method_id = ?"; $params[] = $method_id; }

if ($status_po === 'paid') { $whereClause .= " AND po.payment_status = 'paid'"; }
elseif ($status_po === 'unpaid_partial') { $whereClause .= " AND po.payment_status IN ('unpaid', 'partial')"; }

if ($filter_date === 'harian') { $whereClause .= " AND DATE(pp.payment_date) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(pp.payment_date) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (po.po_no LIKE ? OR s.name LIKE ? OR pp.notes LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$joins = "FROM purchase_payments pp JOIN purchase_orders po ON pp.po_id = po.id JOIN suppliers s ON po.supplier_id = s.id JOIN payment_methods pm ON pp.payment_method_id = pm.id JOIN users u ON pp.user_id = u.id";

$sumStmt = $pdo->prepare("SELECT SUM(pp.amount) $joins $whereClause");
$sumStmt->execute($params);
$grand_total = $sumStmt->fetchColumn() ?: 0;

$sql = "SELECT pp.*, po.po_no, po.payment_status, s.name as supplier_name, pm.name as method_name, u.name as admin_name $joins $whereClause ORDER BY pp.payment_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
    <thead>
        <tr><th colspan="8"><h3>LAPORAN PEMBAYARAN PO (HUTANG SUPPLIER)</h3></th></tr>
        <tr><th colspan="8">Waktu Tarik: <?= date('d M Y H:i:s') ?></th></tr>
        <tr>
            <th style="background-color: #f4f4f4;">Tanggal</th>
            <th style="background-color: #f4f4f4;">No PO</th>
            <th style="background-color: #f4f4f4;">Status PO</th>
            <th style="background-color: #f4f4f4;">Supplier</th>
            <th style="background-color: #f4f4f4;">Metode Pembayaran</th>
            <th style="background-color: #f4f4f4;">Catatan</th>
            <th style="background-color: #f4f4f4;">Admin</th>
            <th style="background-color: #f4f4f4;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($data as $row): 
            $stat = $row['payment_status'] == 'paid' ? 'LUNAS' : ($row['payment_status'] == 'partial' ? 'PARSIAL' : 'BELUM LUNAS');
        ?>
        <tr>
            <td><?= date('d/m/Y H:i', strtotime($row['payment_date'])) ?></td>
            <td style="color: #1d4ed8; font-weight: bold;"><?= $row['po_no'] ?></td>
            <td style="text-align: center; font-weight: bold;"><?= $stat ?></td>
            <td><?= $row['supplier_name'] ?></td>
            <td><?= $row['method_name'] ?></td>
            <td><?= $row['notes'] ?: '-' ?></td>
            <td><?= $row['admin_name'] ?></td>
            <td style="text-align: right;"><?= floatval($row['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold; background-color: #f4f4f4;">TOTAL PEMBAYARAN</td>
            <td style="text-align: right; font-weight: bold; background-color: #f4f4f4; color: #1d4ed8;"><?= floatval($grand_total) ?></td>
        </tr>
    </tbody>
</table>