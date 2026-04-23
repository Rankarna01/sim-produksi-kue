<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
// Pastikan kamu punya izin input_titipan di role management, atau abaikan jika sama dengan produksi.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background" onclick="closeAllDropdowns(event)">

    <?php include '../../components/sidebar_produksi.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <div class="w-full max-w-5xl mx-auto mt-2 sm:mt-4">
                
                <div class="mb-6 bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-3xl shrink-0 shadow-sm">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Catat Produk Titipan</h2>
                            <p class="text-sm text-secondary mt-1">Keluarkan barang titipan UMKM untuk dikirim ke Store.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-amber-400 to-amber-600"></div>

                    <form id="formProduksi" class="p-5 sm:p-8 md:p-10">
                        <div class="space-y-6">
                            
                            <div class="bg-amber-50 p-4 sm:p-6 rounded-2xl border border-amber-100">
                                <label class="block text-sm sm:text-base font-bold text-amber-900 mb-2 sm:mb-3">
                                    <span class="bg-amber-500 text-white w-6 h-6 inline-flex items-center justify-center rounded-full text-xs mr-2 shadow-sm">1</span> 
                                    Siapa yang mencatat ini? <span class="text-danger">*</span>
                                </label>
                                <select id="employee_id" name="employee_id" required class="w-full px-4 py-3 sm:py-4 border-2 border-amber-200 rounded-xl focus:ring-0 focus:border-amber-500 outline-none transition-all bg-white text-base font-bold text-amber-900 cursor-pointer shadow-sm">
                                    <option value="">-- Memuat Data Pegawai --</option>
                                </select>
                            </div>

                            <div class="bg-slate-50 p-4 sm:p-6 rounded-2xl border border-slate-100">
                                <div class="flex justify-between items-center mb-4">
                                    <label class="block text-sm sm:text-base font-bold text-slate-700">
                                        <span class="bg-slate-800 text-white w-6 h-6 inline-flex items-center justify-center rounded-full text-xs mr-2 shadow-sm">2</span> 
                                        Daftar Barang Titipan
                                    </label>
                                </div>
                                
                                <div id="product-container" class="space-y-4"></div>

                                <button type="button" onclick="addProductRow()" class="mt-5 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-2 border border-slate-300 border-dashed w-full sm:w-auto justify-center">
                                    <i class="fa-solid fa-plus"></i> Tambahkan Barang Lainnya
                                </button>
                            </div>

                            <div class="bg-slate-50 p-4 sm:p-6 rounded-2xl border border-slate-100">
                                <label class="block text-sm sm:text-base font-bold text-slate-700 mb-2 sm:mb-3">
                                    <span class="bg-slate-800 text-white w-6 h-6 inline-flex items-center justify-center rounded-full text-xs mr-2 shadow-sm">3</span> 
                                    Kirim ke Store Tujuan <span class="text-danger">*</span>
                                </label>
                                <select id="warehouse_id" name="warehouse_id" required class="w-full px-4 py-3 sm:py-4 border-2 border-slate-200 rounded-xl focus:ring-0 focus:border-amber-500 outline-none transition-all bg-white text-base font-medium cursor-pointer shadow-sm">
                                    <option value="">-- Memuat Lokasi Store --</option>
                                </select>
                            </div>

                            <div class="bg-slate-50 p-4 sm:p-6 rounded-2xl border border-slate-100">
                                <label class="block text-sm sm:text-base font-bold text-slate-700 mb-2 sm:mb-3">
                                    <span class="bg-slate-800 text-white w-6 h-6 inline-flex items-center justify-center rounded-full text-xs mr-2 shadow-sm">4</span> 
                                    Catatan Tambahan <span class="text-slate-400 font-normal text-sm">(Opsional)</span>
                                </label>
                                <textarea id="notes" name="notes" rows="2" class="w-full px-4 py-3 sm:py-4 border-2 border-slate-200 rounded-xl focus:ring-0 focus:border-amber-500 outline-none transition-all bg-white text-base font-medium shadow-sm placeholder:text-slate-300" placeholder="Ketik catatan di sini..."></textarea>
                            </div>
                            
                        </div>

                        <div class="mt-8 sm:mt-10 pt-6 sm:pt-8 border-t border-slate-100">
                            <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-4 sm:py-5 rounded-2xl text-lg sm:text-xl font-bold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 flex items-center justify-center gap-3">
                                <i class="fa-solid fa-paper-plane text-2xl"></i> 
                                <span>Proses & Potong Stok Titipan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <div id="modal-sukses" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-surface w-full max-w-sm rounded-3xl shadow-2xl z-[110] transform transition-all text-center p-6 sm:p-8">
            <div class="w-20 h-20 bg-success/20 text-success rounded-full flex items-center justify-center mx-auto mb-5 sm:mb-6 text-4xl shadow-inner">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 mb-2">Berhasil!</h3>
            <p class="text-sm text-secondary mb-8">Data dicatat dan stok barang titipan telah terpotong otomatis.</p>
            
            <div class="space-y-3 relative z-20">
                <button id="btnCetak" onclick="" type="button" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2 shadow-md cursor-pointer relative z-30">
                    <i class="fa-solid fa-print text-xl"></i> Cetak Struk
                </button>
                <button onclick="selesaiProduksi()" type="button" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-3.5 rounded-xl font-bold transition-all cursor-pointer relative z-30">
                    Selesai
                </button>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>