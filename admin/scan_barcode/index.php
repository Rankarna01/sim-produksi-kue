<?php
require_once '../../config/auth.php';
checkRole(['admin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-start relative">
            
            <div class="w-full max-w-xl mt-2 sm:mt-4">
                
                <div class="text-center mb-6 sm:mb-8">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 text-3xl sm:text-4xl shadow-inner">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Validasi Produk</h2>
                    <p class="text-sm text-secondary mt-2 max-w-sm mx-auto">Scan struk barcode atau ketik Nomor Invoice untuk menerima barang fisik dari dapur.</p>
                </div>

                <button id="btnKamera" onclick="toggleKamera()" class="w-full mb-6 bg-slate-800 hover:bg-slate-900 text-white py-4 rounded-2xl text-base font-bold transition-all shadow-md flex items-center justify-center gap-3">
                    <i class="fa-solid fa-camera text-xl"></i> Buka Kamera Scanner
                </button>

                <div id="kameraContainer" class="hidden mb-6 bg-white p-2 sm:p-4 rounded-3xl shadow-lg border border-slate-200">
                    <div class="flex justify-between items-center mb-3 px-2">
                        <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-danger"></span>
                            </span>
                            Kamera Aktif
                        </span>
                        <button onclick="toggleKamera()" class="text-danger hover:text-red-700 text-xs font-bold bg-danger/10 px-4 py-2 rounded-xl transition-colors">Tutup Kamera</button>
                    </div>
                    <div id="reader" class="w-full rounded-2xl overflow-hidden bg-slate-50 min-h-[250px] sm:min-h-[300px] border border-slate-100"></div>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-lg border border-slate-200 relative overflow-hidden mb-6">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-primary to-emerald-400"></div>
                    <div class="flex items-center justify-between mb-4">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Atau Ketik Manual</p>
                        <i class="fa-solid fa-keyboard text-slate-300"></i>
                    </div>
                    
                    <form id="formScan" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <input type="text" id="barcodeInput" name="barcode" autocomplete="off" 
                                class="w-full text-center sm:text-left text-lg font-mono tracking-wider px-5 py-4 border-2 border-slate-200 rounded-2xl focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-300 placeholder:tracking-normal uppercase bg-slate-50 focus:bg-white" 
                                placeholder="Cth: D0426-001" required>
                        </div>
                        <button type="submit" class="w-full sm:w-auto bg-primary hover:bg-blue-700 text-white px-8 py-4 rounded-2xl font-bold transition-all shadow-md flex items-center justify-center gap-2 shrink-0">
                            <i class="fa-solid fa-magnifying-glass"></i> Cek
                        </button>
                    </form>
                </div>

                <div id="scanResult" class="hidden transform transition-all mb-10"></div>
            </div>
            
        </main>
    </div>

    <div id="modal-konfirmasi" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="document.getElementById('modal-konfirmasi').classList.add('hidden')"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-2xl z-[110] transform transition-all flex flex-col overflow-hidden max-h-[90vh]">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
            
            <div class="p-6 sm:p-8 flex flex-col h-full">
                <div class="text-center mb-5 border-b border-slate-100 pb-5">
                    <div class="w-14 h-14 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-1">Cek Fisik Barang!</h3>
                    <p class="text-xs text-secondary leading-relaxed px-4">Pastikan jumlah fisik roti di keranjang benar-benar sesuai dengan daftar di bawah ini.</p>
                </div>
                
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-left mb-6 flex flex-col overflow-hidden">
                    <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-200">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No. Invoice</div>
                        <div class="font-mono font-black text-primary text-base bg-blue-50 px-3 py-1 rounded-lg" id="konf-invoice">INV-XXXX</div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 mb-4 space-y-2 max-h-[25vh] sm:max-h-[30vh]" id="konf-list-produk">
                        </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs pt-3 border-t border-slate-200">
                        <div class="bg-white p-2 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block mb-0.5 font-semibold text-[10px] uppercase">Dari Dapur:</span>
                            <strong class="text-slate-700 font-bold" id="konf-user">User</strong>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block mb-0.5 font-semibold text-[10px] uppercase">Tujuan Gudang:</span>
                            <strong class="text-slate-700 font-bold truncate block" id="konf-gudang">Gudang</strong>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="konf-prod-id">

                <div class="flex flex-col gap-3 mt-auto shrink-0">
                    <button onclick="prosesValidasi('masuk_gudang')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-3.5 rounded-xl text-sm font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle text-lg"></i> Ya, Sesuai & Terima
                    </button>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="prosesValidasi('ditolak')" class="w-full bg-red-50 hover:bg-red-500 hover:text-white text-red-600 py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 border border-red-100 hover:border-red-500">
                            <i class="fa-solid fa-xmark"></i> Tolak
                        </button>
                        <button onclick="document.getElementById('modal-konfirmasi').classList.add('hidden')" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl text-sm font-bold transition-all flex items-center justify-center">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>