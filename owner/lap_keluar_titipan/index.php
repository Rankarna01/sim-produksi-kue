<?php
require_once '../../config/auth.php';
checkPermission('lap_keluar_titipan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">
    <?php include '../../components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <div class="mb-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Produk Keluar (Titipan)</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau barang UMKM yang ditarik, expired, atau rusak.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="exportData('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fa-regular fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="exportData('pdf')" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fa-solid fa-print"></i> Cetak PDF
                    </button>
                </div>
            </div>

            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Periode</label>
                    <select id="filter_periode" onchange="toggleDateCustom()" class="w-full px-3 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                        <option value="bulan_ini">Bulan Ini</option>
                        <option value="hari_ini">Hari Ini</option>
                        <option value="semua">Semua Waktu</option>
                        <option value="custom">Pilih Tanggal...</option>
                    </select>
                </div>
                <div class="custom-date hidden">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Dari Tanggal</label>
                    <input type="date" id="start_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-white">
                </div>
                <div class="custom-date hidden">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                    <input type="date" id="end_date" class="w-full px-3 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-white">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Alasan Keluar</label>
                    <select id="filter_reason" class="w-full px-3 py-2 border border-slate-300 rounded-xl outline-none text-sm font-bold text-slate-700 bg-slate-50">
                        <option value="semua">Semua Alasan</option>
                        <option value="Expired">Expired</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Diretur UMKM">Diretur UMKM</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end mt-2">
                    <button onclick="loadData(1)" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-sm transition-all">Terapkan</button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[900px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 text-center w-12">No</th>
                                <th class="p-5">Waktu Ditarik</th>
                                <th class="p-5">Petugas</th>
                                <th class="p-5">Nama UMKM</th>
                                <th class="p-5">Produk</th>
                                <th class="p-5 text-center">QTY (PCS)</th>
                                <th class="p-5 text-center">Alasan</th>
                                <th class="p-5">Catatan</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="pagination" class="mt-6 flex items-center justify-center gap-2 pb-10"></div>
        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>