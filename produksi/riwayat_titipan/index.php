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
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50" onclick="closeAllDropdowns(event)">

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
                            <option value="received">Diterima / Valid</option>
                            <option value="ditolak">Ditolak</option>
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

    <div id="modal-revisi" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModalRevisi()"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-[2rem] shadow-xl z-10 flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center rounded-t-[2rem]">
                <div>
                    <h3 class="text-xl font-black text-slate-800 flex items-center gap-2"><i class="fa-solid fa-pen-to-square text-amber-500"></i> Revisi Barang Titipan</h3>
                    <p class="text-xs text-slate-500 font-bold mt-1">Invoice: <span id="rev_invoice" class="text-blue-600"></span></p>
                </div>
                <button onclick="tutupModalRevisi()" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-2xl"></i></button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 custom-scrollbar">
                <form id="formRevisi">
                    <input type="hidden" id="rev_prod_id" name="production_id">
                    <input type="hidden" id="rev_emp_name">
                    
                    <div id="rev-product-container" class="space-y-4">
                        </div>

                    <button type="button" onclick="addRevRow()" class="mt-5 bg-white hover:bg-slate-100 text-slate-700 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-2 border border-slate-300 border-dashed w-full sm:w-auto justify-center shadow-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Barang Lainnya
                    </button>
                </form>
            </div>
            
            <div class="p-6 border-t border-slate-100 bg-white rounded-b-[2rem] flex justify-end gap-3">
                <button type="button" onclick="tutupModalRevisi()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all">Batal</button>
                <button type="button" onclick="submitRevisi()" class="bg-amber-500 hover:bg-amber-600 text-white px-8 py-2.5 rounded-xl text-sm font-black shadow-md shadow-amber-200 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan & Kirim Ulang
                </button>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>