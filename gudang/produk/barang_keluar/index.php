<?php
require_once '../../../config/auth.php';
checkPermission('trx_barang_keluar');
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ tab: 'semua' }">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Barang Keluar</h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau dan kelola pencatatan pengurangan stok bahan baku secara manual.</p>
                </div>
                <button onclick="openModalKeluar()" class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-rose-200 flex items-center gap-2">
                    <i class="fa-solid fa-minus"></i> Tambah Keluar
                </button>
            </div>

            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search" placeholder="Cari transaksi atau barang..." class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all text-sm font-bold text-slate-600 bg-slate-50" onkeyup="cariData()">
                    </div>
                    
                    <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 w-full md:w-auto">
                        <i class="fa-regular fa-calendar text-slate-400 mr-2"></i>
                        <input type="date" id="start_date" class="bg-transparent border-none outline-none text-sm font-bold text-slate-600" onchange="loadData()">
                        <span class="mx-2 text-slate-300">-</span>
                        <input type="date" id="end_date" class="bg-transparent border-none outline-none text-sm font-bold text-slate-600" onchange="loadData()">
                    </div>

                    <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto overflow-hidden">
                        <button @click="tab = 'semua'; switchTab('semua')" :class="tab === 'semua' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all">Semua</button>
                        <button @click="tab = 'Rusak'; switchTab('Rusak')" :class="tab === 'Rusak' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all">Rusak</button>
                        <button @click="tab = 'Expired'; switchTab('Expired')" :class="tab === 'Expired' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all">Expired</button>
                        <button @click="tab = 'Lainnya'; switchTab('Lainnya')" :class="tab === 'Lainnya' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all">Lainnya</button>
                    </div>
                </div>
                
                <button onclick="cetakLaporan()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-print"></i> Cetak Laporan
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5">Tanggal & Waktu (TRX)</th>
                                <th class="p-5">Barang</th>
                                <th class="p-5 text-center">Status Keluar</th>
                                <th class="p-5 text-center">Jumlah Keluar</th>
                                <th class="p-5 text-center">Satuan</th>
                                <th class="p-5 text-center">Approval</th>
                                <th class="p-5">Keterangan</th>
                                <th class="p-5">Oleh User</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-50 font-medium text-slate-700">
                            <tr><td colspan="8" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50/50"></div>
            </div>

        </main>
    </div>

    <div id="modal-keluar" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalKeluar()"></div>
        <div class="relative bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl z-10 transform transition-all flex flex-col overflow-hidden max-h-[95vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-black text-slate-800 tracking-tighter">Input Barang Keluar</h3>
                <button onclick="closeModalKeluar()" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <div class="mb-4 text-xs font-bold text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-200">
                    <i class="fa-solid fa-circle-info mr-1"></i> Data barang keluar membutuhkan persetujuan Owner untuk memotong stok Gudang.
                </div>
                <form id="formKeluar" class="space-y-5">
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pilih Barang <span class="text-rose-500">*</span></label>
                        <select id="material_id" name="material_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-bold text-slate-700 bg-slate-50" onchange="updateSatuan()">
                            <option value="">-- Pilih Barang --</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1 pl-1" id="stock_info">Pilih barang untuk melihat sisa stok.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Jumlah Keluar <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" id="qty" name="qty" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-black text-rose-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Satuan</label>
                            <input type="text" id="satuan_label" readonly class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none font-bold text-slate-400 bg-slate-100 cursor-not-allowed" placeholder="-">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status Keluar <span class="text-rose-500">*</span></label>
                        <select id="status" name="status" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                            <option value="Rusak">Rusak / Cacat</option>
                            <option value="Expired">Kadaluarsa (Expired)</option>
                            <option value="Lainnya">Alasan Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Keterangan / Alasan</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Contoh: Terjatuh saat pemindahan, Uji coba, dll" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-medium text-slate-700 bg-slate-50 custom-scrollbar"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModalKeluar()" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-white bg-rose-500 hover:bg-rose-600 rounded-xl transition-all shadow-md shadow-rose-200">
                            Ajukan Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
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