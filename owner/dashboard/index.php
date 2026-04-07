<?php
require_once '../../config/auth.php';
checkRole(['owner', 'auditor']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Dashboard Utama</h2>
                    <p class="text-sm text-secondary mt-1">Ringkasan performa bisnis dan operasional dapur Anda.</p>
                </div>
                <div class="text-sm font-semibold text-slate-500 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-primary"></i> <?= date('d F Y') ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-fire-burner"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Produksi Hari Ini</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-produksi">0 <span class="text-sm font-semibold text-slate-400">Pcs</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-primary transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-danger/10 text-danger rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Bahan Baku Menipis</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-bahan">0 <span class="text-sm font-semibold text-slate-400">Item</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-danger transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Total Varian Produk</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-produk">0 <span class="text-sm font-semibold text-slate-400">Macam</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-purple-500/10 text-purple-500 rounded-xl flex items-center justify-center text-xl mb-4"><i class="fa-solid fa-users"></i></div>
                    <h3 class="text-slate-500 text-sm font-bold mb-1">Total Akun User</h3>
                    <div class="text-3xl font-black text-slate-800" id="stat-user">0 <span class="text-sm font-semibold text-slate-400">Orang</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-purple-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 lg:col-span-2">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Tren Produksi (7 Hari Terakhir)</h3>
                    <div class="relative w-full h-[300px]">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <div class="bg-surface p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-800">Aktivitas Dapur</h3>
                        <a href="../laporan_produksi/" class="text-xs font-bold text-primary hover:underline">Lihat Laporan</a>
                    </div>
                    <div class="space-y-3 overflow-y-auto pr-2" id="recent-activities" style="max-height: 300px;">
                        <p class="text-center text-sm text-secondary py-4">Memuat data...</p>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>