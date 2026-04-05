<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full">
            
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Stok Opname Bahan</h2>
                    <p class="text-sm text-secondary mt-1">Sesuaikan stok fisik di dapur dengan stok yang tercatat di sistem komputer.</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button onclick="openModal('modal-opname'); resetForm();" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-scale-balanced"></i> Catat Opname Baru
                    </button>
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-history text-slate-500"></i>
                    <h3 class="font-bold text-slate-700">Riwayat Penyesuaian Stok</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-white border-b border-slate-200 text-xs text-secondary uppercase tracking-wider">
                                <th class="p-3 font-bold w-12 text-center">No</th>
                                <th class="p-3 font-bold">Waktu Opname</th>
                                <th class="p-3 font-bold">No. Dokumen</th>
                                <th class="p-3 font-bold">Bahan Baku</th>
                                <th class="p-3 font-bold text-right">Stok Sistem</th>
                                <th class="p-3 font-bold text-right text-indigo-600">Stok Fisik</th>
                                <th class="p-3 font-bold text-center">Selisih</th>
                                <th class="p-3 font-bold">Catatan</th>
                                <th class="p-3 font-bold text-center">Petugas</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="9" class="p-8 text-center text-secondary">Memuat riwayat opname...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <div id="modal-opname" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-opname')"></div>
        <div class="relative bg-surface w-full max-w-2xl rounded-3xl shadow-xl z-10 transform transition-all flex flex-col max-h-[95vh] overflow-hidden">
            <div class="p-5 border-b border-emerald-100 flex justify-between items-center bg-emerald-50 rounded-t-3xl shrink-0">
                <h3 class="text-lg font-bold text-emerald-900"><i class="fa-solid fa-clipboard-list mr-2"></i> Dokumen Stok Opname</h3>
                <button onclick="closeModal('modal-opname')" class="text-emerald-400 hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <form id="formOpname" class="space-y-6">
                    
                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Cari & Tambah Bahan <span class="text-danger">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <input type="text" id="search_material" placeholder="Ketik nama atau kode bahan baku..." class="w-full pl-11 pr-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 outline-none text-sm font-medium bg-slate-50 focus:bg-white transition-colors" autocomplete="off">
                        </div>
                        <ul id="suggest_box" class="absolute z-20 w-full bg-white border border-slate-200 shadow-xl rounded-xl mt-1 hidden max-h-48 overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                        </ul>
                    </div>

                    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                        <div class="flex justify-between items-center mb-3 border-b border-slate-200 pb-2">
                            <h4 class="text-sm font-bold text-slate-700">Daftar Bahan Diopname</h4>
                            <span id="item_count" class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-xs font-bold">0 Item</span>
                        </div>
                        
                        <div id="opname_list" class="space-y-3">
                            <div id="empty_state" class="text-center py-6 text-secondary text-sm font-medium">
                                <i class="fa-solid fa-box-open text-3xl mb-2 text-slate-300 block"></i>
                                Belum ada bahan baku yang dipilih.<br>Silakan cari dan pilih pada kolom di atas.
                            </div>
                            </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Catatan Penyesuaian (Opsional)</label>
                        <input type="text" id="reason" name="reason" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 outline-none text-sm font-medium placeholder:text-slate-300" placeholder="Contoh: Audit stok bulanan / Pembuangan bahan rusak">
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
                        <button type="button" onclick="closeModal('modal-opname')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" id="btn-save" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Post Opname
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