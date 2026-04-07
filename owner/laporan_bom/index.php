<?php
require_once '../../config/auth.php';
checkRole(['owner', 'auditor']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        /* CSS Khusus Print / Cetak PDF */
        @media print {
            @page { margin: 1.5cm; size: portrait; }
            html, body, .flex-1, main { height: auto !important; min-height: 100% !important; overflow: visible !important; display: block !important; position: static !important; margin: 0 !important; padding: 0 !important; background-color: white !important; }
            aside, header, nav, #normal-content, .no-print, .swal2-container { display: none !important; }
            #print-container { display: block !important; width: 100% !important; }
            
            #print-header { text-align: center; margin-bottom: 30px; }
            .print-product-title { font-size: 16px; font-weight: bold; background-color: #f1f5f9; padding: 8px 12px; border: 1px solid #cbd5e1; margin-top: 15px; }
            
            table { border-collapse: collapse !important; width: 100% !important; margin-bottom: 20px !important; }
            th, td { border: 1px solid #cbd5e1 !important; padding: 8px 10px !important; color: #000 !important; font-size: 12px !important; }
            th { font-weight: bold !important; text-transform: uppercase; text-align: left; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
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
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Laporan Resep (BOM)</h2>
                        <p class="text-sm text-secondary mt-1">Daftar lengkap komposisi bahan baku untuk setiap produk (Per 1 Pcs).</p>
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

                <div id="form-filter-section" class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                    <form id="formFilter" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-wider">Cari Produk / Bahan Baku</label>
                            <input type="text" id="search" name="search" placeholder="Cari kue atau bahan (Misal: Coklat)..." class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none text-sm font-medium">
                        </div>
                        <div class="w-full md:w-auto flex gap-2">
                            <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                                <i class="fa-solid fa-magnifying-glass"></i> Cari
                            </button>
                            <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2.5 rounded-xl font-bold transition-all">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-bold w-16 text-center">No</th>
                                    <th class="p-4 font-bold">Produk (Hasil Jadi)</th>
                                    <th class="p-4 font-bold text-center">Total Komposisi</th>
                                    <th class="p-4 font-bold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-laporan" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="4" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="print-container" class="hidden">
                <div id="print-header">
                    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px; color:#0f172a;">LAPORAN RESEP (BILL OF MATERIAL)</h1>
                    <p id="print-periode" style="font-size: 14px; color: #64748b;"></p>
                    <hr style="border: 2px solid #e2e8f0; margin-top: 15px; margin-bottom: 20px;">
                </div>
                <div id="print-content-wrapper"></div>
            </div>

        </main>
    </div>

    <div id="modal-detail" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('modal-detail').classList.add('hidden')"></div>
        <div class="relative bg-surface w-full max-w-lg rounded-3xl shadow-2xl z-[110] transform transition-all flex flex-col overflow-hidden max-h-[90vh]">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
            
            <div class="p-6 sm:p-8 flex flex-col h-full">
                <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800" id="modal-title-produk">Nama Produk</h3>
                        <p class="text-xs text-secondary mt-1">Rincian resep untuk 1 Pcs Produk.</p>
                    </div>
                    <button onclick="document.getElementById('modal-detail').classList.add('hidden')" class="w-8 h-8 bg-slate-100 text-slate-500 hover:bg-danger hover:text-white rounded-full flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar mb-4 bg-slate-50 rounded-xl border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-100 sticky top-0">
                            <tr>
                                <th class="p-3 text-slate-600 font-bold text-xs uppercase w-12 text-center">No</th>
                                <th class="p-3 text-slate-600 font-bold text-xs uppercase">Bahan Baku</th>
                                <th class="p-3 text-slate-600 font-bold text-xs uppercase text-right">Takaran</th>
                            </tr>
                        </thead>
                        <tbody id="modal-list-bahan" class="divide-y divide-slate-200 bg-white">
                            </tbody>
                    </table>
                </div>

                <div class="mt-auto pt-2">
                    <button onclick="document.getElementById('modal-detail').classList.add('hidden')" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-3 rounded-xl font-bold transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>