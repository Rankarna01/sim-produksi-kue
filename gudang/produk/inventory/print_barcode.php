<?php
require_once '../../../config/auth.php';
require_once '../../../config/database.php';

// Cek apakah user punya akses ke menu inventory
checkPermission('master_inventory');

$id = $_GET['id'] ?? 0;
$qty = (int)($_GET['qty'] ?? 1);

// Ambil data barang berdasarkan ID
$stmt = $pdo->prepare("SELECT sku_code, material_name FROM materials_stocks WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Data barang tidak ditemukan.");
}

// Batasi maksimal cetak sekaligus agar browser tidak lag (misal max 100)
if ($qty > 100) $qty = 100;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - <?= htmlspecialchars($item['sku_code']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #e2e8f0; /* Warna bg di luar kertas cetak */
        }
        .page-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            justify-content: center;
        }
        .label-box {
            /* Ukuran standar stiker barcode thermal (50mm x 30mm) */
            width: 50mm;
            height: 30mm;
            background: #fff;
            border: 1px dashed #94a3b8;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2mm;
            page-break-inside: avoid; /* Jangan potong stiker di tengah-tengah halaman */
        }
        .item-name {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            margin-bottom: 2px;
        }
        .barcode-svg {
            max-width: 100%;
            height: 18mm; /* Tinggi garis barcode */
        }
        
        /* PENGATURAN KHUSUS PRINTER THERMAL */
        @media print {
            body { background: #fff; }
            .page-container { gap: 0; padding: 0; display: block; }
            .label-box { 
                border: none; /* Hilangkan garis putus-putus saat dicetak */
                margin: 0; 
                page-break-after: always; /* 1 Stiker = 1 Lembar Thermal */
            }
            @page { 
                size: 50mm 30mm; /* Sesuai ukuran stiker hardware printer */
                margin: 0; 
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    
    <div class="no-print" style="text-align: center; padding: 15px; background: #fff; border-bottom: 2px solid #cbd5e1; margin-bottom: 10px;">
        <p style="margin: 0 0 10px 0; font-size: 14px;">Mempersiapkan <b><?= $qty ?> label</b>. Pastikan printer thermal Anda sudah terhubung.</p>
        <button onclick="window.print()" style="padding: 10px 25px; cursor: pointer; background: #2563eb; color: #fff; font-weight: bold; border: none; border-radius: 8px;">Cetak Sekarang</button>
        <button onclick="window.close()" style="padding: 10px 25px; cursor: pointer; background: #f1f5f9; color: #475569; font-weight: bold; border: 1px solid #cbd5e1; border-radius: 8px; margin-left: 10px;">Tutup</button>
    </div>

    <div class="page-container">
        <?php for ($i = 0; $i < $qty; $i++): ?>
            <div class="label-box">
                <div class="item-name"><?= strtoupper(htmlspecialchars($item['material_name'])) ?></div>
                <svg class="barcode-svg" id="barcode-<?= $i ?>"></svg>
            </div>
        <?php endfor; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sku = "<?= $item['sku_code'] ?>";
            const qty = <?= $qty ?>;
            
            // Loop untuk menggambar barcode di masing-masing tag SVG
            for (let i = 0; i < qty; i++) {
                JsBarcode("#barcode-" + i, sku, {
                    format: "CODE128",
                    displayValue: true,    // Tampilkan kode SKU di bawah garis
                    fontSize: 12,          // Ukuran font teks SKU
                    fontOptions: "bold",
                    margin: 2,             // Jarak aman luar barcode
                    width: 1.5,            // Ketebalan garis
                    height: 40             // Tinggi garis
                });
            }

            // Otomatis buka dialog print setelah 500ms agar JSBarcode selesai render
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>