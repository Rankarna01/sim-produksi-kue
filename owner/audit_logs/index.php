<?php
require_once '../../config/auth.php';
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-background">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8 w-full">
            
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Audit Logs (Jejak Produk)</h2>
                <p class="text-sm text-secondary mt-1">Lacak perjalanan riwayat sebuah produksi dari awal masuk dapur hingga keluar (expired/rusak).</p>
            </div>

            <div class="bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 mb-8 max-w-3xl mx-auto">
                <form id="formSearch" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        </div>
                        <input type="text" id="search_invoice" required class="w-full pl-11 pr-4 py-3 sm:py-4 border-2 border-slate-200 rounded-xl focus:border-indigo-500 outline-none font-mono text-base sm:text-lg placeholder:text-slate-300 uppercase transition-colors" placeholder="Masukkan No. Invoice (Cth: A131-0326-ABCD)">
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 sm:py-4 rounded-xl font-bold transition-all shadow-md flex items-center justify-center gap-2">
                        Lacak Jejak
                    </button>
                </form>
            </div>

            <div id="timeline-container" class="max-w-3xl mx-auto hidden">
                
                <div class="bg-slate-800 rounded-t-2xl p-5 text-white flex justify-between items-center">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Hasil Pelacakan Untuk</div>
                        <div class="font-mono text-xl font-bold text-indigo-300" id="info-inv">INV-XXXX</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Status Terakhir</div>
                        <div id="info-status" class="text-sm font-bold bg-white/20 px-3 py-1 rounded-lg inline-block">Memuat...</div>
                    </div>
                </div>

                <div class="bg-surface rounded-b-2xl shadow-sm border-x border-b border-slate-200 p-6 sm:p-8">
                    <div id="timeline-events" class="relative border-l-2 border-slate-200 ml-4 md:ml-6 space-y-8 pb-4">
                        </div>
                </div>
                
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>