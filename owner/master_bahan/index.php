<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Bahan Baku</h2>
                    <p class="text-sm text-secondary mt-1">Kelola stok awal dan batas minimum bahan mentah dapur.</p>
                </div>
                <button onclick="openModal('modal-bahan'); resetForm();" class="bg-primary hover:opacity-90 text-surface px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Bahan
                </button>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold text-center w-16">No</th>
                                <th class="p-4 font-semibold">Kode</th>
                                <th class="p-4 font-semibold">Nama Bahan</th>
                                <th class="p-4 font-semibold text-center">Satuan</th>
                                <th class="p-4 font-semibold text-right">Stok Aktual</th>
                                <th class="p-4 font-semibold text-right">Min. Stok</th>
                                <th class="p-4 font-semibold text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="7" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-bahan" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-bahan')"></div>
        <div class="bg-surface w-full max-w-md rounded-2xl shadow-xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Bahan Baku</h3>
                <button onclick="closeModal('modal-bahan')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <form id="formBahan" class="space-y-4">
                    <input type="hidden" id="material_id" name="id">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kode Bahan <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface uppercase" placeholder="Contoh: TPG-01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Bahan Baku <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface" placeholder="Contoh: Tepung Terigu Segitiga Biru">
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                            <select id="unit" name="unit" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                                <option value="Gram">Gram (g)</option>
                                <option value="Kg">Kilogram (kg)</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Liter">Liter (L)</option>
                                <option value="Ml">Mililiter (ml)</option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Stok Awal</label>
                            <input type="number" step="0.01" id="stock" name="stock" value="0" min="0" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-slate-700 mb-1" title="Batas peringatan stok">Min. Stok</label>
                            <input type="number" step="0.01" id="min_stock" name="min_stock" value="0" min="0" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-bahan')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-surface bg-primary hover:opacity-90 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js"></script>
</body>
</html>