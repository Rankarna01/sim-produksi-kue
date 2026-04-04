<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        @media print {
            @page { margin: 1cm; size: landscape; }
            body { background-color: white !important; }
            #main-sidebar, header, #form-filter-section, .no-print, #pagination, .tab-buttons { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; overflow: visible !important; }
            .bg-surface { border: none !important; box-shadow: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; }
            th, td { border: 1px solid #e2e8f0 !important; padding: 8px !important; color: #000 !important; }
            #print-header { display: block !important; text-align: center; margin-bottom: 20px; }
            #summary-cards { display: flex !important; gap: 10px !important; margin-bottom: 20px !important; }
            .summary-card { flex: 1 !important; border: 1px solid #000 !important; padding: 10px !important; border-radius: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full">
            
            <div id="print-header" class="hidden">
                <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">LAPORAN PRODUKSI KUE</h1>
                <h2 style="font-size: 18px; margin-bottom: 5px;" id="print-tab-name">Detail Histori Produksi</h2>
                <p id="print-periode" style="font-size: 14px; color: #666;"></p>
                <hr style="border: 1px solid #000; margin-top: 15px;">
            </div>

            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Produksi</h2>
                    <p class="text-sm text-secondary mt-1">Pantau, cetak (PDF), dan ekspor (Excel) data produksi beserta total kalkulasinya.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Cetak Tab Aktif
                    </button>
                </div>
            </div>

            <div id="form-filter-section" class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col md:flex-row gap-4 items-end flex-wrap">
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Pilih Periode</label>
                        <select id="quick_filter" onchange="applyQuickFilter()" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-semibold">
                            <option value="custom">Kustom Tanggal</option>
                            <option value="today">Hari Ini</option>
                            <option value="this_week">Minggu Ini</option>
                            <option value="this_month" selected>Bulan Ini</option>
                            <option value="this_year">Tahun Ini</option>
                        </select>
                    </div>

                    <div class="w-full md:w-36">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                    </div>
                    <div class="w-full md:w-36">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                    </div>
                    
                    <div class="w-full md:w-48">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Status Produk</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending (Antrean)</option>
                            <option value="ditolak">Ditolak (Butuh Revisi)</option>
                            <option value="masuk_gudang">Selesai (Masuk Gudang)</option>
                            <option value="expired">Expired / Rusak</option>
                        </select>
                    </div>

                    <div class="w-full md:w-48">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Lokasi Gudang</label>
                        <select id="warehouse_filter" name="warehouse_filter" class="w-full px-3 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm">
                            <option value="">Semua Gudang</option>
                        </select>
                    </div>

                    <div class="w-full md:w-auto flex gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-5 py-2 rounded-xl font-bold transition-all shadow-sm">
                            Terapkan
                        </button>
                    </div>
                </form>
            </div>

            <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="summary-card bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                    <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="z-10">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Diproduksi</p>
                        <h3 class="text-2xl font-black text-slate-800" id="sum-total">0 <span class="text-sm font-semibold text-slate-500">Pcs</span></h3>
                    </div>
                </div>
                <div class="summary-card bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                    <div class="w-14 h-14 rounded-full bg-success/10 text-success flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-check-double"></i></div>
                    <div class="z-10">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sukses (Masuk Gudang)</p>
                        <h3 class="text-2xl font-black text-success" id="sum-masuk">0 <span class="text-sm font-semibold text-success/70">Pcs</span></h3>
                    </div>
                </div>
                <div class="summary-card bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                    <div class="w-14 h-14 rounded-full bg-danger/10 text-danger flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="z-10">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ditolak / Expired</p>
                        <h3 class="text-2xl font-black text-danger" id="sum-gagal">0 <span class="text-sm font-semibold text-danger/70">Pcs</span></h3>
                    </div>
                </div>
            </div>

            <div class="flex border-b border-slate-200 mb-6 gap-6 tab-buttons no-print overflow-x-auto whitespace-nowrap">
                <button onclick="switchTab('tab-detail')" id="btn-tab-detail" class="tab-btn pb-3 text-sm font-bold border-b-2 border-primary text-primary transition-colors">
                    <i class="fa-solid fa-list mr-1"></i> Detail Histori Produksi
                </button>
                <button onclick="switchTab('tab-rekap-produk')" id="btn-tab-rekap-produk" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-chart-simple mr-1"></i> Rekap Per Produk
                </button>
                <button onclick="switchTab('tab-rekap-karyawan')" id="btn-tab-rekap-karyawan" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-user-check mr-1"></i> Kinerja Karyawan
                </button>
            </div>

            <div id="tab-detail" class="tab-content bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider print:bg-slate-100">
                                <th class="p-3 font-bold w-12 text-center">No</th>
                                <th class="p-3 font-bold">Waktu Produksi</th>
                                <th class="p-3 font-bold">No. Invoice</th>
                                <th class="p-3 font-bold">Karyawan (Dapur)</th>
                                <th class="p-3 font-bold">Produk</th>
                                <th class="p-3 font-bold text-right">Qty (Pcs)</th>
                                <th class="p-3 font-bold text-center">Status</th>
                                <th class="p-3 font-bold">Gudang</th>
                            </tr>
                        </thead>
                        <tbody id="table-laporan" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="8" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50 no-print"></div>
            </div>

            <div id="tab-rekap-produk" class="tab-content hidden bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider print:bg-slate-100">
                                <th class="p-4 font-bold w-12 text-center">No</th>
                                <th class="p-4 font-bold">Nama Produk Varian</th>
                                <th class="p-4 font-bold text-right">Total Diproduksi (Pcs)</th>
                            </tr>
                        </thead>
                        <tbody id="table-rekap" class="text-sm divide-y divide-slate-100">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="tab-rekap-karyawan" class="tab-content hidden bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-none flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-indigo-50 border-b border-indigo-100 text-xs text-indigo-700 uppercase tracking-wider print:bg-slate-100">
                                <th class="p-4 font-bold w-12 text-center">No</th>
                                <th class="p-4 font-bold">Nama Karyawan</th>
                                <th class="p-4 font-bold">Roti/Kue Yang Dibuat</th>
                                <th class="p-4 font-bold text-right">Total (Pcs)</th>
                            </tr>
                        </thead>
                        <tbody id="table-rekap-karyawan" class="text-sm divide-y divide-slate-100">
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