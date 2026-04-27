<?php
require_once '../../config/auth.php';
checkPermission('view_dashboard');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Animasi khusus untuk pengumuman berjalan */
        .marquee-container {
            display: flex;
            overflow: hidden;
            white-space: nowrap;
            width: 100%;
        }
        .marquee-content {
            animation: marquee 25s linear infinite;
            display: inline-block;
        }
        .marquee-container:hover .marquee-content {
            animation-play-state: paused;
        }
        @keyframes marquee {
            0%   { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 sm:p-6 lg:p-8">
            
            <div class="bg-blue-600 rounded-2xl shadow-sm mb-6 flex items-center overflow-hidden text-white relative h-12 shrink-0">
                <div class="bg-blue-800 px-4 h-full flex items-center justify-center font-black text-xs uppercase tracking-widest z-10 shrink-0 gap-2">
                    <i class="fa-solid fa-bullhorn"></i> Info
                </div>
                <div class="marquee-container flex-1 mx-4 text-sm font-bold opacity-90" id="marquee-text">
                    <span class="marquee-content" id="pengumuman-text">Memuat pengumuman...</span>
                </div>
                
                <?php if(hasPermission('edit_pengumuman_dashboard')): ?>
                <button onclick="openModalPengumuman()" class="bg-blue-800 hover:bg-blue-900 transition-colors px-4 h-full flex items-center justify-center font-black text-xs uppercase tracking-widest z-10 shrink-0 gap-2 cursor-pointer border-l border-blue-700/50">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
                <?php endif; ?>
            </div>

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

    <?php if(hasPermission('edit_pengumuman_dashboard')): ?>
    <div id="modal-pengumuman" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-pengumuman')"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl z-10 flex flex-col overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-black text-slate-800"><i class="fa-solid fa-bullhorn text-blue-600 mr-2"></i> Edit Pengumuman</h3>
                <button onclick="closeModal('modal-pengumuman')" class="text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form id="formPengumuman" class="p-6">
                <div>
                    <label class="block text-xs font-black text-slate-500 mb-2 uppercase tracking-widest">Teks Pengumuman (Max 250 Karakter)</label>
                    <textarea id="teks_pengumuman" name="pengumuman" rows="3" maxlength="250" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:border-blue-600 outline-none font-medium text-slate-700 bg-slate-50" placeholder="Ketik info terbaru di sini..."></textarea>
                    <p class="text-[10px] text-slate-400 mt-2 text-right"><span id="char-count">0</span>/250</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-pengumuman')" class="px-5 py-2.5 text-xs font-black text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors uppercase tracking-widest">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-md uppercase tracking-widest flex items-center gap-2"><i class="fa-solid fa-save"></i> Simpan Info</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>