<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$search = $_GET['search'] ?? '';
$tab = $_GET['tab'] ?? 'semua';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($tab !== 'semua') {
    $whereClause .= " AND bk.status = ?";
    $params[] = $tab;
}
if (!empty($start_date) && !empty($end_date)) {
    $whereClause .= " AND DATE(bk.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}
if (!empty($search)) {
    $whereClause .= " AND (bk.transaction_no LIKE ? OR ms.material_name LIKE ? OR ms.sku_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = "
    SELECT bk.*, ms.material_name, ms.unit, ms.sku_code, u.name as admin_name 
    FROM barang_keluar bk
    LEFT JOIN materials_stocks ms ON bk.material_id = ms.id
    LEFT JOIN users u ON bk.user_id = u.id
    $whereClause 
    ORDER BY bk.created_at ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$periode_text = (!empty($start_date) && !empty($end_date)) ? date('d/m/Y', strtotime($start_date)) . ' s.d ' . date('d/m/Y', strtotime($end_date)) : 'Semua Waktu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Barang Keluar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4 landscape; margin: 0; }
        body { background-color: #f1f5f9; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .a4-container { width: 297mm; min-height: 210mm; background: white; margin: 20px auto; padding: 15mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        @media print { body { background-color: white; } .a4-container { margin: 0; box-shadow: none; padding: 10mm; } .no-print { display: none; } }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        th { font-weight: 800; text-transform: uppercase; font-size: 10px; color: #64748b; text-align: left; background: #f8fafc;}
    </style>
</head>
<body class="text-slate-800">
    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md">Cetak Laporan</button>
    </div>

    <div class="a4-container">
        <div class="flex justify-between items-end border-b-2 border-slate-800 pb-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tighter">LAPORAN BARANG KELUAR</h1>
                <p class="text-xs font-bold text-slate-500 mt-1">Gudang Pilar | Status: <?= strtoupper($tab) ?> | Periode: <?= $periode_text ?></p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Dicetak Pada</p>
                <p class="text-sm font-black text-slate-700"><?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Waktu & TRX</th>
                    <th style="width: 25%;">Nama Barang & SKU</th>
                    <th style="width: 10%;">Status</th>
                    <th class="text-center" style="width: 10%;">Jumlah Keluar</th>
                    <th style="width: 25%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($data) > 0): ?>
                    <?php foreach($data as $idx => $item): ?>
                    <tr>
                        <td class="text-slate-500 font-bold"><?= $idx + 1 ?></td>
                        <td>
                            <span class="font-bold text-slate-700 block"><?= date('d/m/y H:i', strtotime($item['created_at'])) ?></span>
                            <span class="text-[9px] text-slate-400 font-mono"><?= $item['transaction_no'] ?></span>
                        </td>
                        <td class="uppercase">
                            <span class="font-black text-slate-800"><?= htmlspecialchars($item['material_name']) ?></span>
                            <span class="text-[9px] text-slate-400 block font-mono">#<?= $item['sku_code'] ?></span>
                        </td>
                        <td class="text-slate-500 font-bold text-xs"><?= $item['status'] ?></td>
                        <td class="text-center font-black text-sm text-rose-600">-<?= (float)$item['qty'] ?> <span class="text-[9px] text-rose-400 uppercase"><?= $item['unit'] ?></span></td>
                        <td class="text-slate-500 italic"><?= htmlspecialchars($item['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-10 italic text-slate-400 font-bold">Tidak ada transaksi ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <script>window.onload = function() { setTimeout(window.print, 500); }</script>
</body>
</html>