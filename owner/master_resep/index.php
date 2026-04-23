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
            <div class="mb-6 flex justify-between items-end">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Resep Produk (BOM)</h2>
                    <p class="text-sm text-secondary mt-1">Atur komposisi bahan baku untuk setiap 1 Pcs produk jadi.</p>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="p-4 text-center w-16">No</th>
                                <th class="p-4">Nama Produk</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4 text-center">Status Resep</th>
                                <th class="p-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-products" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="5" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-resep" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-resep')"></div>
        <div class="bg-surface w-full max-w-2xl rounded-3xl shadow-2xl z-10 transform transition-all flex flex-col max-h-[90vh] overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-mortar-pestle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Komposisi Resep</h3>
                        <p id="modal-product-name" class="text-xs text-primary font-bold mt-0.5 tracking-wide">Nama Produk</p>
                    </div>
                </div>
                <button onclick="closeModal('modal-resep')" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar bg-slate-50/30">
                
                <form id="formTambahBahan" class="mb-8 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                    <input type="hidden" id="bom_product_id" name="product_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Pilih Bahan Baku</label>
                            <select id="pilar_material_id" name="material_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-primary outline-none text-sm font-bold text-slate-700 bg-slate-50">
                                <option value="">-- Pilih Bahan --</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Takaran (1 Pcs)</label>
                            <div class="relative">
                                <input type="number" step="any" id="quantity" name="quantity_needed" required min="0.0001" class="w-full pl-4 pr-10 py-2.5 border border-slate-200 rounded-xl focus:border-primary outline-none text-sm font-black text-primary bg-slate-50" placeholder="0.00">
                                <button type="button" onclick="toggleKalkulator()" class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all" title="Gunakan Kalkulator Konversi">
                                    <i class="fa-solid fa-calculator text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Satuan</label>
                            <select id="unit_used" name="unit_used" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:border-primary outline-none text-sm font-bold text-slate-700 bg-slate-50">
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
                        <button type="submit" class="w-full bg-primary hover:bg-blue-700 text-white py-3 rounded-xl text-sm font-black transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-100">
                            <i class="fa-solid fa-save"></i> SIMPAN KE DAFTAR RESEP
                        </button>
                    </div>
                </form>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bahan Terdaftar (Untuk 1 Pcs)</h4>
                        <span id="total-item-badge" class="bg-primary/10 text-primary text-[10px] font-black px-2 py-0.5 rounded-full">0 BAHAN</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <tbody id="table-bom" class="divide-y divide-slate-50">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>