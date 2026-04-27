<?php
require_once '../../config/auth.php';
// checkPermission('trx_keluar_titipan');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">
    <?php include '../../components/sidebar_produksi.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Produk Keluar Titipan (Expired/Rusak)</h2>
                    <p class="text-sm text-slate-500 mt-1">Catat barang titipan UMKM yang ditarik, rusak, atau kadaluarsa.</p>
                </div>
                <button onclick="openModalTambah()" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-rose-200 flex items-center gap-2">
                    <i class="fa-solid fa-minus-circle"></i> Catat Produk Keluar
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[900px]">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                <th class="p-5 text-center w-12">No</th>
                                <th class="p-5">Waktu Tarik</th>
                                <th class="p-5">ID Penarikan</th>
                                <th class="p-5">Produk UMKM</th>
                                <th class="p-5 text-center">Jumlah</th>
                                <th class="p-5 text-center">Alasan</th>
                                <th class="p-5">Petugas</th>
                            </tr>
                        </thead>
                        <tbody id="table-data" class="divide-y divide-slate-100 font-medium text-slate-600">
                            <tr><td colspan="7" class="p-10 text-center"><i class="fa-solid fa-circle-notch fa-spin text-rose-600 text-2xl"></i></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="pagination" class="mt-6 flex items-center justify-center gap-2 pb-10"></div>
        </main>
    </div>

    <div id="modal-form" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-form')"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl z-10 flex flex-col max-h-[90vh] overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-black text-slate-800"><i class="fa-solid fa-box-open text-rose-600 mr-2"></i> Tarik Barang Titipan</h3>
                <button onclick="closeModal('modal-form')" class="text-slate-400 hover:text-rose-500 w-8 h-8 rounded-full hover:bg-rose-50 flex items-center justify-center transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <form id="formData" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-widest">Pilih Barang <span class="text-rose-500">*</span></label>
                        <select id="titipan_id" name="titipan_id" required onchange="setMaksStok()" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none font-bold text-slate-700 bg-slate-50">
                            <option value="">-- Memuat Barang --</option>
                        </select>
                        <p id="stok_info" class="text-[10px] text-blue-600 font-bold mt-1 hidden">Stok Tersedia: <span id="stok_max_val">0</span> Pcs</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-widest">Jumlah Tarik <span class="text-rose-500">*</span></label>
                            <input type="number" id="qty" name="qty" required min="1" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none font-black text-rose-600 bg-slate-50" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-widest">Alasan <span class="text-rose-500">*</span></label>
                            <select id="reason" name="reason" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none font-bold text-slate-700 bg-slate-50">
                                <option value="Expired">Expired</option>
                                <option value="Rusak">Rusak</option>
                                <option value="Diretur UMKM">Diretur UMKM</option>
                                <option value="Konsumsi Internal">Konsumsi Internal</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1 uppercase tracking-widest">Catatan / Detail Tambahan</label>
                        <input type="text" id="notes" name="notes" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-rose-600 outline-none text-sm bg-slate-50" placeholder="Opsional...">
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="closeModal('modal-form')" class="flex-1 py-3 text-xs font-bold uppercase tracking-widest text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button type="submit" class="flex-1 py-3 text-xs font-black uppercase tracking-widest text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all shadow-md shadow-rose-200">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>