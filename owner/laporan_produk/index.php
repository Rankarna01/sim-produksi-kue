<?php
require_once '../../config/auth.php';
checkPermission('laporan_produk_jadi');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* CSS Khusus Print / Cetak PDF */
        @media print {
            @page { margin: 1cm; size: portrait; }
            body { background-color: white !important; }
            #main-sidebar, header, #form-filter-section, .no-print { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; overflow: visible !important; }
            .bg-surface { border: none !important; box-shadow: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { border: 1px solid #e2e8f0 !important; padding: 10px !important; color: #000 !important; }
            #print-header { display: block !important; text-align: center; margin-bottom: 20px; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full">
            
            <div id="print-header" class="hidden">
                <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">LAPORAN DATA PRODUK JADI</h1>
                <p id="print-periode" style="font-size: 14px; color: #666;"></p>
                <hr style="border: 1px solid #000; margin-top: 15px;">
            </div>

            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Produk Jadi</h2>
                    <p class="text-sm text-secondary mt-1">Daftar master data kue/roti beserta kategorinya.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Cetak PDF
                    </button>
                </div>
            </div>

            <div id="form-filter-section" class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col md:flex-row gap-4 items-end">
                    
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Cari Produk / Kode</label>
                        <input type="text" id="search" name="search" placeholder="Misal: Roti Coklat..." class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                    </div>
                    
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Kategori</label>
                        <select id="category" name="category" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                            <option value="">Semua Kategori</option>
                            <option value="Roti Manis">Roti Manis</option>
                            <option value="Roti Tawar">Roti Tawar</option>
                            <option value="Kue Kering">Kue Kering</option>
                            <option value="Kue Basah">Kue Basah</option>
                            <option value="Cake">Cake</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto flex gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-bold transition-all shadow-sm">
                            Terapkan
                        </button>
                        <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-xl font-bold transition-all">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider print:bg-slate-100">
                                <th class="p-4 font-bold w-12 text-center">No</th>
                                <th class="p-4 font-bold">Kode Produk</th>
                                <th class="p-4 font-bold">Nama Produk</th>
                                <th class="p-4 font-bold">Kategori</th>
                            </tr>
                        </thead>
                        <tbody id="table-laporan" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="4" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>