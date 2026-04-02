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
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar_admin.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8 flex flex-col items-center justify-start relative">
            
            <div class="w-full max-w-xl mt-2 sm:mt-6">
                <div class="text-center mb-6 sm:mb-8">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 text-3xl sm:text-4xl shadow-inner">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Validasi Produk</h2>
                    <p class="text-xs sm:text-sm text-secondary mt-1 sm:mt-2">Scan struk atau ketik Nomor Invoice untuk memvalidasi fisik barang dari dapur.</p>
                </div>

                <button id="btnKamera" onclick="toggleKamera()" class="w-full mb-6 bg-slate-800 hover:bg-slate-900 text-white py-4 rounded-2xl text-base font-bold transition-all shadow-md flex items-center justify-center gap-3">
                    <i class="fa-solid fa-camera text-xl"></i> Buka Kamera Scanner
                </button>

                <div id="kameraContainer" class="hidden mb-6 bg-surface p-2 sm:p-4 rounded-3xl shadow-lg border border-slate-200">
                    <div class="flex justify-between items-center mb-2 px-2">
                        <span class="text-sm font-bold text-slate-700"><i class="fa-solid fa-video text-danger animate-pulse mr-1"></i> Kamera Aktif</span>
                        <button onclick="toggleKamera()" class="text-danger hover:text-red-700 text-sm font-bold bg-danger/10 px-3 py-1 rounded-lg">Tutup</button>
                    </div>
                    <div id="reader" class="w-full rounded-2xl overflow-hidden bg-slate-100 min-h-[250px] sm:min-h-[300px]"></div>
                </div>

                <div class="bg-surface p-6 sm:p-10 rounded-3xl shadow-lg border border-slate-200 relative overflow-hidden text-center mb-6">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-primary to-success"></div>
                    <p class="text-xs font-bold text-slate-400 mb-2 uppercase tracking-widest">Atau Ketik / Scan Manual</p>
                    <form id="formScan" class="relative">
                        <input type="text" id="barcodeInput" name="barcode" autocomplete="off" 
                            class="w-full text-center text-lg sm:text-2xl font-mono tracking-widest px-4 py-4 sm:py-5 border-2 border-slate-300 rounded-2xl focus:border-primary focus:ring-4 focus:ring-primary/20 outline-none transition-all placeholder:text-slate-300 uppercase" 
                            placeholder="BARCODE / NO. INVOICE" required>
                        <button type="submit" id="btnSubmitHidden" class="hidden">Submit</button>
                    </form>
                </div>

                <div id="scanResult" class="hidden transform transition-all mb-10"></div>
            </div>
            
        </main>
    </div>

    <div id="modal-konfirmasi" class="fixed inset-0 z-[100] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-surface w-full max-w-md rounded-3xl shadow-2xl z-[110] transform transition-all flex flex-col overflow-hidden max-h-[95vh]">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
            
            <div class="p-6 sm:p-8 flex flex-col h-full">
                <div class="text-center mb-4">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4 text-2xl sm:text-3xl">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-800 mb-1">Cek Fisik Barang!</h3>
                    <p class="text-xs sm:text-sm text-secondary">Pastikan seluruh fisik barang di keranjang sesuai dengan daftar ini.</p>
                </div>
                
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-left mb-6 flex-1 flex flex-col overflow-hidden">
                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-slate-200">
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">No. Invoice:</div>
                        <div class="font-mono font-bold text-primary text-sm sm:text-base" id="konf-invoice">INV-XXXX</div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 mb-4 space-y-2 max-h-40 sm:max-h-48" id="konf-list-produk">
                        </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs pt-3 border-t border-slate-200">
                        <div class="text-slate-500">Dari Dapur: <br><strong class="text-slate-700" id="konf-user">User</strong></div>
                        <div class="text-slate-500">Tujuan: <br><strong class="text-slate-700" id="konf-gudang">Gudang</strong></div>
                    </div>
                </div>

                <input type="hidden" id="konf-prod-id">

                <div class="flex flex-col gap-2 sm:gap-3 mt-auto shrink-0">
                    <button onclick="prosesValidasi('masuk_gudang')" class="w-full bg-success hover:bg-green-600 text-white py-3 sm:py-3.5 rounded-xl text-sm sm:text-base font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i> Ya, Semua Sesuai
                    </button>
                    <button onclick="prosesValidasi('ditolak')" class="w-full bg-danger/10 hover:bg-danger hover:text-white text-danger py-3 sm:py-3.5 rounded-xl text-sm sm:text-base font-bold transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark-circle"></i> Ada yang Salah (Tolak)
                    </button>
                    <button onclick="document.getElementById('modal-konfirmasi').classList.add('hidden')" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 py-2.5 sm:py-3 rounded-xl text-sm font-semibold transition-all mt-1 sm:mt-2">
                        Batal Scan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>