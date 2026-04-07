<?php
require_once '../../config/auth.php';
// Kode ini bisa dipakai di role admin maupun produksi
checkRole(['admin', 'produksi', 'owner', 'auditor']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* CSS Khusus Print / Cetak PDF */
        @media print {
            @page { margin: 1cm; size: landscape; }
            body { background-color: white !important; }
            #main-sidebar, header, .no-print, .swal2-container { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; overflow: visible !important; }
            #print-container { display: block !important; width: 100% !important; }
            #normal-content { display: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; }
            th, td { border: 1px solid #cbd5e1 !important; padding: 8px !important; color: #000 !important; font-size: 12px !important; }
            th { font-weight: bold !important; text-transform: uppercase; background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
            .print-header-title { text-align: center; margin-bottom: 20px; }
        }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php 
        if($_SESSION['role'] == 'admin') include '../../components/sidebar_admin.php'; 
        else if($_SESSION['role'] == 'produksi') include '../../components/sidebar_produksi.php';
        else include '../../components/sidebar.php'; 
    ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="no-print">
            <?php include '../../components/header.php'; ?>
        </header>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full relative">
            
            <div id="normal-content">
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Riwayat & Status Produksi</h2>
                        <p class="text-sm text-secondary mt-1">Pantau seluruh status antrean, validasi, revisi, hingga data rusak.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="exportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i> Export Excel
                        </button>
                        <button onclick="cetakPDF()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak PDF
                        </button>
                    </div>
                </div>

                <div class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                    <form id="formFilter" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Tanggal Mulai</label>
                                <input type="date" id="start_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Tanggal Akhir</label>
                                <input type="date" id="end_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm bg-white">
                            </div>
                        </div>
                        <div class="w-full md:w-auto flex gap-2">
                            <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all shadow-sm">
                                Filter
                            </button>
                            <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl font-bold transition-all">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex border-b border-slate-200 mb-6 gap-2 sm:gap-6 overflow-x-auto custom-scrollbar whitespace-nowrap px-1">
                    <button onclick="switchTab('pending')" id="tab-btn-pending" class="pb-3 text-sm font-bold border-b-2 border-accent text-accent transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-clock"></i> Belum Validasi
                    </button>
                    <button onclick="switchTab('masuk_gudang')" id="tab-btn-masuk_gudang" class="pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Selesai (Riwayat Validasi)
                    </button>
                    <button onclick="switchTab('ditolak')" id="tab-btn-ditolak" class="pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-rotate-left"></i> Ditolak (Perlu Revisi)
                    </button>
                    <button onclick="switchTab('expired')" id="tab-btn-expired" class="pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-ban"></i> Expired / Rusak
                    </button>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-bold w-12 text-center">No</th>
                                    <th class="p-4 font-bold">Waktu</th>
                                    <th class="p-4 font-bold">No. Invoice</th>
                                    <th class="p-4 font-bold">Pembuat (Dapur)</th>
                                    <th class="p-4 font-bold">Nama Produk</th>
                                    <th class="p-4 font-bold text-center">Qty</th>
                                </tr>
                            </thead>
                            <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                                </tbody>
                        </table>
                    </div>
                    <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50"></div>
                </div>
            </div>

            <div id="print-container" class="hidden">
                <div class="print-header-title">
                    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">LAPORAN DATA PRODUKSI</h1>
                    <h2 id="print-subtitle" style="font-size: 16px; margin-bottom: 5px; color: #334155;">Status: PENDING</h2>
                    <p id="print-periode" style="font-size: 12px; color: #64748b;"></p>
                    <hr style="border: 1.5px solid #000; margin-top: 15px; margin-bottom: 20px;">
                </div>
                <div id="print-table-wrapper"></div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>