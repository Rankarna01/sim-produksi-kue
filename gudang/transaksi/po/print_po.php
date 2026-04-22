<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? '';

// 1. AMBIL DATA PROFIL TOKO
$stmtToko = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
$toko = $stmtToko->fetch(PDO::FETCH_ASSOC);

// Fallback jika tabel profil toko kosong
$nama_toko = $toko['store_name'] ?? 'PERUSAHAAN KAMI';
$alamat_toko = $toko['address'] ?? 'Alamat Belum Disetting';
$telp_toko = $toko['phone'] ?? '-';
$email_toko = $toko['email'] ?? '-';
$logo_toko = !empty($toko['logo_path']) ? '../../../' . $toko['logo_path'] : null;

// 2. AMBIL DATA PO & SUPPLIER (Hanya yang statusnya sudah diterima)
$stmt = $pdo->prepare("
    SELECT p.*, s.name as supplier_name, u.name as admin_name 
    FROM purchase_orders p 
    JOIN suppliers s ON p.supplier_id = s.id 
    JOIN users u ON p.created_by = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$po = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$po || $po['status'] !== 'received') die("Data Penerimaan belum selesai atau tidak valid!");

// 3. AMBIL DETAIL ITEM BARANG
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
    <meta charset="UTF-8">
    <title>Tanda Terima: <?= $po['po_no'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; background-color: #f1f5f9; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .a4-container { width: 210mm; min-height: 297mm; background: white; margin: 20px auto; padding: 20mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .item-table th { background-color: #e2e8f0; text-align: center; }
        .sign-area { width: 100%; margin-top: 50px; display: table; }
        .sign-box { display: table-cell; width: 33.33%; text-align: center; }
        .sign-line { margin-top: 60px; border-top: 1px solid #000; width: 80%; display: inline-block; }
        @media print { body { background-color: white; } .a4-container { margin: 0; box-shadow: none; border: none; padding: 15mm; } button { display: none; } }
    </style>
</head>
<body>
    
    <div class="text-center mt-6" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-emerald-700 transition-colors">Cetak Tanda Terima</button>
        <button onclick="window.close()" class="bg-slate-200 text-slate-700 px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-slate-300 ml-2 transition-colors">Tutup</button>
    </div>

    <div class="a4-container">
        
        <div class="flex justify-between items-start border-b-4 border-slate-800 pb-4 mb-8">
            <div class="flex items-center gap-4">
                <?php if ($logo_toko && file_exists($logo_toko)): ?>
                    <img src="<?= htmlspecialchars($logo_toko) ?>" alt="Logo Toko" class="w-20 h-20 object-contain">
                <?php endif; ?>
                
                <div>
                    <h1 class="text-2xl font-black text-emerald-700 uppercase tracking-tighter"><?= htmlspecialchars($nama_toko) ?></h1>
                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-widest">Divisi Logistik & Gudang</p>
                    <p class="text-xs text-slate-500 mt-2">
                        <?= nl2br(htmlspecialchars($alamat_toko)) ?><br>
                        Telp: <?= htmlspecialchars($telp_toko) ?> | <?= htmlspecialchars($email_toko) ?>
                    </p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-black text-slate-800 uppercase tracking-widest mb-1">TANDA TERIMA BARANG</h2>
                <h3 class="text-2xl font-black text-emerald-600 mb-1">RCV-<?= substr($po['po_no'], 3) ?></h3>
                <p class="text-xs text-slate-500 font-bold mt-1">Diterima: <?= date('d F Y H:i', strtotime($po['updated_at'])) ?></p>
            </div>
        </div>

        <table class="info-table">
            <tr>
                <td style="width: 20%;"><strong>Nomor Referensi PO</strong></td>
                <td style="width: 30%;">: <?= $po['po_no'] ?></td>
                <td style="width: 20%;"><strong>Nama Supplier</strong></td>
                <td style="width: 30%;">: <?= htmlspecialchars($po['supplier_name']) ?></td>
            </tr>
            <tr>
                <td><strong>Dibuat Oleh</strong></td>
                <td>: <?= htmlspecialchars($po['admin_name']) ?></td>
                <td></td>
                <td></td>
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
                    <td>
                        <strong><?= htmlspecialchars($item['material_name']) ?></strong><br>
                        <span style="font-size: 10px; color: #64748b; font-family: monospace;">SKU: <?= htmlspecialchars($item['sku_code']) ?></span>
                    </td>
                    <td style="text-align:center; font-weight:bold; font-size: 14px;"><?= floatval($item['qty']) ?> <span style="font-size: 10px; font-weight: normal;"><?= htmlspecialchars($item['unit']) ?></span></td>
                    <td style="text-align:right;">Rp <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right; font-weight:bold; padding-top: 15px; padding-bottom: 15px;">TOTAL NILAI FAKTUR :</td>
                    <td style="text-align:right; font-weight:bold; font-size:16px; color: #047857; background-color: #ecfdf5;">Rp <?= number_format($po['total_amount'], 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="sign-area">
            <div class="sign-box">
                <p>Dikirim Oleh,</p><br><br>
                <div class="sign-line"></div>
                <p><strong>( ___________________ )</strong><br>Kurir / Supplier</p>
            </div>
            <div class="sign-box">
                <p>Diterima & Dicek Oleh,</p><br><br>
                <div class="sign-line"></div>
                <p><strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin Gudang') ?></strong><br>Checker Gudang</p>
            </div>
            <div class="sign-box">
                <p>Mengetahui,</p><br><br>
                <div class="sign-line"></div>
                <p><strong>( ___________________ )</strong><br>Manager Operasional</p>
            </div>
        </div>
    </div>
    
    <script>
        // Hapus timeout print jika logo perlu loading agak lama
        window.onload = function() { 
            setTimeout(function() { window.print(); }, 1000); 
        }
    </script>
</body>
</html>