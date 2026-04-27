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
                    <table class="w-full text-left text-sm min-w-[900px]">
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

    <div id="modal-keluar" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalKeluar()"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-[2.5rem] shadow-2xl z-10 flex flex-col overflow-hidden max-h-full">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 shrink-0">
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tighter">Input Barang Keluar</h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">Tambahkan beberapa barang ke daftar sebelum memotong stok.</p>
                </div>
                <button onclick="closeModalKeluar()" class="text-slate-400 hover:bg-rose-50 hover:text-rose-500 w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 bg-slate-50/30">
                <div class="mb-6 text-xs font-bold text-amber-600 bg-amber-50 p-3.5 rounded-xl border border-amber-200 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <span>Data barang keluar membutuhkan persetujuan Owner untuk memotong stok Gudang (Tergantung SOP Toko).</span>
                </div>
                
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6 relative">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Cari & Pilih Barang <span class="text-rose-500">*</span></label>
                            
                            <input type="text" id="search_material" placeholder="Ketik nama bahan..." autocomplete="off" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-bold text-slate-700 bg-slate-50" onkeyup="filterMaterialList()">
                            <input type="hidden" id="material_id">
                            
                            <div id="material_list" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden custom-scrollbar"></div>
                            
                            <p id="stock_info" class="text-[10px] text-slate-400 mt-1 pl-1 font-bold">Pilih barang untuk melihat sisa stok.</p>
                            <input type="hidden" id="max_stock" value="0">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Jumlah Keluar <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" id="qty" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-black text-rose-600 bg-slate-50">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Satuan</label>
                            <input type="text" id="satuan_label" readonly class="w-full px-3 py-2.5 border border-slate-200 rounded-xl outline-none font-bold text-slate-400 bg-slate-100 cursor-not-allowed" placeholder="-">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" onclick="tambahKeDraft()" class="bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Tambah ke Daftar
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Daftar Barang Keluar</h4>
                        <span id="draft-count" class="bg-rose-100 text-rose-600 text-[10px] font-black px-2 py-0.5 rounded-full">0 ITEM</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[600px]">
                            <thead class="bg-white border-b border-slate-100">
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-3 pl-5 text-center w-12">No</th>
                                    <th class="p-3">Nama Bahan Baku</th>
                                    <th class="p-3 text-right">Jumlah Keluar</th>
                                    <th class="p-3 pr-5 text-center w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-draft" class="divide-y divide-slate-50">
                                <tr><td colspan="4" class="p-6 text-center text-slate-400 italic font-bold text-xs">Belum ada barang ditambahkan.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Alasan Keluar Global <span class="text-rose-500">*</span></label>
                        <select id="status" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                            <option value="Rusak">Rusak / Cacat</option>
                            <option value="Expired">Kadaluarsa (Expired)</option>
                            <option value="Lainnya">Alasan Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Catatan / Keterangan</label>
                        <input type="text" id="notes" placeholder="Contoh: Jatuh saat angkat barang..." class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none transition-all font-medium text-slate-700 bg-slate-50">
                    </div>
                </div>

            </div>

            <div class="p-5 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0 rounded-b-[2.5rem]">
                <button type="button" onclick="closeModalKeluar()" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="button" onclick="simpanTransaksi()" class="px-8 py-3 text-xs font-black uppercase tracking-widest text-white bg-rose-500 hover:bg-rose-600 rounded-xl transition-all shadow-md shadow-rose-200 flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Ajukan Keluar
                </button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px;}
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>