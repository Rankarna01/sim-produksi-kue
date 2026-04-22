<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$filter_date = $_GET['filter_date'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

$whereClause = "WHERE so.status = 'approved'";
$params = [];

if ($filter_date === 'harian') { $whereClause .= " AND DATE(so.opname_date) = CURDATE()"; } 
elseif ($filter_date === 'periode' && !empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(so.opname_date) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}

if (!empty($search)) {
    $whereClause .= " AND (so.opname_no LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

// Gunakan tabel gudang
$sql = "SELECT so.*, u.name as admin_name FROM gudang_stok_opnames so JOIN users u ON so.created_by = u.id $whereClause ORDER BY so.opname_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$opnames = $stmt->fetchAll(PDO::FETCH_ASSOC);

$periode_teks = "Semua Waktu";
if($filter_date === 'harian') $periode_teks = date('d F Y');
if($filter_date === 'periode') $periode_teks = date('d/m/Y', strtotime($start_date)) . " - " . date('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Stok Opname Gudang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .card { border: 1px solid #cbd5e1; margin-bottom: 20px; border-radius: 5px; overflow: hidden;}
        .card-header { background-color: #f8fafc; padding: 10px; border-bottom: 1px solid #cbd5e1; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 10px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; }
        .text-blue { color: #16a34a; font-weight: bold; } /* Hijau untuk Plus */
        .text-red { color: #e11d48; font-weight: bold; } /* Merah untuk Minus */
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#dc2626; color:white; border:none; border-radius:5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN STOK OPNAME GUDANG</h2>
        <p>Periode: <?= $periode_teks ?></p>
    </div>
    
    <?php if(count($opnames) > 0): ?>
        <?php foreach($opnames as $op): 
            $sqlDet = "SELECT sod.*, ms.material_name, ms.unit FROM gudang_stok_opname_details sod JOIN materials_stocks ms ON sod.material_id = ms.id WHERE sod.opname_id = ?";
            $stmtDet = $pdo->prepare($sqlDet);
            $stmtDet->execute([$op['id']]);
            $details = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0 0 5px 0;">Opname Stok <span style="color:#2563eb">#<?= $op['opname_no'] ?></span></h3>
                <table style="border:none; width: 50%; margin-bottom:0;">
                    <tr><td style="border:none; padding:2px;"><strong>Tanggal</strong></td><td style="border:none; padding:2px;">: <?= date('d/m/Y H:i', strtotime($op['opname_date'])) ?></td></tr>
                    <tr><td style="border:none; padding:2px;"><strong>Auditor</strong></td><td style="border:none; padding:2px;">: <?= $op['admin_name'] ?></td></tr>
                </table>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align:center;">#</th>
                        <th>Produk / Barang</th>
                        <th style="width: 15%; text-align:center;">Qty System</th>
                        <th style="width: 15%; text-align:center;">Qty Fisik (Aktual)</th>
                        <th style="width: 15%; text-align:center;">Selisih</th>
                        <th style="width: 25%;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details as $idx => $item): 
                        $diff = floatval($item['difference']);
                        $diffHtml = ($diff > 0) ? "<span class='text-blue'>+$diff</span>" : (($diff < 0) ? "<span class='text-red'>$diff</span>" : "0");
                    ?>
                    <tr>
                        <td style="text-align:center;"><?= $idx + 1 ?></td>
                        <td><?= $item['material_name'] ?></td>
                        <td style="text-align:center;"><?= floatval($item['system_stock']) ?> <?= $item['unit'] ?></td>
                        <td style="text-align:center;"><?= floatval($item['physical_stock']) ?> <?= $item['unit'] ?></td>
                        <td style="text-align:center; background:#f8fafc;"><?= $diffHtml ?></td>
                        <td><?= $item['notes'] ?: '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: #666; font-style: italic;">Tidak ada data laporan stok opname.</p>
    <?php endif; ?>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>