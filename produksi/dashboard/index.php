<?php
require_once '../../config/auth.php';
checkRole(['produksi']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">
    <?php include '../../components/sidebar_produksi.php'; ?>
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            <div class="mb-6 sm:mb-8 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Dashboard Produksi</h2>
                    <p class="text-sm text-secondary mt-1">Ringkasan KPI performa dapur Anda hari ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                
                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Total Produksi (Pcs)</h3>
                    <div class="text-4xl font-black text-slate-800" id="stat-total">0</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-primary"></div>
                </div>
                
                <a href="../riwayat_produksi/index.php?status=pending" class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md hover:border-amber-300 transition-all block cursor-pointer">
                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-accent group-hover:text-white transition-colors">
                        <i class="fa-solid fa-arrow-right -rotate-45"></i>
                    </div>
                    <div class="w-12 h-12 bg-accent/10 text-accent rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-hourglass-half animate-pulse"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Status Pending (Pcs)</h3>
                    <div class="text-4xl font-black text-slate-800" id="stat-pending">0</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-accent"></div>
                </a>

                <a href="../riwayat_produksi/index.php?status=ditolak" class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group hover:shadow-md hover:border-red-300 transition-all block cursor-pointer">
                    <div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-danger group-hover:text-white transition-colors">
                        <i class="fa-solid fa-arrow-right -rotate-45"></i>
                    </div>
                    <div class="w-12 h-12 bg-danger/10 text-danger rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Perlu Revisi (Pcs)</h3>
                    <div class="text-4xl font-black text-slate-800" id="stat-ditolak">0</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-danger"></div>
                </a>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-success/10 text-success rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-check-double"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Selesai/Valid (Pcs)</h3>
                    <div class="text-4xl font-black text-slate-800" id="stat-valid">0</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-success"></div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col items-center">
                    <h3 class="text-lg font-bold text-slate-800 w-full text-left mb-4">Rasio Status Produksi Hari Ini</h3>
                    <div class="relative w-full max-w-[300px] aspect-square">
                        <canvas id="kpiChart"></canvas>
                    </div>
                </div>

                <div class="bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-800">5 Aktivitas Terakhir</h3>
                        <a href="../riwayat_produksi/" class="text-xs font-bold text-primary hover:underline">Lihat Semua</a>
                    </div>
                    <div class="space-y-3" id="recent-activities">
                        <p class="text-center text-sm text-secondary py-4">Memuat...</p>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>