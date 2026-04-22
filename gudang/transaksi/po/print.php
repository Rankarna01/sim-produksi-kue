<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

$po_id = $_GET['id'] ?? '';
if (empty($po_id)) die("ID Purchase Order tidak valid.");

// 1. AMBIL DATA PROFIL TOKO DARI DATABASE
$stmtToko = $pdo->query("SELECT * FROM store_profile WHERE id = 1");
$toko = $stmtToko->fetch(PDO::FETCH_ASSOC);

// Fallback jika tabel profil toko kosong (belum disetting)
$nama_toko = $toko['store_name'] ?? 'PERUSAHAAN KAMI';
$alamat_toko = $toko['address'] ?? 'Alamat Belum Disetting';
$telp_toko = $toko['phone'] ?? '-';
$email_toko = $toko['email'] ?? '-';
$logo_toko = !empty($toko['logo_path']) ? '../../../' . $toko['logo_path'] : null;

// 2. AMBIL DATA PO & SUPPLIER
$sqlHeader = "SELECT p.*, s.name as supplier_name, s.address, s.phone, s.contact_person, u.name as admin_name 
              FROM purchase_orders p 
              JOIN suppliers s ON p.supplier_id = s.id 
              LEFT JOIN users u ON p.created_by = u.id 
              WHERE p.id = ?";
$stmtHeader = $pdo->prepare($sqlHeader);
$stmtHeader->execute([$po_id]);
$po = $stmtHeader->fetch(PDO::FETCH_ASSOC);

if (!$po) die("Data Purchase Order tidak ditemukan.");

// 3. AMBIL DETAIL ITEM BARANG
$sqlDetail = "SELECT pod.*, ms.material_name, ms.sku_code, ms.unit 
              FROM purchase_order_details pod 
              JOIN materials_stocks ms ON pod.material_id = ms.id 
              WHERE pod.po_id = ?";
