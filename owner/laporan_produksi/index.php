<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* =========================================================
           CSS KHUSUS PRINT - SUPER CLEAN & FIX BLANK SPACE
           ========================================================= */
        @media print {
            /* 1. HILANGKAN HEADER/FOOTER BAWAAN BROWSER (Tgl, URL) */
            @page { margin: 0; size: landscape; }
            
            /* 2. RESET SEMUA ELEMEN WRAPPER & PADDING BAWAAN WEB */
            html, body, .flex-1, main { 
                height: auto !important; 
                min-height: 100% !important; 
                overflow: visible !important; 
                display: block !important; 
                position: static !important; 
                margin: 0 !important; 
                padding: 0 !important; /* Kunci perbaikan: Hapus padding dari <main> */
                background-color: white !important;
            }
            
            /* 3. SEMBUNYIKAN TOTAL SEMUA ELEMEN WEB */
            aside, header, nav, #normal-content, .no-print, .swal2-container { 
                display: none !important; 
            }
            
            /* 4. MUNCULKAN CONTAINER PRINT & BERI JARAK KERTAS (PENGGANTI MARGIN) */
            #print-container { 
                display: block !important; 
                width: 100% !important; 
                margin: 0 !important; 
                padding: 1.5cm !important; /* Jarak aman tepi kertas */
                box-sizing: border-box !important;
            }

            /* Kotak Infobox Print */
            #print-summary-cards { display: flex !important; gap: 15px; margin-bottom: 20px; width: 100%; }
            .print-card { flex: 1; border: 2px solid #cbd5e1; padding: 12px; border-radius: 8px; text-align: center; }
            .print-card p { font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; margin: 0; }
            .print-card h3 { font-size: 24px; font-weight: 900; color: #0f172a; margin: 5px 0 0 0; }

            /* Styling Tabel Print yang Elegan */
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td { border: 1px solid #94a3b8 !important; padding: 8px 10px !important; color: #000 !important; font-size: 12px !important; }
            th { background-color: #f1f5f9 !important; font-weight: bold !important; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            #print-header { text-align: center; margin-bottom: 20px; }
            .print-badge { border: 1px solid #000; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <header class="no-print">
            <?php include '../../components/header.php'; ?>
        </header>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full relative">
            
            <div id="normal-content">
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Produksi</h2>
                        <p class="text-sm text-secondary mt-1">Pantau, cetak (PDF), dan ekspor (Excel) data produksi beserta total kalkulasinya.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </button>
                        <button onclick="cetakPDF()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
                        </button>
                    </div>
                </div>

                <div id="form-filter-section" class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                    <form id="formFilter" class="flex flex-col lg:flex-row gap-4 items-end">
                        
                        <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Pilih Cepat</label>
                                <select id="quick_filter" onchange="applyQuickFilter()" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium text-slate-700 bg-white">
                                    <option value="custom">Kustom Tanggal</option>
                                    <option value="today">Hari Ini</option>
                                    <option value="this_week">Minggu Ini</option>
                                    <option value="this_month" selected>Bulan Ini</option>
                                    <option value="this_year">Tahun Ini</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Tanggal Mulai</label>
                                <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium text-slate-700 bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Tanggal Akhir</label>
                                <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium text-slate-700 bg-white">
                            </div>
                        </div>

                        <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 lg:mt-0 lg:w-auto">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Status</label>
                                <select id="status" name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium text-slate-700 bg-white">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending (Antrean)</option>
                                    <option value="ditolak">Ditolak (Revisi)</option>
                                    <option value="masuk_gudang">Selesai (Masuk Gudang)</option>
                                    <option value="expired">Expired / Rusak</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Gudang</label>
                                <select id="warehouse_filter" name="warehouse_filter" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium text-slate-700 bg-white">
                                    <option value="">Semua Gudang</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2 w-full lg:w-auto mt-4 lg:mt-0">
                            <button type="submit" class="flex-1 lg:flex-none bg-primary hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                                <i class="fa-solid fa-filter"></i> Filter
                            </button>
                            <button type="button" onclick="resetFilter()" class="flex-1 lg:flex-none bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                        <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="z-10">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Diproduksi</p>
                            <h3 class="text-2xl font-black text-slate-800" id="sum-total">0 <span class="text-sm font-semibold text-slate-500">Pcs</span></h3>
                        </div>
                    </div>
                    <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                        <div class="w-14 h-14 rounded-full bg-success/10 text-success flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-check-double"></i></div>
                        <div class="z-10">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Sukses (Masuk Gudang)</p>
                            <h3 class="text-2xl font-black text-success" id="sum-masuk">0 <span class="text-sm font-semibold text-success/70">Pcs</span></h3>
                        </div>
                    </div>
                    <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-5 flex items-center gap-4 relative overflow-hidden">
                        <div class="w-14 h-14 rounded-full bg-danger/10 text-danger flex items-center justify-center text-2xl z-10"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="z-10">
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ditolak / Expired</p>
                            <h3 class="text-2xl font-black text-danger" id="sum-gagal">0 <span class="text-sm font-semibold text-danger/70">Pcs</span></h3>
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-slate-200 mb-6 gap-6 tab-buttons overflow-x-auto whitespace-nowrap">
                    <button onclick="switchTab('tab-detail')" id="btn-tab-detail" class="tab-btn pb-3 text-sm font-bold border-b-2 border-primary text-primary transition-colors">
                        <i class="fa-solid fa-list mr-1"></i> Histori Lengkap
                    </button>
                    <button onclick="switchTab('tab-pemakaian-bahan')" id="btn-tab-pemakaian-bahan" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                        <i class="fa-solid fa-wheat-awn mr-1"></i> Rincian Bahan Baku
                    </button>
                    <button onclick="switchTab('tab-rekap-produk')" id="btn-tab-rekap-produk" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                        <i class="fa-solid fa-chart-simple mr-1"></i> Rekap Produk
                    </button>
                    <button onclick="switchTab('tab-rekap-karyawan')" id="btn-tab-rekap-karyawan" class="tab-btn pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                        <i class="fa-solid fa-user-check mr-1"></i> Kinerja Karyawan
                    </button>
                </div>

                <div id="tab-detail" class="tab-content bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                    <th class="p-3 font-bold w-12 text-center">No</th>
                                    <th class="p-3 font-bold">Waktu Produksi</th>
                                    <th class="p-3 font-bold">No. Invoice</th>
                                    <th class="p-3 font-bold">Karyawan (Dapur)</th>
                                    <th class="p-3 font-bold">Produk</th>
                                    <th class="p-3 font-bold text-center">Qty (Pcs)</th>
                                    <th class="p-3 font-bold text-center">Status</th>
                                    <th class="p-3 font-bold">Gudang</th>
                                </tr>
                            </thead>
                            <tbody id="table-laporan" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50"></div>
                </div>

                <div id="tab-pemakaian-bahan" class="tab-content hidden">
                    <div id="bahan-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
                </div>

                <div id="tab-rekap-produk" class="tab-content hidden bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-bold w-12 text-center">No</th>
                                    <th class="p-4 font-bold">Nama Produk Varian</th>
                                    <th class="p-4 font-bold text-center">Total Produksi (Pcs)</th>
                                </tr>
                            </thead>
                            <tbody id="table-rekap" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                <div id="tab-rekap-karyawan" class="tab-content hidden bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-indigo-50 border-b border-indigo-100 text-xs text-indigo-700 uppercase tracking-wider">
                                    <th class="p-4 font-bold w-12 text-center">No</th>
                                    <th class="p-4 font-bold">Nama Karyawan</th>
                                    <th class="p-4 font-bold">Roti/Kue Yang Dibuat</th>
                                    <th class="p-4 font-bold text-center">Total (Pcs)</th>
                                </tr>
                            </thead>
                            <tbody id="table-rekap-karyawan" class="text-sm divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="print-container" class="hidden">
                <div id="print-header">
                    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; color: #0f172a;">LAPORAN PRODUKSI KUE</h1>
                    <h2 style="font-size: 16px; margin-bottom: 5px; color: #334155;" id="print-tab-name">Detail Histori Produksi</h2>
                    <p id="print-periode" style="font-size: 12px; color: #64748b;"></p>
                    <hr style="border: 2px solid #e2e8f0; margin-top: 15px; margin-bottom: 20px;">
                </div>
                
                <div id="print-summary-cards">
                    <div class="print-card">
                        <p>Total Diproduksi</p>
                        <h3 id="print-sum-total">0 Pcs</h3>
                    </div>
                    <div class="print-card" style="border-color: #10B981; color: #10B981;">
                        <p style="color: #10B981;">Sukses (Masuk Gudang)</p>
                        <h3 id="print-sum-masuk" style="color: #10B981;">0 Pcs</h3>
                    </div>
                    <div class="print-card" style="border-color: #EF4444; color: #EF4444;">
                        <p style="color: #EF4444;">Ditolak / Expired</p>
                        <h3 id="print-sum-gagal" style="color: #EF4444;">0 Pcs</h3>
                    </div>
                </div>

                <div id="print-table-wrapper">
                    </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>