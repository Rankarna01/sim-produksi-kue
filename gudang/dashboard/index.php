<?php
require_once '../../config/auth.php'; 
checkPermission('dashboard'); 

// Ambil role dari session untuk pengecekan tombol edit
$userRole = $_SESSION['role'] ?? $_SESSION['role_slug'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include '../../components/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

    <?php include '../../components/sidebar_gudang.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <?php include '../../components/header.php'; ?>
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
            
            <div id="pengumuman-container" class="hidden mb-6 bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-700 rounded-2xl shadow-lg shadow-blue-200 overflow-hidden relative border border-blue-400/50">
                <div class="flex items-center">
                    <div class="bg-blue-900 text-white px-4 py-3 font-black text-xs uppercase tracking-widest z-10 shadow-lg flex items-center gap-2 relative">
                        <i class="fa-solid fa-bullhorn animate-pulse text-amber-300"></i> INFO
                        <div class="absolute -right-3 top-0 h-full w-4 bg-gradient-to-r from-blue-900 to-transparent"></div>
                    </div>
                    
                    <div class="flex-1 overflow-hidden py-3 relative">
                        <div class="marquee-text whitespace-nowrap text-sm font-bold text-white tracking-wide" id="text-pengumuman">
                            Memuat pengumuman...
                        </div>
                    </div>

                    <?php if ($userRole === 'owner_gudang'): ?>
                    <button onclick="bukaModalPengumuman()" class="bg-blue-900/60 hover:bg-blue-900 text-white px-5 py-3 text-xs font-black z-10 flex items-center gap-2 transition-colors border-l border-blue-400/50 shadow-inner cursor-pointer">
                        <i class="fa-solid fa-pen"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Logistik Dashboard</h2>
                    <p class="text-sm text-slate-500 mt-1">Monitoring stok pilar dan kesehatan inventaris.</p>
                </div>
                <div class="text-sm font-bold text-slate-500 bg-white px-5 py-2.5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-blue-600"></i> <?= date('d F Y') ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4 shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cart-flatbed"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">PO Berjalan</h3>
                    <div class="text-3xl font-black text-slate-800"><span id="stat-po">...</span> <span class="text-xs font-bold text-slate-400 italic">Order</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-600 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Pending Approval</h3>
                    <div class="text-3xl font-black text-slate-800"><span id="stat-req">...</span> <span class="text-xs font-bold text-slate-400 italic">Data</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Stok Kritis</h3>
                    <div class="text-3xl font-black text-slate-800"><span id="stat-kritis">...</span> <span class="text-xs font-bold text-slate-400 italic">Item</span></div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-6 relative overflow-hidden group">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-1">Hutang PO</h3>
                    <div class="text-2xl font-black text-slate-800 truncate" id="stat-hutang">...</div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase flex items-center gap-2"><i class="fa-solid fa-chart-line text-blue-600"></i> Tren Transaksi (7 Hari)</h3>
                    <div class="relative h-64">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-200">
                    <h3 class="text-sm font-black text-slate-800 mb-4 uppercase flex items-center gap-2"><i class="fa-solid fa-chart-pie text-indigo-600"></i> Kesehatan Stok</h3>
                    <div class="relative h-64 flex justify-center">
                        <canvas id="stockChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200 lg:col-span-2 overflow-hidden">
                    <h3 class="text-sm font-black text-slate-800 uppercase mb-6 flex items-center gap-2"><i class="fa-solid fa-receipt text-blue-600"></i> Request Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <tr>
                                    <th class="pb-4">Waktu</th>
                                    <th class="pb-4">Bahan Baku</th>
                                    <th class="pb-4 text-center">Qty</th>
                                    <th class="pb-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="table-permintaan" class="text-sm font-medium text-slate-600 divide-y divide-slate-50">
                                <tr><td colspan="4" class="py-10 text-center text-slate-300 italic">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
                    <h3 class="text-sm font-black text-slate-800 uppercase mb-6 flex items-center gap-2"><i class="fa-solid fa-truck-fast text-emerald-600"></i> Supplier Baru</h3>
                    <div id="list-supplier" class="space-y-4">
                         <div class="py-10 text-center text-slate-300 italic">Memuat data...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($userRole === 'owner_gudang'): ?>
    <div id="modal-pengumuman" class="fixed inset-0 z-50 flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modal-pengumuman')"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-xl z-10 overflow-hidden transform transition-all">
            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-slate-800">Edit Pengumuman</h3>
                <button onclick="closeModal('modal-pengumuman')" class="text-slate-400 hover:text-rose-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="formPengumuman" class="p-6">
                <textarea id="input_pengumuman" rows="4" class="w-full px-4 py-3 border border-slate-300 rounded-xl outline-none font-bold text-slate-700 bg-slate-50" required></textarea>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-pengumuman')" class="px-6 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-xl text-xs font-black shadow-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .marquee-text { display: inline-block; padding-left: 100%; animation: marquee 20s linear infinite; }
        @keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }
    </style>

    <?php include '../../components/footer.php'; ?>
    <script src="ajax.js?v=<?= time() ?>"></script>
</body>
</html>