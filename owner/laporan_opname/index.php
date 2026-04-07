<?php
require_once '../../config/auth.php';
checkRole(['owner', 'auditor']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        @media print {
            @page { margin: 1cm; size: landscape; }
            body { background-color: white !important; }
            #main-sidebar, header, #form-filter-section, .no-print, #pagination { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; overflow: visible !important; }
            .bg-surface { border: none !important; box-shadow: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; }
            th, td { border: 1px solid #e2e8f0 !important; padding: 8px !important; color: #000 !important; }
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
                <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">LAPORAN STOK OPNAME BAHAN BAKU</h1>
                <p id="print-periode" style="font-size: 14px; color: #666;"></p>
                <hr style="border: 1px solid #000; margin-top: 15px;">
            </div>

            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Stok Opname</h2>
                    <p class="text-sm text-secondary mt-1">Pantau dan cetak data histori penyesuaian stok bahan baku dapur.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <div id="form-filter-section" class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col md:flex-row gap-4 items-end flex-wrap">
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Pilih Periode</label>
                        <select id="quick_filter" onchange="applyQuickFilter()" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-emerald-500 outline-none text-sm font-semibold">
                            <option value="custom">Kustom Tanggal</option>
                            <option value="today">Hari Ini</option>
                            <option value="this_week">Minggu Ini</option>
                            <option value="this_month" selected>Bulan Ini</option>
                            <option value="this_year">Tahun Ini</option>
                        </select>
                    </div>
                    <div class="w-full md:w-36">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-emerald-500 outline-none text-sm">
                    </div>
                    <div class="w-full md:w-36">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-emerald-500 outline-none text-sm">
                    </div>
                    <div class="w-full md:w-auto flex gap-2">
                        <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-xl font-bold transition-all shadow-sm">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-emerald-50 border-b border-emerald-100 text-xs text-emerald-800 uppercase tracking-wider print:bg-slate-100">
                                <th class="p-3 font-bold w-12 text-center">No</th>
                                <th class="p-3 font-bold">Waktu Dokumen</th>
                                <th class="p-3 font-bold">No. Dokumen</th>
                                <th class="p-3 font-bold">Bahan Baku</th>
                                <th class="p-3 font-bold text-right">Stok Sistem</th>
                                <th class="p-3 font-bold text-right">Stok Fisik</th>
                                <th class="p-3 font-bold text-center">Selisih</th>
                                <th class="p-3 font-bold">Catatan</th>
                                <th class="p-3 font-bold text-center">Petugas</th>
                            </tr>
                        </thead>
                        <tbody id="table-laporan" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="9" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50 no-print"></div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>