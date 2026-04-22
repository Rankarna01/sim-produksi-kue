<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

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

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Purchase Order</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .badge-green { color: #16a34a; font-weight: bold; }
        .badge-red { color: #e11d48; font-weight: bold; }
        @media print { @page { size: landscape; } button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN PURCHASE ORDER (PO)</h2>
        <p>Periode: <?= $periode_teks ?> | Status PO: <?= strtoupper($status_po) ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 12%">ID PO</th>
                <th style="width: 10%">Waktu Buat</th>
                <th style="width: 10%">Waktu Terima</th>
                <th style="width: 15%">Supplier</th>
                <th style="width: 8%; text-align:center;">Status</th>
                <th style="width: 25%">Item</th>
                <th style="width: 10%; text-align:right;">Total (Rp)</th>
                <th style="width: 10%">Dibuat Oleh</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($pos) > 0): ?>
                <?php foreach($pos as $row): 
                    $stmtItem = $pdo->prepare("SELECT ms.material_name, pod.qty, pod.price FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?");
                    $stmtItem->execute([$row['id']]);
                    $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
                    
                    $itemList = "";
                    foreach($items as $i) {
                        $priceStr = $i['price'] > 0 ? " @ Rp " . number_format($i['price'],0,',','.') : "";
                        $itemList .= "&bull; " . $i['material_name'] . " (" . floatval($i['qty']) . ")" . $priceStr . "<br>";
                    }
                    $statusClass = ($row['status'] === 'received') ? 'badge-green' : (($row['status'] === 'rejected' || $row['status'] === 'cancelled') ? 'badge-red' : '');
                ?>
                <tr>
                    <td><strong><?= $row['po_no'] ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td><?= ($row['status'] === 'received' && $row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-' ?></td>
                    <td><?= $row['supplier_name'] ?></td>
                    <td style="text-align: center;" class="<?= $statusClass ?>"><?= strtoupper($row['status']) ?></td>
                    <td><?= $itemList ?></td>
                    <td style="text-align: right;"><strong><?= number_format($row['total_amount'],0,',','.') ?></strong></td>
                    <td><?= $row['admin_name'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center;">Tidak ada data laporan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>