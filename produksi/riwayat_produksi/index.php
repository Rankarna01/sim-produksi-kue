<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Riwayat Produksi</h2>
                <p class="text-sm text-secondary mt-1">Daftar semua produksi berdasarkan Invoice. Anda dapat merevisi atau membatalkan data.</p>
            </div>

            <div class="bg-surface p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <form id="formFilter" class="flex flex-col sm:flex-row gap-4 items-end flex-wrap">
                    <div class="flex-1 w-full min-w-[140px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                    </div>
                    <div class="flex-1 w-full min-w-[140px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                    </div>
                    
                    <div class="flex-1 w-full min-w-[140px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Asal Dapur</label>
                        <select id="kitchen_id" name="kitchen_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                            <option value="">Semua Dapur</option>
                        </select>
                    </div>

                    <div class="flex-1 w-full min-w-[140px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Store Tujuan</label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                            <option value="">Semua Store</option>
                        </select>
                    </div>

                    <div class="flex-1 w-full min-w-[140px]">
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:border-primary outline-none">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending (Antrean)</option>
                            <option value="ditolak">Ditolak (Butuh Revisi)</option>
                            <option value="masuk_gudang">Selesai (Masuk Store)</option>
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
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                <th class="p-4 font-semibold w-16 text-center">No</th>
                                <th class="p-4 font-semibold">Tanggal & Waktu</th>
                                <th class="p-4 font-semibold">No. Invoice</th>
                                <th class="p-4 font-semibold">Asal Dapur</th>
                                <th class="p-4 font-semibold">Daftar Produk (Qty)</th>
                                <th class="p-4 font-semibold text-center">Total Pcs</th>
                                <th class="p-4 font-semibold text-center">Status</th>
                                <th class="p-4 font-semibold text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-history" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="8" class="p-8 text-center text-secondary">Memuat data...</td></tr>
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
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-xl z-10 transform transition-all flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl shrink-0">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-pen-to-square text-primary mr-2"></i> Revisi Invoice</h3>
                <button onclick="closeModal('modal-edit')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 flex-1 overflow-hidden flex flex-col">
                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-800 font-medium shrink-0">
                    Silakan ubah jumlah produk di bawah ini. Stok bahan baku akan dihitung ulang secara otomatis.
                </div>
                
                <form id="formEdit" class="flex flex-col flex-1 overflow-hidden">
                    <input type="hidden" id="edit_prod_id" name="prod_id">
                    <div id="edit-produk-list" class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3 mb-6">
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                        <button type="button" onclick="closeModal('modal-edit')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-pin-supervisor" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closePinModal()"></div>
        <div class="relative bg-[#0095ff] w-full max-w-[320px] rounded-2xl shadow-2xl z-10 overflow-hidden transform transition-all">
            <div class="p-4 flex justify-between items-center text-white border-b border-white/10">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-shield"></i>
                    <span class="font-bold text-xs uppercase tracking-widest">Otorisasi PIN</span>
                </div>
                <button onclick="closePinModal()" class="hover:bg-white/10 w-8 h-8 rounded-full flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="p-6 text-center">
                <h3 class="text-white font-bold text-xl mb-6 uppercase tracking-wider">PIN Supervisor</h3>
                
                <div class="bg-white/10 rounded-xl py-4 mb-6">
                    <input type="password" id="pin-display" readonly class="bg-transparent text-white text-center text-3xl font-black tracking-[0.5em] outline-none w-full border-none pointer-events-none" placeholder="******">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <?php for($i=1; $i<=9; $i++): ?>
                        <button onclick="pressPin('<?= $i ?>')" class="py-4 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xl transition-all active:scale-90"><?= $i ?></button>
                    <?php endfor; ?>
                    <button onclick="clearPin()" class="py-4 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xl transition-all"><i class="fa-solid fa-rotate-left"></i></button>
                    <button onclick="pressPin('0')" class="py-4 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xl active:scale-90">0</button>
                    <button onclick="backspacePin()" class="py-4 bg-white/10 hover:bg-white/20 text-white rounded-xl font-bold text-xl"><i class="fa-solid fa-delete-left"></i></button>
                </div>

                <button onclick="confirmCancelWithPin()" id="btn-verify-pin" class="w-full mt-6 bg-white text-[#0095ff] font-black py-4 rounded-xl hover:bg-slate-100 transition-all shadow-lg active:scale-95">
                    BUKA AKSES
                </button>
            </div>
        </div>
    </div>

    <input type="hidden" id="temp_prod_id">

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>