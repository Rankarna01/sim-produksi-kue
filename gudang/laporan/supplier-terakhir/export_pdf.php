<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

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

// Ambil Summary per Supplier
$sql = "
    SELECT s.id as supplier_id, s.name as supplier_name, COUNT(po.id) as total_transaksi, SUM(po.total_amount) as total_pembelian
    FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.id
    $whereClause GROUP BY s.id, s.name ORDER BY total_pembelian DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Analisa Supplier</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .supplier-box { margin-bottom: 25px; border: 1px solid #cbd5e1; border-radius: 5px; padding: 10px; background: #f8fafc; page-break-inside: avoid; }
        table { border-collapse: collapse; width: 100%; background: #fff; margin-top:10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; }
        th { background-color: #e2e8f0; font-weight: bold; font-size: 11px; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#dc2626; color:white; border:none; border-radius:5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN ANALISA PEMBELIAN SUPPLIER</h2>
        <p>Periode: <?= $periode_teks ?></p>
    </div>
    
    <?php if(count($suppliers) > 0): ?>
        <?php foreach($suppliers as $sup): 
            // Ambil Detail PO untuk Supplier Ini
            $sqlDet = "SELECT id as po_id, po_no, updated_at, total_amount FROM purchase_orders po $whereClause AND po.supplier_id = ? ORDER BY updated_at DESC";
            $stmtDet = $pdo->prepare($sqlDet);
            $stmtDet->execute(array_merge($params, [$sup['supplier_id']]));
            $pos = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="supplier-box">
            <h3 style="margin: 0 0 5px 0; color: #1e293b;"><?= mb_strtoupper($sup['supplier_name']) ?></h3>
            <p style="margin: 0; font-size:11px; color:#64748b;">Total Transaksi: <strong><?= $sup['total_transaksi'] ?> PO</strong> | Total Nilai Pembelian: <strong style="color:#059669;">Rp <?= number_format($sup['total_pembelian'],0,',','.') ?></strong></p>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Tanggal Terima</th>
                        <th style="width: 20%;">No. PO</th>
                        <th style="width: 45%;">Rincian Barang</th>
                        <th style="width: 20%; text-align:right;">Nilai Faktur (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pos as $po): 
                        // Ambil Item Barang
                        $sqlItem = "SELECT ms.material_name, pod.qty, pod.price, ms.unit FROM purchase_order_details pod JOIN materials_stocks ms ON pod.material_id = ms.id WHERE pod.po_id = ?";
                        $stmtItem = $pdo->prepare($sqlItem);
                        $stmtItem->execute([$po['po_id']]);
                        $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
                        
                        $itemText = [];
                        foreach($items as $i) { $itemText[] = "- " . $i['material_name'] . " (" . floatval($i['qty']) . " " . $i['unit'] . ")"; }
                    ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($po['updated_at'])) ?></td>
                        <td><strong><?= $po['po_no'] ?></strong></td>
                        <td style="font-size:10px;"><?= implode("<br>", $itemText) ?></td>
                        <td style="text-align:right; font-weight:bold;"><?= number_format($po['total_amount'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; font-style: italic;">Tidak ada data laporan.</p>
    <?php endif; ?>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>