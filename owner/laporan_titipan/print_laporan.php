<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$warehouse_id = $_GET['warehouse_id'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "WHERE 1=1";
$params = [];

if (!empty($start_date)) { $where .= " AND DATE(p.created_at) >= ?"; $params[] = $start_date; }
if (!empty($end_date)) { $where .= " AND DATE(p.created_at) <= ?"; $params[] = $end_date; }
if (!empty($warehouse_id)) { $where .= " AND p.warehouse_id = ?"; $params[] = $warehouse_id; }
if (!empty($status_filter)) { $where .= " AND p.status = ?"; $params[] = $status_filter; }

$sql = "SELECT p.created_at, p.invoice_no, p.status, w.name as store_name, 
               b.nama_barang, b.nama_umkm, d.quantity, 
               b.harga_modal, b.harga_jual,
               (d.quantity * b.harga_modal) as total_modal,
               (d.quantity * b.harga_jual) as total_omset,
               (d.quantity * (b.harga_jual - b.harga_modal)) as profit
        FROM titipan_productions p
        JOIN titipan_production_details d ON p.id = d.titipan_production_id
        JOIN barang_titipan b ON d.titipan_id = b.id
        LEFT JOIN warehouses w ON p.warehouse_id = w.id
        $where ORDER BY p.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SUMMARY HANYA MENGHITUNG STATUS 'received' AGAR AKURAT
$sum_qty = 0; $sum_omset = 0; $sum_profit = 0;
foreach($data as $d) {
    if ($d['status'] === 'received') {
        $sum_qty += $d['quantity'];
        $sum_omset += $d['total_omset'];
        $sum_profit += $d['profit'];
    }
}

function rp($angka) { return "Rp " . number_format($angka, 0, ',', '.'); }

$statusLabel = "Semua Status";
if ($status_filter === 'received') $statusLabel = "Valid (Masuk Gudang)";
if ($status_filter === 'pending') $statusLabel = "Pending";
if ($status_filter === 'ditolak') $statusLabel = "Ditolak";
if ($status_filter === 'cancelled') $statusLabel = "Dibatalkan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Titipan UMKM</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { max-width: 900px; margin: 20px auto; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .dim { color: #888; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom:20px; padding:10px; background:blue; color:white; border:none; cursor:pointer;">Cetak Sekarang</button>
    <div class="container">
        <div class="header">
            <h2>LAPORAN PENYALURAN PRODUK TITIPAN (UMKM)</h2>
            <p>Periode: <?= $start_date ?: 'Awal' ?> s/d <?= $end_date ?: 'Sekarang' ?> | Filter: <?= $statusLabel ?></p>
        </div>

        <table style="margin-bottom: 20px; width: 60%;">
            <tr><th colspan="2" class="text-center" style="background: #e5e7eb;">SUMMARY (Hanya Menghitung Status Valid/Diterima)</th></tr>
            <tr><th width="50%">Total Barang Terkirim</th><td><strong><?= $sum_qty ?> Pcs</strong></td></tr>
            <tr><th>Total Estimasi Omset</th><td><strong><?= rp($sum_omset) ?></strong></td></tr>
            <tr><th>Total Profit Bersih</th><td style="color: green;"><strong><?= rp($sum_profit) ?></strong></td></tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>Tgl & Invoice</th>
                    <th class="text-center">Status</th>
                    <th>Store Tujuan</th>
                    <th>Barang & UMKM</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Harga Jual</th>
                    <th class="text-right">Total Omset</th>
                    <th class="text-right">Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($data) == 0): ?>
                    <tr><td colspan="9" class="text-center">Tidak ada data.</td></tr>
                <?php else: ?>
                    <?php foreach ($data as $idx => $row): 
                        $cls = ($row['status'] !== 'received' && $row['status'] !== 'pending') ? 'dim' : '';
                    ?>
                        <tr class="<?= $cls ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?><br><small><?= $row['invoice_no'] ?></small></td>
                            <td class="text-center"><strong><?= strtoupper($row['status']) ?></strong></td>
                            <td><?= $row['store_name'] ?></td>
                            <td><strong><?= $row['nama_barang'] ?></strong><br><small><?= $row['nama_umkm'] ?></small></td>
                            <td class="text-center"><strong><?= $row['quantity'] ?></strong></td>
                            <td class="text-right"><?= rp($row['harga_jual']) ?></td>
                            <td class="text-right"><?= rp($row['total_omset']) ?></td>
                            <td class="text-right"><strong><?= rp($row['profit']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>