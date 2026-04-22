<?php
require_once '../../../config/auth.php';
checkPermission('lap_stok_menipis');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Stok Menipis</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau barang yang hampir habis untuk segera dilakukan pengadaan (Restock).</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center bg-white border border-slate-300 rounded-lg px-3 py-2 shadow-sm">
                        <span class="text-xs font-bold text-slate-500 mr-2">Tampilkan stok di bawah:</span>
                        <input type="number" id="threshold" value="10" min="0" onchange="loadData()" class="w-16 border-none outline-none text-sm font-black text-rose-600 bg-transparent text-center">
                    </div>

                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="search" placeholder="Cari barang / SKU..." onkeyup="cariData()" class="pl-8 pr-3 py-2 border border-slate-300 rounded-lg outline-none text-sm w-48 focus:border-rose-600 transition-all bg-white shadow-sm">
                    </div>

                    <button onclick="exportData('pdf')" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fa-regular fa-file-pdf"></i> Export PDF
                    </button>
                    <button onclick="exportData('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fa-regular fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-xs font-black text-slate-600 uppercase tracking-widest">
                                <th class="p-5">SKU</th>
                                <th class="p-5">Nama Barang</th>
                                <th class="p-5">Kategori & Rak</th>
                                <th class="p-5 text-center">Sisa Stok</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination" class="mt-6 flex items-center justify-center gap-2"></div>

        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>