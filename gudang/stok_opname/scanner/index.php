<?php
require_once '../../../config/auth.php';
checkPermission('scanner_opname');
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8" 
              x-data="{ unlocked: false, pin: '' }"
              @unlock-session.window="unlocked = true">
            
            <div x-show="!unlocked" x-transition class="flex flex-col items-center justify-center min-h-[70vh]">
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl border border-slate-200 w-full max-w-md text-center">
                    <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-lock text-2xl text-indigo-500"></i>
                        </div>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-2">Kontrol Akses Opname</h2>
                    <p class="text-sm text-slate-500 mb-8 font-medium">Masukkan 6-digit Kode Akses yang telah di-generate oleh Manager/Admin.</p>
                    
                    <form id="form-pin" class="space-y-6">
                        <input type="text" x-model="pin" id="input-pin" maxlength="6" placeholder="• • • • • •" class="w-full text-center text-4xl tracking-[0.5em] font-black text-indigo-600 px-4 py-4 border-2 border-slate-200 rounded-2xl focus:border-indigo-500 outline-none bg-slate-50 transition-all">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-200">
                            Buka Sesi Audit
                        </button>
                    </form>
                </div>
            </div>

            <div x-show="unlocked" x-cloak x-transition class="space-y-6">
                
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                            <i class="fa-solid fa-clipboard-check text-indigo-600"></i> Input Hasil Opname
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">Scan barcode, cari manual, atau gunakan import Excel untuk menyesuaikan stok fisik.</p>
                    </div>
                    <button @click="unlocked = false; pin = '';" class="bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-lock"></i> Kunci Sesi
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 md:p-8">
                    
                    <div class="flex flex-wrap gap-2 mb-8 bg-emerald-50 border border-emerald-100 p-2 rounded-xl w-max items-center">
                        <button onclick="downloadTemplate()" class="flex items-center text-emerald-600 hover:bg-emerald-100 transition-colors text-xs font-bold px-3 py-1.5 border-r border-emerald-200" title="Download Template Excel/CSV">
                            <i class="fa-regular fa-file-excel mr-2"></i> Template
                        </button>
                        
                        <select id="filter_rak" class="bg-white border border-emerald-200 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold outline-none cursor-pointer">
                            <option value="">Semua Rak</option>
                            </select>
                        
                        <button onclick="document.getElementById('fileImport').click()" class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center">
                            <i class="fa-solid fa-upload mr-2"></i> Import
                        </button>
                        
                        <input type="file" id="fileImport" class="hidden" accept=".csv" onchange="prosesImport(this)">
                    </div>

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                            <div class="col-span-1 md:col-span-3 relative">
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Cari Barang <span class="text-rose-500">*</span></label>
                                <div class="absolute inset-y-0 left-0 pl-4 top-6 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-search text-slate-400"></i>
                                </div>
                                <select id="material_id" class="w-full pl-11 pr-4 py-3.5 border border-slate-300 rounded-xl focus:border-indigo-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                                    <option value="">Ketik nama barang atau SKU...</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Jumlah Fisik <span class="text-rose-500">*</span></label>
                                <div class="flex">
                                    <input type="number" step="any" id="phys_qty" placeholder="0" class="w-full px-4 py-3.5 border border-slate-300 rounded-l-xl focus:border-indigo-600 outline-none transition-all font-black text-indigo-600 text-center bg-slate-50">
                                    <div class="bg-slate-100 border border-l-0 border-slate-300 rounded-r-xl px-4 flex items-center justify-center text-xs font-bold text-slate-400 uppercase" id="unit_label">-</div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Catatan (Opsional)</label>
                            <input type="text" id="notes" placeholder="Contoh: Barang rusak, hilang, salah hitung..." class="w-full px-4 py-3.5 border border-slate-300 rounded-xl focus:border-indigo-600 outline-none transition-all font-medium text-slate-700 bg-slate-50">
                        </div>

                        <button onclick="tambahKeDaftar()" class="w-full bg-slate-200 hover:bg-slate-800 text-slate-600 hover:text-white py-4 rounded-xl font-black transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                            <i class="fa-solid fa-plus"></i> Tambahkan ke Daftar
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <h3 class="font-black text-slate-700 flex items-center gap-2"><i class="fa-regular fa-folder-open text-indigo-500"></i> Kelola Draft Tersimpan</h3>
                        <div class="flex items-center gap-3">
                            <button onclick="kosongkanDraft()" class="text-xs font-bold text-rose-500 hover:bg-rose-50 px-3 py-1 rounded-lg transition-colors border border-rose-200">Kosongkan Draft</button>
                            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-black" id="draft-count">0 Item</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[700px]">
                            <thead class="bg-white border-b border-slate-100">
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-4 w-12 text-center">No</th>
                                    <th class="p-4">SKU & Barang</th>
                                    <th class="p-4 text-center">Stok Sistem</th>
                                    <th class="p-4 text-center">Stok Fisik</th>
                                    <th class="p-4 text-center">Selisih</th>
                                    <th class="p-4">Catatan</th>
                                    <th class="p-4 text-center w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="draft-table" class="divide-y divide-slate-50 font-medium text-slate-700">
                                <tr><td colspan="7" class="p-10 text-center text-slate-400 italic font-bold">Draft masih kosong. Tambahkan barang di atas.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-slate-100 bg-slate-50 flex justify-end">
                        <button onclick="simpanOpname()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-200 flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Hasil Opname
                        </button>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>