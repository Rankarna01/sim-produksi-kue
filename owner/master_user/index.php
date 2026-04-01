<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">
    <?php include '../../components/sidebar.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Akses & Karyawan</h2>
                <p class="text-sm text-secondary mt-1">Kelola akun Login Aplikasi dan daftar nama Karyawan Dapur.</p>
            </div>

            <div class="flex border-b border-slate-200 mb-6 gap-6">
                <button onclick="switchTab('tab-akun')" id="btn-tab-akun" class="pb-3 text-sm font-bold border-b-2 border-primary text-primary transition-colors">
                    <i class="fa-solid fa-users-gear mr-1"></i> Akun Login Sistem
                </button>
                <button onclick="switchTab('tab-karyawan')" id="btn-tab-karyawan" class="pb-3 text-sm font-bold border-b-2 border-transparent text-secondary hover:text-slate-700 transition-colors">
                    <i class="fa-solid fa-users-viewfinder mr-1"></i> Daftar Karyawan Dapur
                </button>
            </div>

            <div id="tab-akun" class="block">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-700">Tabel Akun Sistem</h3>
                    <button onclick="openModal('modal-user'); resetFormUser();" class="bg-primary hover:opacity-90 text-surface px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Tambah Akun
                    </button>
                </div>
                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-semibold text-center w-16">No</th>
                                    <th class="p-4 font-semibold">Nama Instansi/Pengguna</th>
                                    <th class="p-4 font-semibold">Username Login</th>
                                    <th class="p-4 font-semibold text-center">Role / Hak Akses</th>
                                    <th class="p-4 font-semibold text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-user" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="5" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-karyawan" class="hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-700">Tabel Pegawai Produksi</h3>
                    <button onclick="openModal('modal-karyawan'); resetFormKaryawan();" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-user-tag"></i> Tambah Pegawai
                    </button>
                </div>
                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="mb-4 p-4 bg-blue-50/50 border-b border-blue-100 text-xs text-blue-800 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-blue-600 text-lg mt-0.5"></i>
                        <p>Nama-nama yang terdaftar di sini <strong>tidak bisa digunakan untuk login</strong>, melainkan hanya akan muncul sebagai pilihan di dropdown "Petugas" saat penginputan produksi dan pencatatan barang expired di Dapur.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[400px]">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-sm text-secondary uppercase tracking-wider">
                                    <th class="p-4 font-semibold text-center w-16">No</th>
                                    <th class="p-4 font-semibold">Nama Lengkap Karyawan</th>
                                    <th class="p-4 font-semibold">Tgl Didaftarkan</th>
                                    <th class="p-4 font-semibold text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="table-karyawan" class="text-sm divide-y divide-slate-100">
                                <tr><td colspan="4" class="p-8 text-center text-secondary">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <div id="modal-user" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-user')"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-3xl">
                <h3 id="modal-title-user" class="text-lg font-bold text-slate-800">Tambah Akun Sistem</h3>
                <button onclick="closeModal('modal-user')" class="text-secondary hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="formUser" class="space-y-4">
                    <input type="hidden" id="user_id" name="id">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Instansi/Pengguna <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Cth: Dapur Utama" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Username Login <span class="text-danger">*</span></label>
                        <input type="text" id="username_input" name="username" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface lowercase">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input type="password" id="password_input" name="password" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface" placeholder="Kosongkan jika tidak ingin diubah">
                        <p class="text-[10px] text-secondary mt-1" id="password_help">Wajib diisi untuk akun baru.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Hak Akses (Role) <span class="text-danger">*</span></label>
                        <select id="role_input" name="role" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary outline-none transition-all bg-slate-50 focus:bg-surface">
                            <option value="produksi">Terminal Produksi (Sistem Dapur)</option>
                            <option value="admin">Admin Gudang (Scanner)</option>
                            <option value="owner">Owner / Pemilik</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-user')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary hover:bg-blue-700 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modal-karyawan" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-karyawan')"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-xl z-10 transform transition-all flex flex-col">
            <div class="p-6 border-b border-indigo-100 flex justify-between items-center bg-indigo-50 rounded-t-3xl">
                <h3 id="modal-title-karyawan" class="text-lg font-bold text-indigo-900">Tambah Karyawan Dapur</h3>
                <button onclick="closeModal('modal-karyawan')" class="text-indigo-400 hover:text-danger transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="formKaryawan" class="space-y-4">
                    <input type="hidden" id="emp_id" name="id">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Karyawan <span class="text-danger">*</span></label>
                        <input type="text" id="emp_name" name="name" placeholder="Cth: Budi Santoso" required class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:border-indigo-500 outline-none transition-all font-semibold text-slate-800">
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-karyawan')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-sm">
                            <i class="fa-solid fa-save mr-1"></i> Simpan
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