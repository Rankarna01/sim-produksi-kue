<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Tambahan agar scroll tabel lebih halus di iOS */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50" onclick="closeAllDropdowns(event)">

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex flex-col sm:row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">Rencana Harian (Forecast)</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Karyawan wajib menyusun target produksi sebelum melakukan input aktual.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-5 sm:p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-black text-slate-800 text-sm sm:text-base"><i class="fa-solid fa-clipboard-check text-blue-600 mr-2"></i> Buat Target Hari Ini</h3>
                        </div>
                        
                        <div class="p-5 sm:p-6">
                            <form id="form-plan" class="space-y-6">
                                <div>
                                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Pilih Karyawan (Petugas) <span class="text-rose-500">*</span></label>
                                    <select id="karyawan_id" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-slate-50 text-sm">
                                        <option value="">-- Memuat Karyawan... --</option>
                                    </select>
                                    <p class="text-[10px] font-bold text-amber-500 mt-2 leading-relaxed">*Hanya karyawan yang sudah membuat rencana yang bisa input produksi aktual.</p>
                                </div>

                                <div class="p-4 sm:p-5 bg-blue-50/50 border border-blue-100 rounded-2xl">
                                    <h4 class="font-black text-slate-700 text-sm mb-3">Item yang akan diproduksi</h4>
                                    <!-- Responsive Grid for Input Item -->
                                    <div class="grid grid-cols-2 md:flex md:flex-row gap-3 items-end">
                                        
                                        <div class="col-span-2 md:flex-1 relative">
                                            <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Pilih Produk</label>
                                            <input type="text" id="search_product" class="search-input w-full px-3 py-2 border border-slate-300 rounded-lg outline-none font-bold text-slate-700 bg-white placeholder:font-normal placeholder:text-slate-400 text-sm" placeholder="Nama/Kode produk..." autocomplete="off" onfocus="showDropdown()" oninput="filterDropdown()">
                                            <input type="hidden" id="item_product_id">
                                            <ul id="product_list" class="custom-dropdown custom-scrollbar absolute z-50 w-full bg-white border border-slate-200 shadow-xl rounded-lg mt-1 max-h-48 overflow-y-auto hidden"></ul>
                                        </div>

                                        <div class="col-span-1 md:w-28">
                                            <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Target (Pcs)</label>
                                            <input type="number" id="item_qty" value="1" min="1" class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none font-black text-blue-600 text-center text-sm">
                                        </div>
                                        <div class="col-span-1 md:w-32">
                                            <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Adonan (Kg)</label>
                                            <input type="number" step="0.01" id="item_adonan" placeholder="Opsional" class="w-full px-3 py-2 border border-slate-300 rounded-lg outline-none font-bold text-slate-700 text-center text-sm">
                                        </div>
                                        <button type="button" onclick="tambahItem()" class="col-span-2 md:w-auto h-[40px] bg-slate-800 hover:bg-black text-white px-5 rounded-lg font-bold transition-all text-sm">
                                            Tambah
                                        </button>
                                    </div>
                                </div>

                                <!-- Table Container with Horizontal Scroll -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <div class="overflow-x-auto table-responsive">
                                        <table class="w-full text-left text-sm min-w-[500px]">
                                            <thead class="bg-slate-50 border-b border-slate-100">
                                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                                    <th class="p-3">Produk</th>
                                                    <th class="p-3 text-center">Target</th>
                                                    <th class="p-3 text-center">Est. Adonan</th>
                                                    <th class="p-3 text-center w-16">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cart-plan" class="divide-y divide-slate-100 font-bold text-slate-700">
                                                <tr><td colspan="4" class="p-6 text-center text-slate-400 italic text-xs">Belum ada target yang ditambahkan.</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Tambahan (Opsional)</label>
                                    <textarea id="notes" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-medium text-slate-700 bg-slate-50 text-sm" placeholder="Misal: Shift pagi target dikebut..."></textarea>
                                </div>

                                <div class="flex justify-end pt-4 border-t border-slate-100">
                                    <button type="button" onclick="simpanRencana()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 sm:py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-blue-200 flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-paper-plane"></i> Simpan Rencana
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar (Sudah Buat Hari Ini) -->
                <div>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden lg:sticky lg:top-6">
                        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <h3 class="font-black text-slate-800 text-xs sm:text-sm uppercase tracking-widest">Sudah Buat Hari Ini</h3>
                            <button onclick="loadTodayPlans()" class="text-slate-400 hover:text-blue-600 transition-colors p-2"><i class="fa-solid fa-rotate-right"></i></button>
                        </div>
                        <div class="p-4">
                            <!-- Di dalam sini biasanya ada list, pastikan isi dari ajax.js juga menghasilkan elemen yang rapi -->
                            <div id="list-today" class="space-y-3">
                                <p class="text-center text-xs text-slate-400 py-4"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>