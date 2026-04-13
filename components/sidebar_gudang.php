<?php
$current_uri = $_SERVER['REQUEST_URI'];
function getNavClass($path, $current_uri) {
    if (strpos($current_uri, $path) !== false) return 'bg-blue-600/10 text-blue-600 font-bold';
    return 'text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium';
}
?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<aside id="main-sidebar" class="w-64 bg-white border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">
    
    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 bg-white shrink-0">
        <h1 class="font-black text-blue-600 text-xl uppercase tracking-tighter">
            <i class="fa-solid fa-boxes-stacked mr-2"></i> Gudang Logistik
        </h1>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-red-500 p-2 rounded-lg bg-slate-50 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto custom-scrollbar">
        
        <a href="<?= BASE_URL ?>gudang/dashboard/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= getNavClass('/gudang/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center"></i> <span class="text-sm">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>gudang/persetujuan/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= getNavClass('/gudang/persetujuan/', $current_uri) ?>">
            <i class="fa-solid fa-file-circle-check w-5 text-center text-amber-500"></i> <span class="text-sm">Persetujuan</span>
        </a>

        <div class="my-4 border-t border-slate-50"></div>

        <div x-data="{ open: <?= strpos($current_uri, 'transaksi') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-right-left w-5 text-center text-emerald-500"></i>
                    <span class="text-sm">Transaksi</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <a href="<?= BASE_URL ?>gudang/transaksi/permintaan/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/permintaan/', $current_uri) ?>">
                    <i class="fa-solid fa-truck-ramp-box text-slate-400"></i> Permintaan Barang
                </a>
                <a href="<?= BASE_URL ?>gudang/transaksi/po/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/po/', $current_uri) ?>">
                    <i class="fa-solid fa-cart-flatbed text-slate-400"></i> Purchase Order (PO)
                </a>
                <a href="<?= BASE_URL ?>gudang/transaksi/pembayaran/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/pembayaran/', $current_uri) ?>">
                    <i class="fa-solid fa-file-invoice-dollar text-slate-400"></i> Pembayaran & Hutang
                </a>
                <a href="<?= BASE_URL ?>gudang/transaksi/supplier/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold transition-colors <?= getNavClass('/transaksi/supplier/', $current_uri) ?>">
                    <i class="fa-solid fa-handshake text-slate-400"></i> Supplier & Harga
                </a>
            </div>
        </div>

        <a href="<?= BASE_URL ?>gudang/opname/" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= getNavClass('/gudang/opname/', $current_uri) ?>">
            <i class="fa-solid fa-clipboard-check w-5 text-center text-purple-500"></i> <span class="text-sm">Stok Opname</span>
        </a>

        <div x-data="{ open: <?= strpos($current_uri, 'laporan') !== false ? 'true' : 'false' ?> }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-lines w-5 text-center text-rose-500"></i>
                    <span class="text-sm">Laporan Lengkap</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <a href="<?= BASE_URL ?>gudang/laporan/masuk/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-arrow-right-to-bracket text-slate-400"></i> Barang Masuk
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/keluar/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-arrow-right-from-bracket text-slate-400"></i> Barang Keluar
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/po/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-cart-shopping text-slate-400"></i> Purchase Order (PO)
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/pembayaran/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-money-check-dollar text-slate-400"></i> Pembayaran PO
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/opname/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-clipboard-list text-slate-400"></i> Stok Opname
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/kartu-stok/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Kartu Stok
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/stok-menipis/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-rose-500 hover:text-rose-600">
                    <i class="fa-solid fa-arrow-trend-down"></i> Stok Menipis
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/stok-terbanyak/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-emerald-500 hover:text-emerald-600">
                    <i class="fa-solid fa-arrow-trend-up"></i> Stok Terbanyak
                </a>
                <a href="<?= BASE_URL ?>gudang/laporan/perbandingan-harga/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[11px] font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-scale-balanced text-slate-400"></i> Perbandingan Harga
                </a>
            </div>
        </div>

        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all text-slate-500 hover:bg-slate-50 hover:text-blue-600 font-medium">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-gears w-5 text-center text-slate-500"></i>
                    <span class="text-sm">Pengaturan</span>
                </div>
                <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open" x-transition class="mt-1 ml-4 pl-2 border-l-2 border-slate-100 space-y-1">
                <a href="<?= BASE_URL ?>gudang/pengaturan/karyawan/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-user-group text-slate-400"></i> Karyawan
                </a>
                <a href="<?= BASE_URL ?>gudang/pengaturan/pembayaran/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-credit-card text-slate-400"></i> Master Pembayaran
                </a>
                <a href="<?= BASE_URL ?>gudang/pengaturan/user-management/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-user-shield text-slate-400"></i> User Management
                </a>
                <a href="<?= BASE_URL ?>gudang/pengaturan/profil/" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-bold text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-store text-slate-400"></i> Profil Toko
                </a>
            </div>
        </div>

    </nav>

    <div class="p-4 border-t border-slate-100 bg-slate-50/30">
        <a href="<?= BASE_URL ?>logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 transition-all font-bold">
            <i class="fa-solid fa-right-from-bracket w-5 text-center"></i> <span class="text-sm">Keluar</span>
        </a>
    </div>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 duration-300"></div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    [x-cloak] { display: none !important; }
</style>