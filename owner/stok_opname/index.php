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
                                <th class="p-3 font-bold">Bahan Baku</th>
                                <th class="p-3 font-bold text-right">Stok Sistem</th>
                                <th class="p-3 font-bold text-right text-indigo-600">Stok Fisik</th>
                                <th class="p-3 font-bold text-center">Selisih</th>
                                <th class="p-3 font-bold">Alasan / Catatan</th>
                                <th class="p-3 font-bold text-center">Petugas (User)</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="8" class="p-8 text-center text-secondary">Memuat riwayat opname...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <div id="modal-opname" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-opname')"></div>
        <div class="relative bg-surface w-full max-w-lg rounded-3xl shadow-xl z-10 transform transition-all flex flex-col max-h-[95vh] overflow-hidden">
            <div class="p-5 border-b border-emerald-100 flex justify-between items-center bg-emerald-50 rounded-t-3xl">
                <h3 class="text-lg font-bold text-emerald-900"><i class="fa-solid fa-scale-balanced mr-2"></i> Form Stok Opname</h3>
                <button onclick="closeModal('modal-opname')" class="text-emerald-400 hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <form id="formOpname" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Pilih Bahan Baku <span class="text-danger">*</span></label>
                        <select id="material_id" name="material_id" required onchange="handleMaterialChange()" class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 outline-none font-bold text-slate-700 text-sm bg-slate-50 focus:bg-white transition-colors">
                            <option value="">-- Memuat Bahan Baku --</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Stok di Sistem</p>
                            <h3 class="text-xl font-black text-slate-800" id="info_system_stock">0</h3>
                            <p class="text-xs font-bold text-slate-500 mt-1 unit-label">Satuan</p>
                            <input type="hidden" id="system_stock" name="system_stock" value="0">
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-200 text-center">
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Stok Fisik (Asli) <span class="text-danger">*</span></p>
                            <input type="number" step="0.01" id="actual_stock" name="actual_stock" required min="0" oninput="calculateDifference()" class="w-full text-center text-xl font-black text-indigo-700 bg-white border border-indigo-200 rounded-lg py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="0">
                            <p class="text-xs font-bold text-indigo-400 mt-1 unit-label">Satuan</p>
                        </div>
                    </div>

                    <div class="text-center p-3 rounded-xl border-2 border-dashed border-slate-200" id="diff-container">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Selisih Opname</p>
                        <h4 class="text-lg font-black text-slate-700" id="info_difference">-</h4>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-wider">Alasan Penyesuaian <span class="text-danger">*</span></label>
                        <input type="text" id="reason" name="reason" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-emerald-500 outline-none text-sm font-medium placeholder:text-slate-300" placeholder="Contoh: Ada tepung tumpah 2 Kg / Salah timbang kemaren">
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 mt-6">
                        <button type="button" onclick="closeModal('modal-opname')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" id="btn-save" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan Penyesuaian
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