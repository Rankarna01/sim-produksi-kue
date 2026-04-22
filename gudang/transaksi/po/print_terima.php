<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? '';
$stmt = $pdo->prepare("
    SELECT p.*, s.name as supplier_name, u.name as admin_name 
    FROM purchase_orders p 
    JOIN suppliers s ON p.supplier_id = s.id 
    JOIN users u ON p.created_by = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$po = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$po || $po['status'] !== 'received') die("Data Penerimaan belum selesai!");

$stmtItems = $pdo->prepare("
    SELECT pod.*, ms.material_name, ms.unit, ms.sku_code 
    FROM purchase_order_details pod 
    JOIN materials_stocks ms ON pod.material_id = ms.id 
    WHERE pod.po_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Tanda Terima: <?= $po['po_no'] ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .item-table th { background-color: #e2e8f0; text-align: center; }
        .sign-area { width: 100%; margin-top: 50px; display: table; }
        .sign-box { display: table-cell; width: 33.33%; text-align: center; }
        .sign-line { margin-top: 60px; border-top: 1px solid #000; width: 80%; display: inline-block; }
        @media print { button { display: none; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="margin-bottom: 20px; padding: 10px; cursor: pointer; background:green; color:white; border:none; border-radius:5px;">Cetak Tanda Terima</button>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="title">TANDA TERIMA BARANG (GOODS RECEIPT)</h1>
                <p style="margin: 5px 0 0 0; font-size:14px;"><strong>GUDANG PILAR</strong></p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; color:#059669;">RCV-<?= substr($po['po_no'], 3) ?></h2>
                <p style="margin: 5px 0 0 0;">Tgl. Terima: <?= date('d F Y H:i', strtotime($po['updated_at'])) ?></p>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td style="width: 20%;"><strong>Nomor Referensi PO</strong></td>
                <td style="width: 30%;">: <?= $po['po_no'] ?></td>
                <td style="width: 20%;"><strong>Nama Supplier</strong></td>
                <td style="width: 30%;">: <?= $po['supplier_name'] ?></td>
            </tr>
        </table>

        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Nama Barang</th>
                    <th style="width: 20%;">Qty Diterima</th>
                    <th style="width: 30%;">Harga Beli (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $idx => $item): ?>
                <tr>
                    <td style="text-align:center;"><?= $idx + 1 ?></td>
                    <td><?= $item['material_name'] ?> <br><small><?= $item['sku_code'] ?></small></td>
                    <td style="text-align:center; font-weight:bold;"><?= floatval($item['qty']) ?> <?= $item['unit'] ?></td>
                    <td style="text-align:right;">Rp <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:bold;">TOTAL NILAI FAKTUR :</td>
                    <td style="text-align:right; font-weight:bold; font-size:14px;">Rp <?= number_format($po['total_amount'], 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="sign-area">
            <div class="sign-box">
                <p>Dikirim Oleh,</p><br><br>
                <div class="sign-line"></div>
                <p><strong>( ___________________ )</strong><br>Kurir/Supplier</p>
            </div>
            <div class="sign-box">
                <p>Diterima & Dicek Oleh,</p><br><br>
                <div class="sign-line"></div>
                <p><strong><?= $_SESSION['name'] ?? 'Admin Gudang' ?></strong><br>Checker Gudang</p>
            </div>
            <div class="sign-box">
                <p>Mengetahui,</p><br><br>
                <div class="sign-line"></div>
                <p><strong>( ___________________ )</strong><br>Manager Operasional</p>
            </div>
        </div>
    </div>
    <script>window.onload = () => setTimeout(window.print, 500);</script>
</body>
</html>