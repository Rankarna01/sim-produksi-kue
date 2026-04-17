<?php
require_once '../../../config/auth.php';
// checkRole(['admin', 'owner']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Inventory & Stok</h2>
                    <p class="text-sm text-secondary mt-1">Kelola data master bahan baku, cek sisa stok, dan pantau tanggal kadaluarsa.</p>
                </div>
                
                <div class="flex flex-wrap gap-2 items-center">
                    <a href="../kategori/" class="bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-tags text-slate-400"></i> Kelola Kategori
                    </a>
                    <a href="../satuan/" class="bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-weight-scale text-slate-400"></i> Kelola Satuan
                    </a>
                    <a href="../lokasi/" class="bg-white hover:bg-slate-50 text-slate-600 border border-slate-200 px-3 py-2 rounded-xl text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-location-dot text-slate-400"></i> Kelola Lokasi
                    </a>
                    <button onclick="openModal('modal-inventory'); resetForm();" class="bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2 ml-2">
                        <i class="fa-solid fa-plus"></i> Tambah Barang
                    </button>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50">
                    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-4">
                        <div class="flex gap-2 bg-slate-200/50 p-1 rounded-xl">
                            <button onclick="switchTab('active')" id="tab-active" class="px-4 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition-all">Aktif</button>
                            <button onclick="switchTab('inactive')" id="tab-inactive" class="px-4 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-700 transition-all">Non-Aktif / Arsip</button>
                            <button onclick="switchTab('all')" id="tab-all" class="px-4 py-2 rounded-lg text-sm font-bold text-slate-500 hover:text-slate-700 transition-all">Semua</button>
                        </div>
                    </div>
                    
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search" placeholder="Cari barang berdasarkan nama atau barcode/ID..." class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm bg-white font-medium" onkeyup="cariData()">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                <th class="p-4 font-bold">ID / Barcode</th>
                                <th class="p-4 font-bold">Nama Barang</th>
                                <th class="p-4 font-bold">Lokasi</th>
                                <th class="p-4 font-bold text-center">Stok</th>
                                <th class="p-4 font-bold">Kadaluarsa</th>
                                <th class="p-4 font-bold text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="6" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50"></div>
            </div>

        </main>
    </div>

    <div id="modal-inventory" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeModal('modal-inventory')"></div>
        <div class="relative bg-surface w-full max-w-2xl rounded-3xl shadow-2xl z-10 transform transition-all flex flex-col max-h-[95vh] overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                <h3 id="modal-title" class="text-xl font-bold text-slate-800">Tambah Master Barang</h3>
                <button onclick="closeModal('modal-inventory')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form id="formInventory" class="space-y-5">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">ID / Barcode <span class="text-danger">*</span></label>
                            <input type="text" id="sku_code" name="sku_code" placeholder="Cth: BRG-001" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all font-mono font-bold text-sm bg-slate-50 focus:bg-surface">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Status Barang</label>
                            <select id="status" name="status" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm font-bold bg-slate-50 focus:bg-surface">
                                <option value="active">Aktif (Digunakan)</option>
                                <option value="inactive">Non-Aktif (Arsip)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Nama Bahan Baku <span class="text-danger">*</span></label>
                        <input type="text" id="material_name" name="material_name" placeholder="Cth: Coklat Batang Elmer" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface font-semibold">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Kategori <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface">
                                <option value="">-- Pilih Kategori --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Satuan Ukur <span class="text-danger">*</span></label>
                            <select id="unit" name="unit" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface">
                                <option value="">-- Pilih Satuan --</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Lokasi Rak</label>
                            <select id="rack_id" name="rack_id" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface">
                                <option value="">-- Tidak Ada / Kosong --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tgl. Kadaluarsa</label>
                            <input type="date" id="expiry_date" name="expiry_date" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 border-t border-slate-100 pt-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Stok Awal Fisik <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.01" id="stock" name="stock" value="0" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface font-black text-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Batas Stok Menipis <span class="text-danger">*</span></label>
                            <div class="relative">
                                <input type="number" step="0.01" id="min_stock" name="min_stock" value="5" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none transition-all text-sm bg-slate-50 focus:bg-surface font-black text-amber-500">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-inventory')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-md">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Data Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>