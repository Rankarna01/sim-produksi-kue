<?php
require_once '../../../config/auth.php';
checkPermission('manage_users'); // Uncomment jika slug permission-nya sudah kamu daftarkan
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
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen Pengguna (User)</h2>
                    <p class="text-sm text-slate-500 mt-1">Kelola akun staf gudang, atur *password*, dan tetapkan jabatannya.</p>
                </div>
                <button onclick="bukaModalTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Tambah User
                </button>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <div class="relative w-full max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fa-solid fa-search text-slate-400"></i>
                    </div>
                    <input type="text" id="search" placeholder="Cari nama atau username..." class="w-full pl-11 pr-4 py-2.5 border border-slate-300 rounded-xl focus:border-blue-600 outline-none text-sm transition-all bg-slate-50 focus:bg-white" onkeyup="cariData()">
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Nama Lengkap</th>
                                <th class="p-5">Username</th>
                                <th class="p-5">Jabatan (Role)</th>
                                <th class="p-5 text-center">Status</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="6" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination" class="mt-6 flex items-center justify-center gap-2"></div>

        </main>
    </div>

    <div id="modal-user" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-user')"></div>
        <div class="relative bg-white w-full max-w-2xl rounded-[2rem] shadow-2xl z-10 flex flex-col overflow-hidden">
            
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-black text-slate-800 flex items-center gap-2" id="modal-title">
                    <i class="fa-solid fa-user-plus text-blue-600"></i> Tambah User Baru
                </h3>
                <button onclick="closeModal('modal-user')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form id="form-user" class="p-6 sm:p-8 space-y-5">
                <input type="hidden" id="user_id" name="user_id">
                
                <div>
                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Cth: Budi Santoso" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-slate-50 focus:bg-white transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Username <span class="text-rose-500">*</span></label>
                        <input type="text" id="username" name="username" required placeholder="Cth: budi_gudang" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-blue-600 bg-slate-50 focus:bg-white transition-all shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Password <span class="text-rose-500" id="req-pass">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-slate-50 focus:bg-white transition-all shadow-sm">
                        <p class="text-[10px] text-slate-400 mt-1.5 font-medium" id="help-pass">Minimal 6 karakter.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Jabatan (Role) <span class="text-rose-500">*</span></label>
                        <select id="role" name="role" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-white transition-all shadow-sm">
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">Status Akun <span class="text-rose-500">*</span></label>
                        <select id="status" name="status" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-700 bg-white transition-all shadow-sm">
                            <option value="active">Aktif (Bisa Login)</option>
                            <option value="inactive">Non-Aktif (Diblokir)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                    <button type="button" onclick="closeModal('modal-user')" class="px-6 py-3 rounded-xl font-black text-slate-500 hover:bg-slate-100 transition-all text-xs uppercase tracking-widest">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                        <i class="fa-regular fa-floppy-disk"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>