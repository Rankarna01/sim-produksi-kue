<?php
require_once '../../../config/auth.php';
checkPermission('trx_permintaan_barang');
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" x-data="{ view: 'list', tab: 'semua' }">
            
            <div x-show="view === 'list'" x-transition.opacity class="space-y-6">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Permintaan Barang</h2>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Kelola permintaan barang dari gudang ke Purchasing/Supplier.</p>
                    </div>
                    <button @click="view = 'form'" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Buat Permintaan
                    </button>
                </div>

                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        
                        <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 w-full md:w-auto">
                                <i class="fa-regular fa-clock text-slate-400 mr-2"></i>
                                <span class="text-xs font-bold text-slate-500 mr-2">Periode:</span>
                                <input type="date" id="start_date" class="bg-transparent border-none outline-none text-xs font-bold text-slate-700 w-28" onchange="loadData()">
                                <span class="mx-1 text-slate-300">-</span>
                                <input type="date" id="end_date" class="bg-transparent border-none outline-none text-xs font-bold text-slate-700 w-28" onchange="loadData()">
                            </div>

                            <div class="flex gap-1 text-sm font-bold">
                                <button @click="tab = 'semua'; switchTab('semua')" :class="tab === 'semua' ? 'text-blue-600 bg-blue-50' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-xl transition-all">Semua</button>
                                <button @click="tab = 'pending'; switchTab('pending')" :class="tab === 'pending' ? 'text-blue-600 bg-blue-50' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-xl transition-all">Menunggu</button>
                                <button @click="tab = 'processing'; switchTab('processing')" :class="tab === 'processing' ? 'text-blue-600 bg-blue-50' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-xl transition-all">Diproses</button>
                                <button @click="tab = 'rejected'; switchTab('rejected')" :class="tab === 'rejected' ? 'text-blue-600 bg-blue-50' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-xl transition-all">Ditolak</button>
                            </div>
                        </div>
                        
                        <div class="relative w-full md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-search text-slate-400 text-xs"></i>
                            </div>
                            <input type="text" id="search" placeholder="Cari barang atau pengirim..." class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-blue-600 outline-none transition-all text-xs font-bold text-slate-700 bg-slate-50" onkeyup="cariData()">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[900px]">
                            <thead class="bg-white border-b border-slate-100">
                                <tr class="text-[11px] font-bold text-slate-500">
                                    <th class="p-5 font-medium">Tanggal</th>
                                    <th class="p-5 font-medium">Barang</th>
                                    <th class="p-5 font-medium text-center">Jumlah</th>
                                    <th class="p-5 font-medium">Pengirim</th>
                                    <th class="p-5 font-medium text-center">Status</th>
                                    <th class="p-5 font-medium">Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="table-data" class="divide-y divide-slate-50 text-slate-700">
                                <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50/50"></div>
                </div>
            </div>

            <div x-show="view === 'form'" x-cloak x-transition.opacity class="space-y-6">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Permintaan Barang</h2>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Kelola permintaan barang dari dapur/staff</p>
                    </div>
                    <button @click="view = 'list'" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md flex items-center gap-2">
                        <i class="fa-regular fa-file-lines"></i> Lihat Daftar
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
                            <h3 class="font-black text-slate-800 text-lg mb-6">Form Permintaan Barang</h3>
                            
                            <form id="form-item" class="space-y-5" onsubmit="event.preventDefault(); addToCart();">
                                
                                <div class="relative">
                                    <label class="block text-xs font-bold text-slate-600 mb-2">Cari Nama Barang <span class="text-rose-500">*</span></label>
                                    <input type="text" id="search_material" placeholder="Ketik nama atau SKU bahan..." autocomplete="off" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-white shadow-sm" onkeyup="filterMaterialList()">
                                    <input type="hidden" id="material_id" name="material_id" required>
                                    
                                    <div id="material_list" class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden custom-scrollbar"></div>
                                </div>

                                <div class="grid grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-2">Jumlah Diminta <span class="text-rose-500">*</span></label>
                                        <input type="number" step="any" id="qty" required placeholder="0" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-blue-600 bg-white shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-2">Satuan</label>
                                        <input type="text" id="unit_label" readonly placeholder="Pcs" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none font-bold text-slate-400 bg-slate-50 cursor-not-allowed">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-2">Catatan / Urgensi (Opsional)</label>
                                    <textarea id="notes" rows="2" placeholder="Contoh: Stok menipis, butuh cepat untuk pesanan besok..." class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-medium text-slate-700 bg-white shadow-sm custom-scrollbar"></textarea>
                                </div>

                                <button type="submit" class="w-full bg-slate-800 hover:bg-black text-white py-3.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2 mt-4 shadow-md">
                                    <i class="fa-solid fa-plus"></i> Tambah ke Daftar Permintaan
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full min-h-[400px]">
                            <div class="p-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <h3 class="font-black text-slate-700 flex items-center gap-2"><i class="fa-solid fa-cart-shopping text-slate-400"></i> Daftar Permintaan (<span id="cart-count">0</span>)</h3>
                                <button onclick="clearCart()" class="text-rose-500 hover:text-rose-700 font-bold text-xs">Hapus Semua</button>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto p-0 custom-scrollbar">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-white border-b border-slate-100 sticky top-0 z-10">
                                        <tr class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                            <th class="p-4">Barang</th>
                                            <th class="p-4">Jml</th>
                                            <th class="p-4 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-table" class="divide-y divide-slate-50">
                                        <tr><td colspan="3" class="p-10 text-center text-slate-400 italic text-xs font-bold">Belum ada barang di daftar.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-5 border-t border-slate-100 bg-white">
                                <button onclick="submitCart()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-paper-plane"></i> Kirim Permintaan
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>