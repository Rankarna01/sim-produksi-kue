<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';
checkPermission('trx_po');

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

$txt_per = (!empty($start_date) && !empty($end_date)) ? date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date)) : "Semua Waktu";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Retur PO</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; background:#dc2626; color:white; border:none; border-radius:5px; cursor:pointer;">Cetak PDF</button>
    <div class="header">
        <h2>LAPORAN HISTORI RETUR PO & PEMOTONGAN TAGIHAN</h2>
        <p>Periode: <?= $txt_per ?> | Status: <?= strtoupper($status) ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="12%">Waktu Retur</th>
                <th width="15%">No. PO & Supplier</th>
                <th width="18%">Barang</th>
                <th class="text-center" width="10%">Qty Retur</th>
                <th class="text-right" width="15%">Potongan (Rp)</th>
                <th class="text-center" width="10%">Status</th>
                <th width="15%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            <?php $total_semua = 0; foreach($data as $idx => $row): $total_semua += $row['total_potongan']; ?>
            <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                <td><b><?= $row['po_no'] ?></b><br><?= $row['supplier_name'] ?></td>
                <td><?= $row['material_name'] ?></td>
                <td class="text-center" style="color:red; font-weight:bold;">-<?= floatval($row['qty_return']) ?> <?= $row['unit'] ?></td>
                <td class="text-right">- Rp <?= number_format($row['total_potongan'], 0, ',', '.') ?></td>
                <td class="text-center"><?= strtoupper($row['status']) ?></td>
                <td><?= $row['reason'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($data)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data.</td></tr>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-right" style="font-weight:bold; font-size:14px;">TOTAL POTONGAN TAGIHAN : </td>
                    <td class="text-right" style="font-weight:bold; font-size:14px; color:red;">- Rp <?= number_format($total_semua, 0, ',', '.') ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>