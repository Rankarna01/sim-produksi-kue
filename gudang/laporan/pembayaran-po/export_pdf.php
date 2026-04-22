<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$method_id = $_GET['method'] ?? 'semua';
$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($method_id !== 'semua') { $whereClause .= " AND pp.payment_method_id = ?"; $params[] = $method_id; }

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

$sql = "SELECT pp.*, po.po_no, s.name as supplier_name, pm.name as method_name, u.name as admin_name $joins $whereClause ORDER BY pp.payment_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Pembayaran PO</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-blue { color: #1d4ed8; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#dc2626; color:white; border:none; border-radius:5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN PEMBAYARAN PO (HUTANG SUPPLIER)</h2>
        <p>Periode: <?= $periode_teks ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 15%;">No PO</th>
                <th style="width: 20%;">Supplier</th>
                <th style="width: 12%;">Metode</th>
                <th style="width: 18%;">Catatan</th>
                <th style="width: 10%;">Admin</th>
                <th style="width: 10%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $row): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['payment_date'])) ?></td>
                    <td class="font-bold text-blue"><?= $row['po_no'] ?></td>
                    <td><?= $row['supplier_name'] ?></td>
                    <td><?= $row['method_name'] ?></td>
                    <td><?= $row['notes'] ?: '-' ?></td>
                    <td><?= $row['admin_name'] ?></td>
                    <td class="text-right font-bold"><?= number_format($row['amount'],0,',','.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="6" class="text-right font-bold" style="background-color: #f4f4f4; padding: 12px 8px;">TOTAL PEMBAYARAN</td>
                    <td class="text-right font-bold" style="background-color: #f4f4f4; font-size: 14px; color: #1d4ed8;">
                        Rp <?= number_format($grand_total,0,',','.') ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center;">Tidak ada data laporan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>