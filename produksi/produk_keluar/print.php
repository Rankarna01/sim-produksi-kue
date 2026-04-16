<?php
require_once '../../config/database.php';

$out_id = $_GET['id'] ?? '';

if (empty($out_id)) die("Data tidak valid.");

$stmtHead = $pdo->prepare("
    SELECT o.invoice_no as out_id, o.origin_invoice, o.created_at as tgl_keluar, o.reason, o.notes, 
           COALESCE(e.name, u.name) as karyawan,
           p.created_at as tgl_produksi,
           k.name as asal_dapur
    FROM product_outs o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN employees e ON o.employee_id = e.id
    LEFT JOIN kitchens k ON e.kitchen_id = k.id
    LEFT JOIN productions p ON o.origin_invoice = p.invoice_no
    WHERE o.invoice_no = ?
    LIMIT 1
");
$stmtHead->execute([$out_id]);
$header = $stmtHead->fetch();

if (!$header) die("Data penarikan tidak ditemukan.");

$stmtDetail = $pdo->prepare("
    SELECT pr.name as produk, o.quantity
    FROM product_outs o
    JOIN products pr ON o.product_id = pr.id
    WHERE o.invoice_no = ?
");
$stmtDetail->execute([$out_id]);
$details = $stmtDetail->fetchAll();

$total_keseluruhan = 0;
foreach ($details as $d) {
    $total_keseluruhan += (int)$d['quantity'];
}

$barcode_str = $header['out_id']; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Produk Keluar</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            color: #000;
            background: #fff;
            font-size: 14px;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .title { font-size: 18px; margin-bottom: 5px; font-weight: bold; }
        
        .table-info { width: 100%; font-size: 14px; margin-bottom: 5px; }
        .table-produk { width: 100%; font-size: 14px; margin-top: 5px; border-collapse: collapse; }
        .table-produk th { border-bottom: 1px solid #000; padding-bottom: 5px; text-align: left; }
        .table-produk td { padding-top: 5px; }
        
        .barcode-container { margin-top: 15px; text-align: center; }
        .barcode-container svg { width: 100%; max-height: 75px; }
        
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    
    <div class="text-center">
        <div class="title">ROTIKU - PRODUK KELUAR</div>
        <div>Bukti Penarikan Store</div>
    </div>
    
    <div class="divider"></div>
    
    <table class="table-info">
        <tr><td style="width: 90px;">Tgl Produksi</td><td>: <?= date('d/m/y H:i', strtotime($header['tgl_produksi'])) ?></td></tr>
        <tr><td>Tgl Ditarik</td><td>: <?= date('d/m/y H:i', strtotime($header['tgl_keluar'])) ?></td></tr>
        <tr><td>ID Tarik</td><td>: <?= $header['out_id'] ?></td></tr>
        <tr><td>Inv Asal</td><td>: <?= $header['origin_invoice'] ?></td></tr>
        <tr><td>Dapur</td><td>: <?= $header['asal_dapur'] ?? '-' ?></td></tr>
        <tr><td>Oleh</td><td>: <?= $header['karyawan'] ?></td></tr>
        <tr><td>Alasan</td><td>: <?= strtoupper($header['reason']) ?></td></tr>
    </table>
    
    <div class="divider"></div>
    
    <div class="text-bold" style="font-size: 14px; margin-bottom: 5px;">DAFTAR PRODUK DITARIK:</div>
    <table class="table-produk">
        <?php foreach ($details as $idx => $d): ?>
        <tr>
            <td style="width: 20px;" valign="top"><?= $idx + 1 ?>.</td>
            <td><?= strtoupper($d['produk']) ?></td>
            <td style="text-align: right; font-weight: bold; width: 60px;" valign="top"><?= $d['quantity'] ?> Pcs</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <div class="divider"></div>
    
    <table style="width: 100%; font-size: 15px;">
        <tr>
            <td class="text-bold" style="text-align: right; padding-right: 10px;">TOTAL :</td>
            <td class="text-bold" style="text-align: right; width: 60px;"><?= $total_keseluruhan ?> Pcs</td>
        </tr>
    </table>
    
    <?php if (!empty($header['notes'])): ?>
    <div style="margin-top: 15px; font-size: 12px; border: 1px dashed #000; padding: 6px; border-radius: 4px;">
        <strong>Catatan Petugas:</strong><br>
        <?= nl2br(htmlspecialchars($header['notes'])) ?>
    </div>
    <?php endif; ?>
    
    <div class="divider" style="margin-top: 15px;"></div>
    
    <div class="text-center" style="font-size: 11px;">
        Struk ini adalah bukti sah bahwa barang telah dikeluarkan dari sistem stok Store karena <?= strtolower($header['reason']) ?>.
    </div>

    <div class="barcode-container">
        <svg id="barcode"></svg>
    </div>

    <div class="text-center no-print" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px; cursor: pointer; border: 1px solid #000; background: #eee; border-radius: 5px;">Print Ulang</button>
        <button onclick="window.close()" style="padding: 10px; cursor: pointer; border: 1px solid #000; background: #eee; border-radius: 5px;">Tutup</button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            JsBarcode("#barcode", "<?= $barcode_str ?>", {
                format: "CODE128",
                displayValue: true,
                fontSize: 14,        
                height: 50,          
                width: 2,            
                margin: 0            
            });

            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>