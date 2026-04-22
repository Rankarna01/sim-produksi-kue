<?php
require_once '../../../config/auth.php';
checkPermission('monitoring_rak');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" 
              x-data="{ view: 'list', rackId: null, rackName: '' }"
              @open-detail.window="rackId = $event.detail.id; rackName = $event.detail.name; view = 'detail';">
            
            <div x-show="view === 'list'" x-transition.opacity class="space-y-6">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fa-solid fa-server text-blue-600"></i> Monitoring Rak
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Scan atau ketik lokasi rak untuk melihat rincian daftar barang di dalamnya.</p>
                </div>

                <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-200">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 text-center">Lokasi Rak / Barcode Rak</label>
                    <div class="relative max-w-2xl mx-auto">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-barcode text-slate-400 text-lg"></i>
                        </div>
                        <input type="text" id="search-rak" placeholder="CONTOH: RAK-A-01 atau A-01" class="w-full pl-12 pr-4 py-4 border-2 border-slate-300 rounded-2xl focus:border-blue-600 outline-none transition-all text-lg font-black text-slate-700 text-center uppercase shadow-inner" onkeyup="cariRak()" onkeypress="scanBarcode(event)" autofocus>
                        <button onclick="document.getElementById('search-rak').value=''; cariRak();" class="absolute inset-y-0 right-0 pr-4 flex items-center text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors">
                            Clear
                        </button>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 text-center mt-3 tracking-widest">Arahkan scanner ke barcode rak, sistem akan otomatis membuka detailnya.</p>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-black text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-cubes text-slate-400"></i> Daftar Rak Tersedia (<span id="total-rak">0</span>)
                        </h3>
                        <a href="print_ringkasan.php" target="_blank" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-print"></i> Cetak Ringkasan
                        </a>
                    </div>
                    <div id="grid-rak" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="col-span-full p-10 text-center text-slate-400"><i class="fa-solid fa-circle-notch fa-spin text-2xl"></i> Memuat data rak...</div>
                    </div>
                </div>
            </div>

            <div x-show="view === 'detail'" x-cloak x-transition.opacity class="space-y-6">
                <button @click="view = 'list'; loadDataRak(); document.getElementById('search-rak').focus();" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2 mb-4 w-max">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Rak
                </button>

                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow-sm">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 text-lg flex items-center gap-3">
                                    Barang di Rak: <span x-text="rackName" class="text-blue-600"></span>
                                    <a :href="'print_rak.php?id=' + rackId" target="_blank" class="bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all flex items-center shadow-sm">
                                        <i class="fa-solid fa-print mr-1"></i> Cetak Detail Rak
                                    </a>
                                </h3>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <div class="bg-blue-100 text-blue-600 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest whitespace-nowrap">
                                Total Barang: <span id="detail-total-item">0</span> | Total Stok: <span id="detail-total-stok">0</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[700px]">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-white">
                                    <th class="p-5 w-16 text-center">No</th>
                                    <th class="p-5">Barcode</th>
                                    <th class="p-5">Nama Barang</th>
                                    <th class="p-5 text-center">Kategori</th>
                                    <th class="p-5 text-center">Stok</th>
                                    <th class="p-5 text-center">Satuan</th>
                                    <th class="p-5 text-center">Exp. Date</th>
                                </tr>
                            </thead>
                            <tbody id="detail-barang" class="text-sm divide-y divide-slate-50 font-bold text-slate-700">
                                <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>