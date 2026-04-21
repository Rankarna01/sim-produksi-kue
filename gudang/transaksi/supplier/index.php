<?php
require_once '../../../config/auth.php';
checkPermission('trx_supplier');
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
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Supplier & Perbandingan Harga</h2>
                    <p class="text-sm text-slate-500 mt-1">Analisis harga beli terbaik dan kelola data master supplier Anda.</p>
                </div>
                <button onclick="openModal('modal-supplier'); resetForm();" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-200 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Supplier
                </button>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 mb-8 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b border-slate-100">
                            <tr>
                                <th class="p-5 font-black text-slate-800 w-1/3">Nama Barang</th>
                                <th class="p-5 font-black text-slate-800 w-1/4">Harga Terbaik</th>
                                <th class="p-5 font-black text-slate-800">Daftar Penawaran Supplier</th>
                            </tr>
                        </thead>
                        <tbody id="comparison-list" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="3" class="p-10 text-center text-slate-400 font-bold italic">
                                    <i class="fa-solid fa-circle-notch fa-spin text-blue-600 mb-2 text-xl"></i><br>
                                    Memuat data analitik harga...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="relative w-full mb-6">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-search text-slate-400"></i>
                </div>
                <input type="text" id="search" placeholder="Cari supplier atau barang yang disupply..." class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl focus:border-blue-600 outline-none text-sm font-bold text-slate-600 transition-all bg-white shadow-sm" onkeyup="cariData()">
            </div>

            <div id="grid-supplier" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                </div>

            <div id="pagination" class="mt-8 flex items-center justify-center gap-2"></div>

        </main>
    </div>

    <div id="modal-supplier" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeModal('modal-supplier')"></div>
        <div class="relative bg-white w-full max-w-lg rounded-[2rem] shadow-xl z-10 transform transition-all flex flex-col max-h-[95vh]">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-[2rem]">
                <h3 id="modal-title" class="text-lg font-black text-slate-800">Tambah Supplier</h3>
                <button onclick="closeModal('modal-supplier')" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar">
                <form id="formSupplier" class="space-y-4">
                    <input type="hidden" id="id" name="id">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Nama Perusahaan/Supplier <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Cth: CV. Sumber Makmur" required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Contact Person</label>
                            <input type="text" id="contact_person" name="contact_person" placeholder="Nama sales/PIC" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">No. Telp/WA <span class="text-rose-500">*</span></label>
                            <input type="text" id="phone" name="phone" placeholder="0812..." required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Email Supplier</label>
                        <input type="email" id="email" name="email" placeholder="kontak@supplier.com" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Alamat Kantor/Gudang <span class="text-rose-500">*</span></label>
                        <textarea id="address" name="address" rows="3" placeholder="Alamat lengkap..." required class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50 custom-scrollbar"></textarea>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 mb-1 uppercase tracking-widest">Catatan Tambahan</label>
                        <textarea id="description" name="description" rows="2" placeholder="Info tambahan (Cth: Spesialis Tepung)" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none transition-all font-bold text-slate-700 bg-slate-50 custom-scrollbar"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modal-supplier')" class="px-6 py-3 text-xs font-black text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors uppercase tracking-widest">Batal</button>
                        <button type="submit" class="px-6 py-3 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-md uppercase tracking-widest">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
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