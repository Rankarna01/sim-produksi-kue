<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$threshold = isset($_GET['threshold']) ? (float)$_GET['threshold'] : 10;
$search = $_GET['search'] ?? '';

$whereClause = "WHERE ms.status = 'active' AND ms.stock <= ?";
$params = [$threshold];

if (!empty($search)) {
    $whereClause .= " AND (ms.material_name LIKE ? OR ms.sku_code LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$sql = "SELECT ms.sku_code, ms.material_name, ms.stock, ms.unit, c.name as category_name, r.name as rack_name 
        FROM materials_stocks ms LEFT JOIN material_categories c ON ms.category_id = c.id LEFT JOIN racks r ON ms.rack_id = r.id 
        $whereClause ORDER BY ms.stock ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Laporan Stok Menipis</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { border-collapse: collapse; margin-top: 10px; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; text-align: center; }
        .text-red { color: #e11d48; font-weight: bold; text-align: center; }
        .text-orange { color: #ea580c; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px 20px; cursor: pointer; background:#e11d48; color:white; border:none; border-radius:5px;">Cetak Laporan</button>
    <div class="header">
        <h2>LAPORAN STOK MENIPIS / HABIS</h2>
        <p>Menampilkan barang dengan stok di bawah atau sama dengan: <strong><?= $threshold ?></strong></p>
        <p>Waktu Tarik Data: <?= date('d M Y, H:i') ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">SKU</th>
                <th style="width: 30%">Nama Barang</th>
                <th style="width: 20%">Kategori / Rak</th>
                <th style="width: 15%">Sisa Stok</th>
                <th style="width: 15%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($data) > 0): ?>
                <?php foreach($data as $idx => $row): 
                    $stock = floatval($row['stock']);
                    $status = $stock <= 0 ? 'HABIS' : 'KRITIS';
                    $colorClass = $stock <= 0 ? 'text-red' : 'text-orange';
                ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><?= $row['sku_code'] ?></td>
                    <td><strong><?= $row['material_name'] ?></strong></td>
                    <td><?= $row['category_name'] ?: '-' ?> <br> <small>Rak: <?= $row['rack_name'] ?: '-' ?></small></td>
                    <td class="<?= $colorClass ?>" style="font-size: 14px;"><?= $stock ?> <?= $row['unit'] ?></td>
                    <td class="<?= $colorClass ?>"><?= $status ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center;">Semua stok barang dalam kondisi aman.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>