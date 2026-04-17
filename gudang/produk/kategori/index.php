<?php
require_once '../../../config/auth.php';
// checkRole(['admin', 'gudang']); 
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
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Kategori Bahan Baku</h2>
                    <p class="text-sm text-secondary mt-1">Kelola pengelompokan jenis bahan baku/material untuk mempermudah pencarian stok.</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="openModal('modal-kategori'); resetForm();" class="bg-primary hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Kategori
                    </button>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search" placeholder="Cari nama kategori..." class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm bg-white" onkeyup="cariData()">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                <th class="p-4 font-bold w-16 text-center">No</th>
                                <th class="p-4 font-bold">Nama Kategori Bahan</th>
                                <th class="p-4 font-bold">Deskripsi</th>
                                <th class="p-4 font-bold text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="4" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50"></div>
            </div>

        </main>
    </div>

    <div id="modal-kategori" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-kategori')"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Kategori</h3>
                <button onclick="closeModal('modal-kategori')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="formKategori" class="space-y-4">
                    <input type="hidden" id="id" name="id">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Cth: Tepung, Kemasan, Dairy" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <textarea id="description" name="description" rows="3" placeholder="Keterangan singkat..." class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface custom-scrollbar"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-kategori')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>