<?php
require_once '../../config/auth.php';
checkRole(['admin']); // Khusus Admin Store
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* CSS KHUSUS PRINT - SUPER CLEAN & FIX BLANK SPACE iOS */
        @media print {
            @page { margin: 0; size: portrait; }
            html, body, .flex-1, main { 
                height: auto !important; min-height: 100% !important; 
                overflow: visible !important; display: block !important; 
                position: static !important; margin: 0 !important; 
                padding: 0 !important; background-color: white !important;
            }
            aside, header, nav, #normal-content, .no-print, .swal2-container { 
                display: none !important; 
            }
            #print-container { 
                display: block !important; width: 100% !important; 
                margin: 0 !important; padding: 1.5cm !important; 
                box-sizing: border-box !important;
            }
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            th, td { border: 1px solid #94a3b8 !important; padding: 8px 10px !important; color: #000 !important; font-size: 12px !important; }
            th { background-color: #f1f5f9 !important; font-weight: bold !important; text-transform: uppercase; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            #print-header { text-align: center; margin-bottom: 20px; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="no-print">
            <?php include '../../components/header.php'; ?>
        </header>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full relative">
            
            <div id="normal-content">
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Antrean Validasi (Pending)</h2>
                        <p class="text-sm text-secondary mt-1">Daftar produksi yang masih berada di Dapur dan belum di-scan masuk ke Store.</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="cetakPDF()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak Daftar Jemput
                        </button>
                    </div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                    
                    <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50">
                        <form id="formFilter" class="flex flex-col sm:flex-row gap-4 items-end flex-wrap">
                            <div class="w-full sm:flex-1 min-w-[140px]">
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Mulai Tanggal</label>
                                <input type="date" id="start_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all text-sm bg-white">
                            </div>
                            <div class="w-full sm:flex-1 min-w-[140px]">
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Sampai Tanggal</label>
                                <input type="date" id="end_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all text-sm bg-white">
                            </div>
                            
                            <div class="w-full sm:flex-1 min-w-[140px]">
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Asal Dapur</label>
                                <select id="kitchen_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all text-sm bg-white">
                                    <option value="">Semua Dapur</option>
                                </select>
                            </div>

                            <div class="w-full sm:flex-1 min-w-[140px]">
                                <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Store Tujuan</label>
                                <select id="warehouse_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all text-sm bg-white">
                                    <option value="">Semua Store</option>
                                </select>
                            </div>

                            <div class="w-full sm:w-auto flex gap-2 mt-4 sm:mt-0">
                                <button type="submit" class="flex-1 sm:flex-none bg-amber-500 hover:bg-amber-600 text-white px-6 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fa-solid fa-filter"></i> Filter
                                </button>
                                <button type="button" onclick="resetFilter()" class="bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-4 py-2.5 rounded-xl font-bold transition-all shadow-sm">
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="p-4 bg-amber-50 border-b border-amber-100 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-amber-800">
                            <i class="fa-solid fa-triangle-exclamation animate-pulse"></i>
                            <h3 class="font-bold">Menunggu Validasi Fisik</h3>
                        </div>
                        <span id="badge-count" class="bg-amber-600 text-white px-2 py-1 rounded-lg text-xs font-bold shadow-sm">0 Item</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1000px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-bold w-12 text-center">No</th>
                                    <th class="p-4 font-bold">Waktu Produksi</th>
                                    <th class="p-4 font-bold">Store Tujuan</th>
                                    <th class="p-4 font-bold">No. Invoice</th>
                                    <th class="p-4 font-bold">Asal Dapur</th>
                                    <th class="p-4 font-bold">Karyawan</th>
                                    <th class="p-4 font-bold">Nama Produk</th>
                                    <th class="p-4 font-bold text-center">Qty (Pcs)</th>
                                </tr>
                            </thead>
                            <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="8" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50 no-print"></div>
                </div>
            </div>

            <div id="print-container" class="hidden">
                <div id="print-header">
                    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; color: #0f172a;">DAFTAR JEMPUT BARANG (PENDING)</h1>
                    <p id="print-periode" style="font-size: 14px; color: #64748b;"></p>
                    <hr style="border: 2px solid #e2e8f0; margin-top: 15px; margin-bottom: 20px;">
                </div>
                <div id="print-table-wrapper"></div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>