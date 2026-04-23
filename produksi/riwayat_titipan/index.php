<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
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

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Riwayat Produk Titipan</h2>
                <p class="text-sm text-slate-500 mt-1">Daftar semua pengiriman barang titipan UMKM berdasarkan Invoice. Anda dapat merevisi atau membatalkan data.</p>
            </div>

            <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Akhir</label>
                        <input type="date" id="end_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Store Tujuan</label>
                        <select id="filter_store" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                            <option value="">Semua Store</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status</label>
                        <select id="filter_status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="received">Diterima</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-black shadow-md transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl text-sm font-black transition-all">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-slate-50">
                                <th class="p-4 sm:p-5 w-16 text-center">No</th>
                                <th class="p-4 sm:p-5">Tanggal & Waktu</th>
                                <th class="p-4 sm:p-5">No. Invoice</th>
                                <th class="p-4 sm:p-5">Pencatat / Dapur</th>
                                <th class="p-4 sm:p-5">Daftar Produk (Qty)</th>
                                <th class="p-4 sm:p-5 text-center">Total Pcs</th>
                                <th class="p-4 sm:p-5 text-center">Status</th>
                                <th class="p-4 sm:p-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50 font-medium text-slate-600">
                            <tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
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