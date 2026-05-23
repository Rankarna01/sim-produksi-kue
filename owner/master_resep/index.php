<?php
require_once '../../config/auth.php';
checkPermission('master_resep');
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
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8">

            <!-- PAGE HEADER -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Resep Produk (BOM)</h2>
                <p class="text-sm text-secondary mt-1">Atur komposisi bahan baku untuk setiap produk & item custom POS.</p>
            </div>

            <!-- ================================
                 TAB NAVIGATION
                 ================================ -->
            <div class="flex items-center gap-1 mb-6 bg-white border border-slate-200 p-1.5 rounded-2xl w-fit shadow-sm">
                <button id="tab-btn-produk" onclick="switchTab('produk')"
                    class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all bg-blue-600 text-white shadow-md shadow-blue-100">
                    <i class="fa-solid fa-cake-candles"></i> Produk Jadi
                </button>
                <button id="tab-btn-custom" onclick="switchTab('custom')"
                    class="tab-btn flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all text-slate-500 hover:bg-slate-100">
                    <i class="fa-solid fa-star-half-stroke"></i> Item Custom POS
                </button>
            </div>

            <!-- ================================
                 TAB KONTEN: PRODUK JADI
                 ================================ -->
            <div id="tab-produk" class="tab-content">
                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-4 text-center w-16">No</th>
                                    <th class="p-4">Nama Produk</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4 text-center">Status Resep</th>
                                    <th class="p-4 text-center w-40">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-products" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="5" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================================
                 TAB KONTEN: ITEM CUSTOM POS
                 ================================ -->
            <div id="tab-custom" class="tab-content hidden">

                <!-- INFO BANNER -->
                <div class="mb-5 p-4 bg-violet-50 border border-violet-200 rounded-2xl flex items-start gap-3">
                    <div class="w-9 h-9 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-circle-info text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-violet-800 uppercase tracking-widest">Resep Item Custom POS</p>
                        <p class="text-xs text-violet-700 mt-1">
                            Item di bawah ini bersumber dari <strong>template item custom Kasir POS</strong> 
                            (<code class="bg-violet-100 px-1 rounded">saved_custom_items_pos</code>). 
                            Atur resep bahan baku untuk setiap item agar bahan baku dapat terpotong saat proses produksi.
                            Perubahan resep di sini <strong>langsung berlaku</strong> tanpa persetujuan Owner.
                        </p>
                    </div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-4 text-center w-16">No</th>
                                    <th class="p-4">Nama Item Custom</th>
                                    <th class="p-4">Tipe</th>
                                    <th class="p-4">Harga POS</th>
                                    <th class="p-4 text-center">Status Resep</th>
                                    <th class="p-4 text-center w-44">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-custom-items" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="6" class="p-8 text-center text-secondary">Memuat data item custom...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ================================================================
         MODAL: RESEP PRODUK JADI (tidak diubah dari versi asli)
         ================================================================ -->
    <div id="modal-resep" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-resep')"></div>
        <div class="bg-surface w-full max-w-2xl rounded-3xl shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh] overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-mortar-pestle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Racik Resep Baru</h3>
                        <p id="modal-product-name" class="text-xs text-blue-600 font-bold mt-0.5 tracking-wide">Nama Produk</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-resep')" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar bg-slate-50/30 flex-1">
                
                <form id="formTambahBahan" class="mb-6 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <input type="hidden" id="bom_product_id" name="product_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Pilih Bahan Baku</label>
                            <select id="pilar_material_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                                <option value="">-- Pilih Bahan --</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Takaran (1 Pcs)</label>
                            <div class="relative">
                                <input type="number" step="any" id="quantity" required min="0.0001" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:border-blue-600 outline-none text-sm font-black text-blue-600 bg-slate-50" placeholder="0.00">
                                <button type="button" onclick="toggleKalkulator()" class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all" title="Gunakan Kalkulator Konversi">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Satuan</label>
                            <select id="unit_used" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                                <option value="">Satuan</option>
                            </select>
                        </div>
                    </div>

                    <div id="panel-kalkulator" class="hidden animate-in fade-in slide-in-from-top-2 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                            <h5 class="text-xs font-black text-amber-700 uppercase tracking-widest">Kalkulator Konversi Adonan</h5>
                        </div>
                        <div class="grid grid-cols-2 gap-4 items-center">
                            <div>
                                <label class="block text-[9px] font-bold text-amber-600 mb-1 uppercase">Total Bahan (Adonan)</label>
                                <input type="number" id="calc_total_bahan" step="any" class="w-full px-3 py-2 rounded-lg border border-amber-200 outline-none text-sm font-bold text-slate-700" placeholder="Cth: 1 (Kg)">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-amber-600 mb-1 uppercase">Hasil Jadi (Pcs)</label>
                                <input type="number" id="calc_hasil_pcs" step="any" class="w-full px-3 py-2 rounded-lg border border-amber-200 outline-none text-sm font-bold text-slate-700" placeholder="Cth: 10 (Roti)">
                            </div>
                        </div>
                        <div class="mt-3 flex justify-between items-center">
                            <p class="text-[10px] text-amber-600 italic font-medium">*Rumus: Total Bahan / Hasil Jadi</p>
                            <button type="button" onclick="hitungKonversi()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest shadow-sm transition-all">Terapkan Hasil</button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 border border-slate-300 border-dashed">
                            <i class="fa-solid fa-plus"></i> Tambah Ke Draft Racikan
                        </button>
                    </div>
                </form>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-6">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Daftar Racikan (Draft)</h4>
                        <span id="total-item-badge" class="bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-full">0 BAHAN</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <tbody id="table-bom" class="divide-y divide-slate-50">
                                </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Catatan / Alasan Perubahan <span class="text-rose-500">*</span></label>
                    <input type="text" id="req_notes" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-medium text-slate-700 bg-white" placeholder="Contoh: Menyesuaikan resep baru dari RnD, lebih manis...">
                </div>

            </div>

            <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3 shrink-0 rounded-b-3xl">
                <button type="button" onclick="closeModal('modal-resep')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all text-sm">Batal</button>
                <button type="button" onclick="ajukanResep()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pengajuan Ke Owner
                </button>
            </div>
        </div>
    </div>

    <!-- ================================================================
         MODAL: RESEP ITEM CUSTOM POS (BARU)
         ================================================================ -->
    <div id="modal-resep-custom" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-resep-custom')"></div>
        <div class="bg-surface w-full max-w-2xl rounded-3xl shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh] overflow-hidden">
            
            <!-- Header Modal Custom -->
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-violet-50 text-violet-600 rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Racik Resep Custom</h3>
                        <p id="modal-custom-product-name" class="text-xs text-violet-600 font-bold mt-0.5 tracking-wide">Nama Item Custom</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-resep-custom')" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Info Chip -->
            <div class="px-6 pt-4 shrink-0">
                <div class="p-3 bg-violet-50 border border-violet-100 rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-bolt text-violet-500 text-xs"></i>
                    <p class="text-[10px] font-bold text-violet-700">Resep ini <strong>langsung disimpan</strong> tanpa memerlukan persetujuan Owner. Cocok untuk item custom yang sering berubah.</p>
                </div>
            </div>
            
            <!-- Body Modal Custom -->
            <div class="p-6 overflow-y-auto custom-scrollbar bg-slate-50/30 flex-1">
                
                <form id="formTambahBahanCustom" class="mb-6 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <input type="hidden" id="bom_custom_item_id" name="custom_item_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Pilih Bahan Baku</label>
                            <select id="custom_material_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-violet-500 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                                <option value="">-- Pilih Bahan --</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Takaran (1 Pcs)</label>
                            <div class="relative">
                                <input type="number" step="any" id="custom_quantity" required min="0.0001" 
                                    class="w-full pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:border-violet-500 outline-none text-sm font-black text-violet-600 bg-slate-50" placeholder="0.00">
                                <button type="button" onclick="toggleKalkulatorCustom()" 
                                    class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all" title="Kalkulator Konversi">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Satuan</label>
                            <select id="custom_unit_used" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-violet-500 outline-none text-sm font-bold text-slate-700 bg-slate-50">
                                <option value="">Satuan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kalkulator Custom -->
                    <div id="panel-kalkulator-custom" class="hidden p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                            <h5 class="text-xs font-black text-amber-700 uppercase tracking-widest">Kalkulator Konversi Adonan</h5>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-amber-600 mb-1 uppercase">Total Bahan (Adonan)</label>
                                <input type="number" id="custom_calc_total" step="any" class="w-full px-3 py-2 rounded-lg border border-amber-200 outline-none text-sm font-bold text-slate-700" placeholder="Cth: 1 (Kg)">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-amber-600 mb-1 uppercase">Hasil Jadi (Pcs)</label>
                                <input type="number" id="custom_calc_pcs" step="any" class="w-full px-3 py-2 rounded-lg border border-amber-200 outline-none text-sm font-bold text-slate-700" placeholder="Cth: 10 pcs">
                            </div>
                        </div>
                        <div class="mt-3 flex justify-between items-center">
                            <p class="text-[10px] text-amber-600 italic font-medium">*Rumus: Total Bahan ÷ Hasil Jadi</p>
                            <button type="button" onclick="hitungKonversiCustom()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest shadow-sm transition-all">Terapkan</button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-violet-50 hover:bg-violet-100 text-violet-700 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 border border-violet-200 border-dashed">
                            <i class="fa-solid fa-plus"></i> Tambah Ke Draft Racikan
                        </button>
                    </div>
                </form>

                <!-- Tabel Draft BOM Custom -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm mb-6">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Daftar Racikan (Draft)</h4>
                        <span id="total-item-badge-custom" class="bg-violet-100 text-violet-600 text-[10px] font-black px-2 py-0.5 rounded-full">0 BAHAN</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <tbody id="table-bom-custom" class="divide-y divide-slate-50"></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Footer Modal Custom -->
            <div class="p-6 border-t border-slate-100 bg-white flex justify-between items-center gap-3 shrink-0 rounded-b-3xl">
                <button type="button" onclick="hapusResepCustom()" 
                    class="px-5 py-3 rounded-xl font-bold text-rose-500 hover:bg-rose-50 transition-all text-xs flex items-center gap-2 border border-rose-200">
                    <i class="fa-solid fa-trash-can"></i> Hapus Semua Resep
                </button>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-resep-custom')" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-100 transition-all text-sm">Batal</button>
                    <button type="button" onclick="simpanResepCustom()" 
                        class="bg-violet-600 hover:bg-violet-700 text-white px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-violet-200 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Resep
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <!-- Script Tab Switcher -->
    <script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-100',
                                  'bg-violet-600', 'shadow-violet-100');
            btn.classList.add('text-slate-500', 'hover:bg-slate-100');
        });

        document.getElementById(`tab-${tab}`).classList.remove('hidden');
        const activeBtn = document.getElementById(`tab-btn-${tab}`);
        activeBtn.classList.remove('text-slate-500', 'hover:bg-slate-100');

        if (tab === 'produk') {
            activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md', 'shadow-blue-100');
        } else {
            activeBtn.classList.add('bg-violet-600', 'text-white', 'shadow-md', 'shadow-violet-100');
        }
    }
    </script>

    <!-- Script Produk Jadi (asli) -->
    <script src="ajax.js?v=<?= time() ?>"></script>
    <!-- Script Item Custom POS (baru) -->
    <script src="ajax_custom.js?v=<?= time() ?>"></script>
</body>
</html>