$stmtDetail = $pdo->prepare($sqlDetail);
$stmtDetail->execute([$po_id]);
$items = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print PO - <?= $po['po_no'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page { size: A4; margin: 0; }
        body { background-color: #f1f5f9; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .a4-container { width: 210mm; min-height: 297mm; background: white; margin: 20px auto; padding: 20mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        @media print { body { background-color: white; } .a4-container { margin: 0; box-shadow: none; border: none; padding: 15mm; } .no-print { display: none; } }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        th { font-weight: 800; text-transform: uppercase; font-size: 10px; color: #64748b; text-align: left; }
    </style>
</head>
<body class="text-slate-800">

    <div class="text-center mt-6 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700">Cetak Dokumen</button>
        <button onclick="window.close()" class="bg-slate-200 text-slate-700 px-6 py-2 rounded-lg font-bold shadow-sm hover:bg-slate-300 ml-2">Tutup</button>
    </div>

    <div class="a4-container">
        
        <div class="flex justify-between items-start border-b-4 border-slate-800 pb-4 mb-8">
            <div class="flex items-center gap-4">
                <?php if ($logo_toko && file_exists($logo_toko)): ?>
                    <img src="<?= htmlspecialchars($logo_toko) ?>" alt="Logo Toko" class="w-20 h-20 object-contain">
                <?php endif; ?>
                
                <div>
                    <h1 class="text-3xl font-black text-blue-700 uppercase tracking-tighter"><?= htmlspecialchars($nama_toko) ?></h1>
                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-widest">Divisi Logistik & Gudang</p>
                    <p class="text-xs text-slate-500 mt-2">
                        <?= nl2br(htmlspecialchars($alamat_toko)) ?><br>
                        Telp: <?= htmlspecialchars($telp_toko) ?> | <?= htmlspecialchars($email_toko) ?>
                    </p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black text-slate-800 uppercase tracking-widest mb-1">PURCHASE ORDER</h2>
                <p class="text-sm font-bold text-slate-600">Nomor: <span class="text-blue-600"><?= $po['po_no'] ?></span></p>
                <p class="text-xs text-slate-500 font-bold mt-1">Tanggal: <?= date('d/m/Y', strtotime($po['created_at'])) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <h3 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Kepada (Supplier):</h3>
                <p class="font-black text-base text-slate-800 uppercase"><?= htmlspecialchars($po['supplier_name']) ?></p>
                <p class="text-xs text-slate-600 mt-1 font-bold">UP: <?= htmlspecialchars($po['contact_person'] ?: '-') ?></p>
                <p class="text-xs text-slate-600 mt-1">Telp: <?= htmlspecialchars($po['phone'] ?: '-') ?></p>
                <p class="text-xs text-slate-600 mt-1"><?= nl2br(htmlspecialchars($po['address'] ?: '-')) ?></p>
            </div>
            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex flex-col justify-center">
                <h3 class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-2">Informasi Pengiriman:</h3>
                <div class="text-xs space-y-2">
                    <div class="flex"><span class="w-32 font-bold text-slate-600">Tgl. Diminta</span><span class="font-black text-slate-800">: <?= date('d M Y', strtotime($po['shipping_date'])) ?></span></div>
                    <div class="flex"><span class="w-32 font-bold text-slate-600">Dibuat Oleh</span><span class="font-black text-slate-800">: <?= htmlspecialchars($po['admin_name']) ?></span></div>
                    <div class="flex">
                        <span class="w-32 font-bold text-slate-600">Status</span>
                        <span class="font-black text-slate-800 uppercase">: <?= str_replace('_', ' ', $po['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-[300px]">
            <table>
                <thead class="bg-slate-50 border-y border-slate-200">
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 45%;">Deskripsi Barang</th>
                        <th class="text-center" style="width: 15%;">Qty</th>
                        <th class="text-center" style="width: 20%;">Satuan</th>
                    </tr>
                </thead>
                <tbody class="font-medium text-slate-700">
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td class="text-center text-slate-400 font-bold"><?= $index + 1 ?></td>
                            <td class="font-mono text-[10px] text-slate-400 font-bold"><?= $item['sku_code'] ?></td>
                            <td class="font-black text-slate-800 uppercase text-xs"><?= htmlspecialchars($item['material_name']) ?></td>
                            <td class="text-center font-black text-base text-blue-700"><?= floatval($item['qty']) ?></td>
                            <td class="text-center font-bold text-slate-500 uppercase text-[10px]"><?= htmlspecialchars($item['unit']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-end mt-12 pt-8">
            <div class="text-center w-48">
                <p class="text-xs font-bold text-slate-600 mb-16">Dibuat Oleh,</p>
                <div class="border-b border-slate-800 mb-1"></div>
                <p class="text-xs font-bold text-slate-800">( <?= htmlspecialchars($po['admin_name']) ?> )</p>
                <p class="text-[9px] text-slate-500 uppercase">Admin Gudang</p>
            </div>
            <div class="text-center w-48">
                <p class="text-xs font-bold text-slate-600 mb-16">Disetujui Oleh,</p>
                <div class="border-b border-slate-800 mb-1"></div>
                <p class="text-xs font-bold text-slate-800">( ...................................... )</p>
                <p class="text-[9px] text-slate-500 uppercase">Manager / Owner</p>
            </div>
            <div class="text-center w-48">
                <p class="text-xs font-bold text-slate-600 mb-16">Supplier,</p>
                <div class="border-b border-slate-800 mb-1"></div>
                <p class="text-xs font-bold text-slate-800">( ...................................... )</p>
                <p class="text-[9px] text-slate-500 uppercase">Tanda Tangan & Cap</p>
            </div>
        </div>
        
        <div class="mt-12 text-[10px] text-slate-400 border-t border-slate-200 pt-4">
            <p class="font-bold text-slate-600">Catatan Penting:</p>
            <ol class="list-decimal pl-4 mt-1">
                <li>Harap kirimkan barang sesuai dengan tanggal pengiriman yang tertera.</li>
                <li>Barang yang tidak sesuai spesifikasi atau dalam kondisi cacat akan dikembalikan (Retur).</li>
                <li>Lampirkan salinan Purchase Order ini saat penagihan (Invoice).</li>
            </ol>
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