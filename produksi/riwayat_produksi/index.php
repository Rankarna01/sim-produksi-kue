<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Riwayat Produksi</h2>
                <p class="text-sm text-secondary mt-1">Daftar semua produk yang diproduksi. Anda dapat merevisi data yang Ditolak oleh Admin.</p>
            </div>

            <div class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending (Antrean)</option>
                            <option value="ditolak">Ditolak (Butuh Revisi)</option>
                            <option value="masuk_gudang">Selesai (Masuk Gudang)</option>
                            <option value="expired">Expired / Rusak</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto flex gap-2">
                        <button type="submit" class="flex-1 bg-primary hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i> Filter
                        </button>
                        <button type="button" onclick="resetFilter()" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2.5 rounded-xl font-bold transition-all">
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold w-16 text-center">No</th>
                                <th class="p-4 font-semibold">Tanggal & Waktu</th>
                                <th class="p-4 font-semibold">No. Invoice</th>
                                <th class="p-4 font-semibold">Produk</th>
                                <th class="p-4 font-semibold text-center">Jumlah</th>
                                <th class="p-4 font-semibold text-center">Status</th>
                                <th class="p-4 font-semibold text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-history" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="7" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div id="pagination" class="p-4 border-t border-slate-100 flex items-center justify-center gap-2 bg-slate-50">
                </div>
            </div>
        </main>
    </div>

    <div id="modal-edit" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-edit')"></div>
        <div class="relative bg-surface w-full max-w-sm rounded-3xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-pen-to-square text-primary mr-2"></i> Revisi Produksi</h3>
                <button onclick="closeModal('modal-edit')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800 font-medium">
                    Struk fisik lama Anda masih berlaku. Stok bahan baku akan dihitung ulang secara otomatis jika merubah jumlah.
                </div>
                <form id="formEdit" class="space-y-4">
                    <input type="hidden" id="edit_prod_id" name="prod_id">
                    <input type="hidden" id="edit_detail_id" name="detail_id">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Produk (Tidak bisa diubah)</label>
                        <input type="text" id="edit_produk" readonly class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-100 text-slate-600 font-bold outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Jumlah Aktual (Pcs)</label>
                        <input type="number" id="edit_qty" name="quantity" required min="1" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:border-primary outline-none font-black text-primary text-2xl text-center">
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-edit')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>