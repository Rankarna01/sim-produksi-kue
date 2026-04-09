<?php
require_once '../../config/auth.php';
checkPermission('master_resep');
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
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Resep Produk (BOM)</h2>
                <p class="text-sm text-secondary mt-1">Atur komposisi bahan baku untuk setiap 1 Pcs produk jadi.</p>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold text-center w-16">No</th>
                                <th class="p-4 font-semibold">Nama Produk</th>
                                <th class="p-4 font-semibold">Kategori</th>
                                <th class="p-4 font-semibold text-center">Status Resep</th>
                                <th class="p-4 font-semibold text-center w-32">Aksi</th>
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

    <div id="modal-resep" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-resep')"></div>
        <div class="bg-surface w-full max-w-2xl rounded-2xl shadow-xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-background rounded-t-2xl">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Komposisi Resep</h3>
                    <p id="modal-product-name" class="text-sm text-primary font-semibold mt-1">Nama Produk</p>
                </div>
                <button onclick="closeModal('modal-resep')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <form id="formTambahBahan" class="flex flex-wrap gap-3 items-end mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <input type="hidden" id="bom_product_id" name="product_id">
                    
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Pilih Bahan Baku</label>
                        <select id="material_id" name="material_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary outline-none text-sm">
                            <option value="">-- Memuat Bahan --</option>
                        </select>
                    </div>
                    
                    <div class="w-24">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Jumlah</label>
                        <input type="number" step="0.01" id="quantity" name="quantity_needed" required min="0.01" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary outline-none text-sm" placeholder="100">
                    </div>

                    <div class="w-28">
                        <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Satuan</label>
                        <select id="unit_used" name="unit_used" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary outline-none text-sm font-semibold text-slate-700">
                            <option value="">Memuat...</option>
                        </select>
                    </div>

                    <button type="submit" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 h-[38px]">
                        <i class="fa-solid fa-plus"></i> Tambah
                    </button>
                </form>

                <h4 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-100 pb-2">Bahan Terdaftar (Untuk 1 Pcs)</h4>
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-secondary">
                            <tr>
                                <th class="py-2 px-4 font-medium">Nama Bahan</th>
                                <th class="py-2 px-4 font-medium text-right">Takaran</th>
                                <th class="py-2 px-4 font-medium text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-bom" class="divide-y divide-slate-100">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>