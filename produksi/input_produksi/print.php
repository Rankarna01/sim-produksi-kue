<?php
require_once '../../config/database.php';
$id = $_GET['id'] ?? 0;

// AMBIL NAMA KARYAWAN DARI TABEL EMPLOYEES (FALLBACK KE TABEL USERS JIKA KOSONG UNTUK DATA LAMA)
$stmtHead = $pdo->prepare("
    SELECT p.invoice_no, p.created_at, p.notes, 
           COALESCE(e.name, u.name) as karyawan
    FROM productions p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN employees e ON p.employee_id = e.id
    WHERE p.id = ?
");
$stmtHead->execute([$id]);
$header = $stmtHead->fetch();

if (!$header) die("Data tidak ditemukan.");

$stmtDetail = $pdo->prepare("
    SELECT pr.name as produk, d.quantity, d.barcode
    FROM production_details d
    JOIN products pr ON d.product_id = pr.id
    WHERE d.production_id = ?
");
$stmtDetail->execute([$id]);
$details = $stmtDetail->fetchAll();

$barcode_str = $details[0]['barcode']; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Produksi</title>
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
        <div class="title">ROTIKU PRODUKSI</div>
        <div>Tiket Masuk Gudang</div>
    </div>
    
    <div class="divider"></div>
    
    <table class="table-info">
        <tr><td style="width: 40px;">Tgl</td><td>: <?= date('d/m/Y H:i', strtotime($header['created_at'])) ?></td></tr>
        <tr><td>Inv</td><td>: <?= $header['invoice_no'] ?></td></tr>
        <tr><td>Oleh</td><td>: <?= $header['karyawan'] ?></td></tr>
    </table>
    
    <div class="divider"></div>
    
    <div class="text-bold" style="font-size: 14px; margin-bottom: 5px;">DAFTAR PRODUK:</div>
    <table class="table-produk">
        <?php foreach ($details as $idx => $d): ?>
        <tr>
            <td style="width: 20px;" valign="top"><?= $idx + 1 ?>.</td>
            <td><?= strtoupper($d['produk']) ?></td>
            <td style="text-align: right; font-weight: bold; width: 60px;" valign="top"><?= $d['quantity'] ?> Pcs</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <?php if (!empty($header['notes'])): ?>
    <div style="margin-top: 15px; font-size: 12px; border: 1px dashed #000; padding: 6px; border-radius: 4px;">
        <strong>Catatan Dapur:</strong><br>
        <?= nl2br(htmlspecialchars($header['notes'])) ?>
    </div>
    <?php endif; ?>
    
    <div class="divider" style="margin-top: 15px;"></div>
    
    <div class="text-center" style="font-size: 11px;">
        Tempelkan struk ini pada keranjang. Scan barcode untuk memproses produk.
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