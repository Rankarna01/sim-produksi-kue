<?php
require_once '../../config/auth.php';
checkPermission('laporan_titipan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Laporan Produk Titipan</h2>
                <p class="text-sm text-slate-500 mt-1">Pantau riwayat validasi, estimasi omset, dan profit bersih dari UMKM (Hanya menghitung status Diterima).</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Valid (Masuk Gudang)</p>
                        <h3 class="text-2xl font-black text-slate-800" id="sum-qty">0 <span class="text-sm text-slate-500 font-bold">Pcs</span></h3>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Estimasi Omset</p>
                        <h3 class="text-2xl font-black text-emerald-600" id="sum-omset">Rp 0</h3>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-2xl shrink-0">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Profit / Keuntungan</p>
                        <h3 class="text-2xl font-black text-amber-500" id="sum-profit">Rp 0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Akhir</label>
                        <input type="date" id="end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Store Tujuan</label>
                        <select id="filter_store" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                            <option value="">Semua Store</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status Validasi</label>
                        <select id="filter_status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                            <option value="">Semua Status</option>
                            <option value="received">Valid (Masuk Gudang)</option>
                            <option value="pending">Pending</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-black shadow-md transition-all">Filter</button>
                        <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-black transition-all">Reset</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-slate-700"><i class="fa-solid fa-list text-slate-400 mr-2"></i> Rincian Data Transaksi</h3>
                    <div class="flex gap-2">
                        <button onclick="exportExcel()" class="bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white border border-emerald-200 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                            <i class="fa-solid fa-file-excel"></i> Excel
                        </button>
                        <button onclick="printPDF()" class="bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-200 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf"></i> Print
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[1100px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-white">
                                <th class="p-4 text-center">No</th>
                                <th class="p-4">Tanggal & Inv</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4">Store Tujuan</th>
                                <th class="p-4">Produk Titipan / UMKM</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Harga Modal</th>
                                <th class="p-4 text-right">Harga Jual</th>
                                <th class="p-4 text-right text-emerald-600">Omset</th>
                                <th class="p-4 text-right text-amber-500">Profit</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50 font-medium text-slate-600">
                            <tr><td colspan="10" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center text-sm font-bold text-slate-500">
                    <div>Menampilkan <span id="page-info">0 - 0 dari 0</span> data</div>
                    <div class="flex gap-2" id="pagination-controls"></div>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>