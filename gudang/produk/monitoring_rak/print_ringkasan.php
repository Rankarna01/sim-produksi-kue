<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$stmtToko = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
$toko = $stmtToko->fetch(PDO::FETCH_ASSOC);
$nama_toko = $toko['store_name'] ?? 'PERUSAHAAN KAMI';

// PERBAIKAN: Menggunakan m.rack_id sesuai dengan database
$sql = "SELECT r.name, COUNT(m.id) as total_items, IFNULL(SUM(m.stock), 0) as total_stock
        FROM racks r
        LEFT JOIN materials_stocks m ON r.id = m.rack_id AND m.status = 'active'
        GROUP BY r.id ORDER BY r.name ASC";
$racks = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ringkasan Lokasi Rak</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { max-width: 800px; margin: 20px auto; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom:20px; padding:10px; background:blue; color:white; border:none; cursor:pointer;">Cetak Sekarang</button>
    <div class="container">
        <div class="header">
            <h2 style="margin:0; text-transform:uppercase;"><?= htmlspecialchars($nama_toko) ?></h2>
            <h3 style="margin:5px 0 0 0;">RINGKASAN LOKASI RAK GUDANG</h3>
            <p style="margin:5px 0 0 0;">Tanggal Cetak: <?= date('d M Y H:i') ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" width="10%">No</th>
                    <th width="50%">Lokasi Rak</th>
                    <th class="text-center" width="20%">Kapasitas (Jenis Barang)</th>
                    <th class="text-right" width="20%">Total Kuantitas Fisik</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $gTotalItems = 0; $gTotalStok = 0;
                foreach ($racks as $idx => $rack): 
                    $gTotalItems += $rack['total_items'];
                    $gTotalStok += $rack['total_stock'];
                ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td><strong><?= strtoupper(htmlspecialchars($rack['name'])) ?></strong></td>
                        <td class="text-center"><?= $rack['total_items'] ?> Jenis</td>
                        <td class="text-right"><strong><?= floatval($rack['total_stock']) ?></strong> Unit</td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" class="text-right"><strong>GRAND TOTAL:</strong></td>
                    <td class="text-center"><strong><?= $gTotalItems ?> Jenis</strong></td>
                    <td class="text-right"><strong><?= $gTotalStok ?> Unit</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>