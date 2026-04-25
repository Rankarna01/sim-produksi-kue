<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$rack_id = $_GET['id'] ?? '';

// Ambil Profil Toko
$stmtToko = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
$toko = $stmtToko->fetch(PDO::FETCH_ASSOC);
$nama_toko = $toko['store_name'] ?? 'PERUSAHAAN KAMI';

// Ambil Nama Rak
$stmtRak = $pdo->prepare("SELECT name FROM racks WHERE id = ?");
$stmtRak->execute([$rack_id]);
$rack_name = $stmtRak->fetchColumn();

if (!$rack_name) die("Rak tidak valid.");

// PERBAIKAN: Menggunakan m.rack_id sesuai dengan database
$sql = "SELECT m.sku_code, m.material_name, m.stock, m.unit, c.name as category_name
        FROM materials_stocks m
        LEFT JOIN material_categories c ON m.category_id = c.id
        WHERE m.rack_id = ? AND m.status = 'active'
        ORDER BY m.material_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$rack_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Isi Rak <?= htmlspecialchars($rack_name) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { max-width: 800px; margin: 20px auto; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-center { text-align: center; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom:20px; padding:10px; background:blue; color:white; border:none; cursor:pointer;">Cetak Sekarang</button>
    <div class="container">
        <div class="header">
            <h2 style="margin:0; text-transform:uppercase;"><?= htmlspecialchars($nama_toko) ?></h2>
            <h3 style="margin:5px 0 0 0;">DAFTAR INVENTARIS FISIK RAK</h3>
            <p style="margin:5px 0 0 0; font-size: 18px; font-weight: bold; color: #2563eb;">LOKASI RAK: <?= htmlspecialchars($rack_name) ?></p>
            <p style="margin:5px 0 0 0;">Tanggal Cetak: <?= date('d/m/Y H:i') ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center" width="5%">No</th>
                    <th width="20%">Barcode (SKU)</th>
                    <th width="45%">Nama Barang</th>
                    <th class="text-center" width="15%">Kategori</th>
                    <th class="text-center" width="15%">Sisa Stok</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) == 0): ?>
                    <tr><td colspan="5" class="text-center">Rak ini masih kosong.</td></tr>
                <?php else: ?>
                    <?php foreach ($items as $idx => $item): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($item['sku_code']) ?></td>
                            <td><strong><?= strtoupper(htmlspecialchars($item['material_name'])) ?></strong></td>
                            <td class="text-center"><?= htmlspecialchars($item['category_name'] ?: '-') ?></td>
                            <td class="text-center" style="font-size: 14px;"><strong><?= floatval($item['stock']) ?></strong> <?= htmlspecialchars($item['unit']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>