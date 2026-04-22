<?php
require_once '../../../config/auth.php';
checkPermission('lap_barang_masuk');
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
                <div class="flex items-center gap-4">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Barang Masuk</h2>
                    <select id="filter_source" onchange="loadData()" class="px-3 py-1.5 border border-slate-300 rounded-lg outline-none text-sm font-bold text-slate-600 bg-white">
                        <option value="semua">Semua Sumber</option>
                        <option value="Manual">Manual</option>
                        <option value="PO">Purchase Order (PO)</option>
                    </select>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="search" placeholder="Cari dalam laporan..." onkeyup="cariData()" class="pl-8 pr-3 py-2 border border-slate-300 rounded-lg outline-none text-sm w-48 focus:border-blue-600">
                    </div>
                    
                    <div class="flex bg-white border border-slate-300 rounded-lg overflow-hidden text-sm font-bold text-slate-600">
                        <button onclick="setFilterDate('harian')" id="btn-harian" class="px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300">Harian</button>
                        <button onclick="setFilterDate('periode')" id="btn-periode" class="px-4 py-2 hover:bg-slate-50 transition-colors border-r border-slate-300">Periode</button>
                        <button onclick="setFilterDate('semua')" id="btn-semua" class="px-4 py-2 bg-blue-50 text-blue-600 transition-colors">Semua</button>
                    </div>

                    <div id="custom-date-filter" class="hidden items-center gap-2 bg-white border border-slate-300 rounded-lg px-2 py-1">
                        <input type="date" id="start_date" onchange="loadData()" class="border-none outline-none text-xs font-bold text-slate-600 bg-transparent">
                        <span class="text-slate-400">-</span>
                        <input type="date" id="end_date" onchange="loadData()" class="border-none outline-none text-xs font-bold text-slate-600 bg-transparent">
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
                            <tr class="text-xs font-black text-slate-600">
                                <th class="p-4">Waktu</th>
                                <th class="p-4">Barang</th>
                                <th class="p-4">Jumlah</th>
                                <th class="p-4">Sumber</th>
                                <th class="p-4">Supplier</th>
                                <th class="p-4">Keterangan</th>
                                <th class="p-4">User</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination" class="mt-6 flex items-center justify-center gap-2"></div>

        </main>
    </div>

    <script>
        let currentFilterDate = 'semua';
    </script>
    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>