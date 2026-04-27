<?php
require_once '../../../config/auth.php';
checkPermission('manage_roles');
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
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Manajemen Jabatan & Akses</h2>
                    <p class="text-sm text-slate-500 mt-1">Buat jabatan baru dan atur hak akses menu secara spesifik untuk masing-masing peran.</p>
                </div>
                <button onclick="bukaModalTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved"></i> Tambah Jabatan
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-xs font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 w-16 text-center">No</th>
                                <th class="p-5">Nama Jabatan</th>
                                <th class="p-5">Kode Sistem (Slug)</th>
                                <th class="p-5">Total Akses Menu</th>
                                <th class="p-5 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr>
                                <td colspan="5" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-2xl"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <div id="modal-role" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4 py-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-role')"></div>
        <div class="relative bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl z-10 flex flex-col overflow-hidden max-h-full">

            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/80">
                <h3 class="text-xl font-black text-slate-800 flex items-center gap-2" id="modal-title">
                    <i class="fa-solid fa-shield-halved text-blue-600"></i> Tambah Jabatan Baru
                </h3>
                <button onclick="closeModal('modal-role')" class="text-slate-400 hover:text-rose-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-rose-50"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <form id="form-role" class="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                <div class="p-6 sm:p-8 space-y-8 flex-1">
                    <input type="hidden" id="role_id" name="role_id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-blue-50/30 p-5 rounded-2xl border border-blue-100">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Nama Jabatan <span class="text-rose-500">*</span></label>
                            <input type="text" id="role_name" name="role_name" required placeholder="Cth: Supervisor Gudang" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-bold text-slate-800 bg-white transition-all shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Kode Slug Sistem <span class="text-rose-500">*</span></label>
                            <input type="text" id="role_slug" name="role_slug" required placeholder="Cth: spv_gudang" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-mono text-sm font-bold text-blue-600 bg-white transition-all shadow-sm lowercase">
                            <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Tanpa spasi. Gunakan underscore (_).</p>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-black text-slate-800 text-lg mb-1"><i class="fa-solid fa-list-check text-blue-600 mr-2"></i>Pengaturan Hak Akses Menu</h4>
                        <p class="text-xs text-slate-500 mb-6">* Centang menu mana saja yang boleh dibuka dan dikelola oleh jabatan ini.</p>

                        <div class="space-y-8">

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-house mr-2 text-slate-400"></i> Menu Utama & Persetujuan</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="dashboard" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Dashboard</span></label>

                                    <label class="flex items-center gap-3 p-3 border border-blue-200 bg-blue-50/30 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-100 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-black text-blue-800">Buka Menu Persetujuan</span></label>

                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_po" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Approve PO</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_pr" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Approve Request (PR)</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_masuk_manual" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Approve Barang Masuk</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_keluar_manual" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Approve Barang Keluar</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_izin_cetak" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Approve Izin Cetak</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition-all"><input type="checkbox" name="permissions[]" value="persetujuan_histori" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"><span class="text-xs font-bold text-slate-700">Lihat Master Histori</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all">
                                        <input type="checkbox" name="permissions[]" value="persetujuan_retur_po" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                                        <span class="text-xs font-bold text-slate-700">Persetujuan Retur PO</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-cube mr-2 text-indigo-400"></i> Produk & Master</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="master_inventory" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Inventory & Stok</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="master_kategori" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Master Kategori</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="master_satuan" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Master Satuan</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="master_lokasi" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Master Lokasi Rak</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="monitoring_rak" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Monitoring Rak & Stok</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_barang_masuk" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Barang Masuk</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_barang_keluar" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Barang Keluar</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition-all"><input type="checkbox" name="permissions[]" value="cetak_barcode" class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"><span class="text-xs font-bold text-slate-700">Cetak Barcode</span></label>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-clipboard-check mr-2 text-purple-400"></i> Stok Opname</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-all"><input type="checkbox" name="permissions[]" value="data_opname" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"><span class="text-xs font-bold text-slate-700">Data Opname (Utama)</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-all"><input type="checkbox" name="permissions[]" value="otorisasi_opname" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"><span class="text-xs font-bold text-slate-700">Otorisasi Akses</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-purple-400 hover:bg-purple-50/50 transition-all"><input type="checkbox" name="permissions[]" value="scanner_opname" class="w-4 h-4 text-purple-600 rounded focus:ring-purple-500"><span class="text-xs font-bold text-slate-700">Scanner Audit</span></label>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-right-left mr-2 text-emerald-400"></i> Transaksi</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_permintaan_dapur" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"><span class="text-xs font-bold text-slate-700">Permintaan Dapur</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_permintaan_barang" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"><span class="text-xs font-bold text-slate-700">Permintaan Barang</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_po" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"><span class="text-xs font-bold text-slate-700">Purchase Order (PO)</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all">
                                        <input type="checkbox" name="permissions[]" value="lap_retur_po" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                                        <span class="text-xs font-bold text-slate-700">Laporan Retur PO</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_pembayaran" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"><span class="text-xs font-bold text-slate-700">Pembayaran PO</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50 transition-all"><input type="checkbox" name="permissions[]" value="trx_supplier" class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500"><span class="text-xs font-bold text-slate-700">Supplier & Harga</span></label>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-file-lines mr-2 text-rose-400"></i> Laporan Lengkap</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_barang_masuk" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Barang Masuk</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_barang_keluar" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Barang Keluar</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_po" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Purchase Order</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_pembayaran_po" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Pembayaran PO</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_stok_opname" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Stok Opname</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_kartu_stok" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Kartu Stok (Pergerakan)</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_stok_menipis" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Stok Menipis</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_stok_terbanyak" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Stok Terbanyak</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_perbandingan_harga" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Perbandingan Harga</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-rose-400 hover:bg-rose-50/50 transition-all"><input type="checkbox" name="permissions[]" value="lap_supplier" class="w-4 h-4 text-rose-600 rounded focus:ring-rose-500"><span class="text-xs font-bold text-slate-700">Laporan Supplier</span></label>
                                </div>
                            </div>

                            <div>
                                <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest mb-3 border-b border-slate-200 pb-2"><i class="fa-solid fa-gears mr-2 text-slate-400"></i> Pengaturan & Setting</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    <!-- <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition-all"><input type="checkbox" name="permissions[]" value="pengaturan_karyawan" class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500"><span class="text-xs font-bold text-slate-700">Data Karyawan</span></label> -->
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition-all"><input type="checkbox" name="permissions[]" value="pengaturan_pembayaran" class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500"><span class="text-xs font-bold text-slate-700">Master Pembayaran</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition-all"><input type="checkbox" name="permissions[]" value="manage_users" class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500"><span class="text-xs font-bold text-slate-700">User Management</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition-all"><input type="checkbox" name="permissions[]" value="manage_roles" class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500"><span class="text-xs font-bold text-slate-700">Manajemen Role</span></label>
                                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-slate-400 hover:bg-slate-100 transition-all"><input type="checkbox" name="permissions[]" value="pengaturan_profil" class="w-4 h-4 text-slate-600 rounded focus:ring-slate-500"><span class="text-xs font-bold text-slate-700">Profil Toko</span></label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-200 bg-slate-50 flex justify-end gap-3 mt-auto shrink-0">
                    <button type="button" onclick="closeModal('modal-role')" class="px-6 py-3 rounded-xl font-black text-slate-500 hover:bg-slate-200 transition-all text-xs uppercase tracking-widest">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black uppercase tracking-widest text-xs transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Simpan Jabatan
                    </button>
                </div>
            </form>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid white;
        }
    </style>

    <?php include '../../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>

</html>