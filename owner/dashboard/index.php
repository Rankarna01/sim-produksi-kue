<?php
// 1. Wajib panggil file auth pertama kali
require_once '../../config/auth.php';

// 2. Validasi Keamanan: Pastikan hanya 'owner' yang bisa akses halaman ini
checkRole(['owner']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden">

    <?php include '../../components/sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <?php include '../../components/header.php'; ?>
        
     <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-6 lg:p-8">
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden flex flex-col justify-between h-36">
            <div class="p-5 flex-1 z-10">
                <div class="flex items-center gap-2 text-secondary mb-2">
                    <i class="fa-solid fa-cake-candles text-sm"></i>
                    <h3 class="text-xs font-bold tracking-wider uppercase">Total Produksi Hari Ini</h3>
                </div>
                <div class="text-4xl font-extrabold text-slate-800">125</div>
                <div class="mt-4 flex items-center gap-2 text-xs text-secondary font-medium">
                    <span class="text-primary bg-primary/20 rounded-full w-5 h-5 flex items-center justify-center"><i class="fa-solid fa-arrow-trend-up text-[10px]"></i></span>
                    Meningkat dari kemarin
                </div>
            </div>
            <i class="fa-solid fa-boxes-stacked absolute -top-4 -right-4 text-7xl text-slate-50 z-0"></i>
            <div class="h-1.5 w-full bg-primary"></div>
        </div>

        <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden flex flex-col justify-between h-36">
            <div class="p-5 flex-1 z-10">
                <div class="flex items-center gap-2 text-secondary mb-2">
                    <i class="fa-solid fa-hourglass-half text-sm"></i>
                    <h3 class="text-xs font-bold tracking-wider uppercase">Produk Pending</h3>
                </div>
                <div class="text-4xl font-extrabold text-slate-800">40</div>
                <div class="mt-4 flex items-center gap-2 text-xs text-secondary font-medium">
                    <span class="text-accent bg-accent/20 rounded-full w-5 h-5 flex items-center justify-center"><i class="fa-solid fa-clock text-[10px]"></i></span>
                    Menunggu di-scan Admin
                </div>
            </div>
            <i class="fa-solid fa-barcode absolute -top-4 -right-4 text-7xl text-slate-50 z-0"></i>
            <div class="h-1.5 w-full bg-accent"></div>
        </div>

        <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden flex flex-col justify-between h-36">
            <div class="p-5 flex-1 z-10">
                <div class="flex items-center gap-2 text-secondary mb-2">
                    <i class="fa-solid fa-wheat-awn text-sm"></i>
                    <h3 class="text-xs font-bold tracking-wider uppercase">Bahan Baku Menipis</h3>
                </div>
                <div class="text-4xl font-extrabold text-slate-800">4</div>
                <div class="mt-4 flex items-center gap-2 text-xs text-secondary font-medium">
                    <span class="text-danger bg-danger/20 rounded-full w-5 h-5 flex items-center justify-center"><i class="fa-solid fa-triangle-exclamation text-[10px]"></i></span>
                    Perlu segera restock
                </div>
            </div>
            <i class="fa-solid fa-boxes-packing absolute -top-4 -right-4 text-7xl text-slate-50 z-0"></i>
            <div class="h-1.5 w-full bg-danger"></div>
        </div>

        <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden flex flex-col justify-between h-36">
            <div class="p-5 flex-1 z-10">
                <div class="flex items-center gap-2 text-secondary mb-2">
                    <i class="fa-solid fa-box-check text-sm"></i>
                    <h3 class="text-xs font-bold tracking-wider uppercase">Masuk Gudang</h3>
                </div>
                <div class="text-4xl font-extrabold text-slate-800">85</div>
                <div class="mt-4 flex items-center gap-2 text-xs text-secondary font-medium">
                    <span class="text-success bg-success/20 rounded-full w-5 h-5 flex items-center justify-center"><i class="fa-solid fa-check text-[10px]"></i></span>
                    Tervalidasi hari ini
                </div>
            </div>
            <i class="fa-solid fa-warehouse absolute -top-4 -right-4 text-7xl text-slate-50 z-0"></i>
            <div class="h-1.5 w-full bg-success"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 tracking-tight">Top Produksi Kue</h3>
                    <p class="text-sm text-secondary mt-1">Statistik produk paling banyak dibuat bulan ini.</p>
                </div>
                <a href="#" class="text-sm font-semibold text-primary hover:opacity-80 flex items-center gap-1 transition-opacity">
                    Lihat Detail <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="relative flex-1 flex items-end justify-between px-4 pb-2 mt-4 min-h-[250px] border-b border-slate-200">
                <div class="absolute w-full border-t border-dashed border-slate-200 top-0 left-0"><span class="absolute -top-3 -left-6 text-xs text-secondary">60</span></div>
                <div class="absolute w-full border-t border-dashed border-slate-200 top-[33%] left-0"><span class="absolute -top-3 -left-6 text-xs text-secondary">45</span></div>
                <div class="absolute w-full border-t border-dashed border-slate-200 top-[66%] left-0"><span class="absolute -top-3 -left-6 text-xs text-secondary">30</span></div>
                <div class="absolute w-full border-t border-dashed border-slate-200 top-[100%] left-0"><span class="absolute -top-3 -left-6 text-xs text-secondary">15</span></div>
                
                <div class="w-12 bg-gradient-to-t from-primary/30 to-primary rounded-t-md h-[40%] z-10 relative group cursor-pointer hover:opacity-80 transition-opacity"></div>
                <div class="w-12 bg-gradient-to-t from-primary/30 to-primary rounded-t-md h-[85%] z-10 relative group cursor-pointer hover:opacity-80 transition-opacity"></div>
                <div class="w-12 bg-gradient-to-t from-primary/30 to-primary rounded-t-md h-[60%] z-10 relative group cursor-pointer hover:opacity-80 transition-opacity"></div>
                <div class="w-12 bg-gradient-to-t from-primary/30 to-primary rounded-t-md h-[30%] z-10 relative group cursor-pointer hover:opacity-80 transition-opacity"></div>
                <div class="w-12 bg-gradient-to-t from-primary/30 to-primary rounded-t-md h-[75%] z-10 relative group cursor-pointer hover:opacity-80 transition-opacity"></div>
            </div>
            <div class="flex justify-between px-4 mt-3 text-xs text-secondary font-medium">
                <span class="w-12 text-center">Roti Coklat</span>
                <span class="w-12 text-center">Roti Keju</span>
                <span class="w-12 text-center">Donat</span>
                <span class="w-12 text-center">Bolu</span>
                <span class="w-12 text-center">Kue Sus</span>
            </div>
        </div>

        <div class="bg-surface rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col h-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-800 tracking-tight">Aktivitas Terbaru</h3>
                <button class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-secondary hover:bg-background transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-download"></i> Export Log
                </button>
            </div>
            
            <div class="space-y-4 overflow-y-auto pr-2 max-h-[350px]">
                
                <div class="p-4 border border-slate-100 rounded-xl flex gap-4 hover:border-slate-300 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-success/20 text-success flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-bold text-slate-800">Citra (Admin)</h4>
                            <span class="text-[10px] text-secondary font-medium tracking-wide">Hari Ini</span>
                        </div>
                        <p class="text-xs text-secondary mt-0.5 mb-2">Validasi struk masuk ke Gudang Jadi.</p>
                        <span class="inline-block px-2 py-1 bg-surface border border-slate-200 text-secondary text-[10px] rounded font-semibold shadow-sm">INV-001 • Roti Coklat</span>
                    </div>
                </div>

                <div class="p-4 border border-slate-100 rounded-xl flex gap-4 hover:border-slate-300 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-accent/20 text-accent flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-ellipsis"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-bold text-slate-800">Andi (Produksi)</h4>
                            <span class="text-[10px] text-secondary font-medium tracking-wide">2 Jam Lalu</span>
                        </div>
                        <p class="text-xs text-secondary mt-0.5 mb-2">Selesai membuat produksi baru.</p>
                        <span class="inline-block px-2 py-1 bg-surface border border-slate-200 text-secondary text-[10px] rounded font-semibold shadow-sm">50 Pcs Roti Keju</span>
                    </div>
                </div>

                <div class="p-4 border border-slate-100 rounded-xl flex gap-4 hover:border-slate-300 transition-colors">
                    <div class="w-10 h-10 rounded-full bg-success/20 text-success flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <h4 class="text-sm font-bold text-slate-800">Citra (Admin)</h4>
                            <span class="text-[10px] text-secondary font-medium tracking-wide">Kemarin</span>
                        </div>
                        <p class="text-xs text-secondary mt-0.5 mb-2">Validasi struk masuk ke Gudang Jadi.</p>
                        <span class="inline-block px-2 py-1 bg-surface border border-slate-200 text-secondary text-[10px] rounded font-semibold shadow-sm">INV-000 • Roti Bolu</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</main>
        
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="ajax.js"></script>
</body>
</html>