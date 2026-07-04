<?php
require_once '../../config/auth.php';
checkPermission('master_user');
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
                    <h2 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-cash-register text-emerald-600"></i> Manajemen Kasir Tiap Outlet
                    </h2>
                    <p class="text-sm text-secondary mt-1">Kelola akun login kasir POS dan atur penugasan di masing-masing cabang Store / Gudang.</p>
                </div>
                
                <button onclick="openModal('modal-kasir'); resetForm();" class="bg-emerald-600 hover:bg-emerald-700 text-surface px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Tambah Kasir Outlet
                </button>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-xs text-slate-500 uppercase tracking-wider font-bold">
                                <th class="p-4 text-center w-16">No</th>
                                <th class="p-4">Nama Pegawai</th>
                                <th class="p-4">Username Login</th>
                                <th class="p-4">Jabatan</th>
                                <th class="p-4">Penugasan Outlet (Store)</th>
                                <th class="p-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-sm divide-y divide-slate-100">
                            <tr><td colspan="6" class="p-8 text-center text-secondary">Memuat data kasir...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-kasir" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-kasir')"></div>
        <div class="bg-surface w-full max-w-md rounded-2xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-emerald-600/5 rounded-t-2xl">
                <h3 id="modal-title" class="text-lg font-bold text-emerald-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-tag"></i> Tambah Kasir Outlet
                </h3>
                <button onclick="closeModal('modal-kasir')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <form id="formKasir" class="space-y-4">
                    <input type="hidden" id="user_id" name="id">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-slate-50 focus:bg-surface" placeholder="Contoh: Rina Kasir Barat">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Username Login <span class="text-danger">*</span></label>
                            <input type="text" id="username" name="username" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-slate-50 focus:bg-surface lowercase" placeholder="rinabar">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Role Jabatan <span class="text-danger">*</span></label>
                            <select id="role_id" name="role_id" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-slate-50 focus:bg-surface">
                                <option value="2">Kasir</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password Login <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-slate-50 focus:bg-surface" placeholder="••••••••">
                        <p id="pass-hint" class="text-xs text-amber-600 mt-1 hidden"><i class="fa-solid fa-circle-info"></i> Kosongkan jika tidak ingin mengubah password akun ini.</p>
                    </div>

                    <div class="pt-2">
                        <label class="block text-sm font-bold text-slate-800 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-store text-emerald-600"></i> Penugasan Outlet (Store Tujuan)
                        </label>
                        <select id="warehouse_id" name="warehouse_id" class="w-full px-4 py-2.5 border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all bg-emerald-50/30 focus:bg-surface font-semibold text-slate-700">
                            <option value="">🌐 Semua Store (Global Akses)</option>
                        </select>
                        <p class="text-xs text-secondary mt-1">Jika dipilih outlet tertentu, kasir hanya bisa memproses transaksi dan melihat stok di outlet tersebut.</p>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-kasir')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-surface bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?php echo time(); ?>"></script>
</body>
</html>
