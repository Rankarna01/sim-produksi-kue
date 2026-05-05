<?php
require_once '../../config/auth.php';
checkPermission('master_produk');
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
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Data Produk</h2>
                    <p class="text-sm text-secondary mt-1">Kelola daftar kue dan roti yang diproduksi beserta harga dan gambar untuk POS.</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <?php if(hasPermission('edit_master_produk')): ?>
                    <button onclick="openModal('modal-import')" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-file-csv"></i> Import CSV
                    </button>
                    <button onclick="openModal('modal-produk'); resetForm();" class="flex-1 sm:flex-none bg-primary hover:opacity-90 text-surface px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Tambah Produk
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold text-center w-16">No</th>
                                <th class="p-4 font-semibold text-center w-20">Gambar</th>
                                <th class="p-4 font-semibold">Kode</th>
                                <th class="p-4 font-semibold">Nama Produk</th>
                                <th class="p-4 font-semibold">Kategori</th>
                                <th class="p-4 font-semibold text-right">Harga Modal</th>
                                <th class="p-4 font-semibold text-right">Harga Jual (OFF / ON)</th>
                                <th class="p-4 font-semibold text-center">Stok Jadi</th>
                                <th class="p-4 font-semibold text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="9" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL FORM PRODUK -->
    <div id="modal-produk" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-produk')"></div>
        <div class="bg-surface w-full max-w-lg rounded-2xl shadow-xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center shrink-0">
                <h3 id="modal-title" class="text-lg font-bold text-slate-800">Tambah Produk Baru</h3>
                <button onclick="closeModal('modal-produk')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form id="formProduk" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="product_id" name="id">
                    <input type="hidden" id="old_image" name="old_image">
                    
                    <div class="flex flex-col items-center justify-center mb-4">
                        <img id="image_preview" src="../../assets/img/no-image.png" alt="Preview" class="w-32 h-32 object-cover rounded-2xl border-4 border-slate-100 shadow-sm mb-3 bg-slate-50">
                        <div class="w-full relative">
                            <label class="block text-sm font-medium text-slate-700 mb-1 text-center">Gambar Produk (Opsional)</label>
                            <input type="file" id="image" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" onchange="previewImage(event)">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kode Produk <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface uppercase" placeholder="Cth: RCK-01">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                            <select id="category" name="category" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                                <option value="Roti Manis">Roti Manis</option>
                                <option value="Roti Tawar">Roti Tawar</option>
                                <option value="Kue Kering">Kue Kering</option>
                                <option value="Bolu">Bolu</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface" placeholder="Contoh: Roti Coklat Keju">
                    </div>
                    
                    <!-- INPUT HARGA 3 KOLOM -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Modal (Rp)</label>
                            <input type="number" id="modal_price" name="modal_price" value="0" min="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface text-rose-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jual Off (Rp) <span class="text-danger">*</span></label>
                            <input type="number" id="price" name="price" value="0" min="0" required class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface text-emerald-600 font-bold">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jual On (Rp)</label>
                            <input type="number" id="online_price" name="online_price" value="0" min="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all bg-slate-50 focus:bg-surface text-blue-600 font-bold">
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-produk')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-surface bg-primary hover:opacity-90 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORT -->
    <div id="modal-import" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-import')"></div>
        <div class="bg-surface w-full max-w-md rounded-2xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <h3 class="text-lg font-bold text-slate-800">Import Data via Excel/CSV</h3>
                <button onclick="closeModal('modal-import')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="mb-5 p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-800">
                    <p class="font-semibold mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Cara Import:</p>
                    <ol class="list-decimal pl-5 space-y-1 mb-3 text-xs">
                        <li>Download template format di bawah.</li>
                        <li>Buka dengan Excel, isi data Anda.</li>
                        <li>Saat menyimpan, pilih tipe <strong>CSV (Comma delimited)</strong>.</li>
                        <li>Upload file CSV tersebut ke sini.</li>
                    </ol>
                    <a href="logic.php?action=download_template" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-800 underline">
                        <i class="fa-solid fa-download"></i> Download Template CSV
                    </a>
                </div>

                <form id="formImport" enctype="multipart/form-data">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih File CSV <span class="text-danger">*</span></label>
                    <input type="file" id="file_import" name="file_import" accept=".csv" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary hover:file:text-white file:transition-colors file:cursor-pointer border border-slate-300 rounded-xl p-1 mb-6 cursor-pointer bg-slate-50">
                    
                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" onclick="closeModal('modal-import')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" id="btn-import-submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-upload"></i> Proses Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <script>
        const canEdit = <?= hasPermission('edit_master_produk') ? 'true' : 'false' ?>;
        const canDelete = <?= hasPermission('hapus_master_produk') ? 'true' : 'false' ?>;
    </script>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?php echo time(); ?>"></script>
</body>
</html>