<?php
require_once '../../../config/auth.php';
checkPermission('pengaturan_profil'); 
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
            
            <div class="mb-6">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-store text-blue-600"></i> Profil Toko & Pengaturan
                </h2>
                <p class="text-sm text-slate-500 mt-1">Atur informasi toko untuk cetak dokumen dan pengaturan saklar sistem persetujuan.</p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden max-w-4xl">
                <form id="formProfil" class="p-6 sm:p-8 space-y-8" enctype="multipart/form-data">
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-4">Logo Toko (Kop Surat)</label>
                        <div class="flex flex-col sm:flex-row items-start gap-6">
                            <div class="w-32 h-32 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden relative group">
                                <span id="logoPlaceholder" class="text-xs font-bold text-slate-400 text-center">Tidak ada logo</span>
                                <img id="logoPreview" src="" alt="Preview" class="absolute inset-0 w-full h-full object-cover hidden">
                            </div>
                            <div class="flex-1 pt-2">
                                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg" onchange="previewLogo(event)" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                                <p class="text-xs font-medium text-slate-400 mt-2">Format: JPG, PNG. Maks: 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <div>
                        <h3 class="text-sm font-bold text-slate-700 mb-1"><i class="fa-solid fa-lock text-rose-500 mr-2"></i> Pengaturan Persetujuan (SOP)</h3>
                        <p class="text-xs text-slate-500 mb-4">Centang untuk MEWAJIBKAN persetujuan Manager pada transaksi tersebut. Hilangkan centang jika ingin otomatis masuk/keluar tanpa persetujuan.</p>
                        
                        <div class="space-y-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" id="req_approval_in" name="req_approval_in" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Persetujuan Transaksi <strong class="text-emerald-600">Barang Masuk</strong> (Manual)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" id="req_approval_out" name="req_approval_out" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Persetujuan Transaksi <strong class="text-rose-600">Barang Keluar</strong> (Manual)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" id="req_approval_pr" name="req_approval_pr" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Persetujuan <strong class="text-blue-600">Permintaan Barang (PR)</strong></span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" id="req_approval_po" name="req_approval_po" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Persetujuan <strong class="text-blue-600">Purchase Order (PO)</strong></span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer w-max">
                                <input type="checkbox" id="req_approval_print" name="req_approval_print" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-slate-700">Persetujuan <strong class="text-amber-600">Izin Cetak Ulang</strong> Dokumen</span>
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nama Perusahaan / Toko <span class="text-rose-500">*</span></label>
                            <input type="text" id="store_name" name="store_name" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-black text-slate-800 bg-white shadow-sm uppercase tracking-widest">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap (Ditampilkan di Invoice/PO)</label>
                            <textarea id="address" name="address" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-800 bg-white shadow-sm custom-scrollbar"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Nomor Telepon / WA</label>
                                <input type="text" id="phone" name="phone" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-800 bg-white shadow-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Email Resmi</label>
                                <input type="email" id="email" name="email" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl outline-none focus:border-blue-600 font-bold text-slate-800 bg-white shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-black text-sm transition-all shadow-lg shadow-blue-200 flex items-center gap-2">
                            <i class="fa-regular fa-floppy-disk"></i> Simpan Profil & Pengaturan
                        </button>
                    </div>

                </form>
            </div>

        </main>
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