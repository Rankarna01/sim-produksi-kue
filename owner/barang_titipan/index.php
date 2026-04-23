<?php
require_once '../../config/auth.php';
// Gunakan slug permission baru, atau karena Owner punya VIP bypass, ini akan langsung lolos
checkPermission('master_titipan'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar.php'; ?> <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight flex items-center gap-3">
                        <i class="fa-solid fa-store text-blue-600"></i> Barang Titipan (UMKM)
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola master data produk titipan, harga modal, harga jual, dan stok.</p>
                </div>
                <button onclick="bukaModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl text-sm font-black shadow-lg shadow-blue-200 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Barang Baru
                </button>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <div class="relative w-full max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-slate-400"></i>
                        </div>
                        <input type="text" id="search-data" placeholder="Cari nama barang atau UMKM..." class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm font-bold text-slate-700" onkeyup="cariData()">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 bg-white">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Informasi Produk</th>
                                <th class="p-5 text-right">Harga Modal</th>
                                <th class="p-5 text-right">Harga Jual</th>
                                <th class="p-5 text-center">Profit</th>
                                <th class="p-5 text-center">Sisa Stok</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-50 font-medium text-slate-600">
                            <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-form" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModal()"></div>
        <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-xl z-10 overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800" id="modal-title">Tambah Barang Titipan</h3>
                <button onclick="tutupModal()" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="formData" class="p-6 space-y-4">
                <input type="hidden" id="id_barang" name="id">
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Nama Barang Titipan</label>
                    <input type="text" id="nama_barang" name="nama_barang" required class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-700 bg-slate-50" placeholder="Contoh: Keripik Singkong Balado">
                </div>
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Nama Supplier / UMKM</label>
                    <input type="text" id="nama_umkm" name="nama_umkm" required class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-700 bg-slate-50" placeholder="Contoh: UMKM Ibu Sari">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Harga Modal (Rp)</label>
                        <input type="number" id="harga_modal" name="harga_modal" required min="0" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Harga Jual (Rp)</label>
                        <input type="number" id="harga_jual" name="harga_jual" required min="0" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:border-emerald-600 font-bold text-slate-700 bg-slate-50">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Stok Tersedia</label>
                    <input type="number" id="stok" name="stok" required min="0" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-black text-blue-600 text-lg bg-slate-50" placeholder="0">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="tutupModal()" class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 uppercase tracking-widest">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl text-xs font-black shadow-md uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>