<?php
$current_uri = $_SERVER['REQUEST_URI'];

// Fungsi untuk menu standar (Warna Biru/Primary)
function getNavClass($path, $current_uri)
{
    if (strpos($current_uri, $path) !== false) return 'bg-primary/10 text-primary font-bold';
    return 'text-secondary hover:bg-slate-50 hover:text-primary font-medium';
}

// Fungsi khusus untuk menu Barang Keluar (Warna Merah/Danger)
function getDangerNavClass($path, $current_uri)
{
    if (strpos($current_uri, $path) !== false) return 'bg-danger/10 text-danger font-bold';
    return 'text-secondary hover:bg-slate-50 hover:text-danger font-medium';
}
?>

<aside id="main-sidebar" class="w-64 bg-surface border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">
        <h1 class="font-bold text-primary text-xl"><i class="fa-solid fa-cake-candles mr-2"></i> RotiKu</h1>
        <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-danger p-2 rounded-lg bg-slate-50 hover:bg-red-50 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <div class="px-4 py-3 mt-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider sidebar-text transition-all duration-300 opacity-100">Menu Produksi</p>
    </div>

    <nav class="flex-1 px-4 space-y-2 overflow-y-auto custom-scrollbar">
        <a href="<?= BASE_URL ?>produksi/dashboard/" title="Dashboard" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>produksi/rencana_harian/" title="Rencana Harian" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/rencana_harian/', $current_uri) ?>">
            <i class="fa-solid fa-clipboard-list w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Rencana Harian</span>
        </a>

        <a href="<?= BASE_URL ?>produksi/input_produksi/" title="Input Produksi" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/input_produksi/', $current_uri) ?>">
            <i class="fa-solid fa-fire-burner w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Input Produksi</span>
        </a>
        
        <a href="<?= BASE_URL ?>produksi/input_titipan/" title="Input Produk Titipan" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/input_titipan/', $current_uri) ?>">
            <i class="fa-solid fa-store w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Input Produk Titipan</span>
        </a>

        <a href="<?= BASE_URL ?>produksi/riwayat_produksi/" title="Riwayat & Filter" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/riwayat_produksi/', $current_uri) ?>">
            <i class="fa-solid fa-clock-rotate-left w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Riwayat & Filter</span>
        </a>
        
        <a href="<?= BASE_URL ?>produksi/riwayat_titipan/" title="Riwayat Titipan" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/riwayat_titipan/', $current_uri) ?>">
            <i class="fa-solid fa-clock-rotate-left w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Riwayat Titipan</span>
        </a>
        
        <a href="<?= BASE_URL ?>produksi/produk_keluar/" title="Produk Keluar (Expired)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getDangerNavClass('/produksi/produk_keluar/', $current_uri) ?>">
            <i class="fa-solid fa-box-open w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Produk Keluar (Expired)</span>
        </a>

        <a href="<?= BASE_URL ?>produksi/keluar_titipan/" title="Produk Keluar (Titipan)" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getDangerNavClass('/produksi/keluar_titipan/', $current_uri) ?>">
            <i class="fa-solid fa-box-open w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Produk Keluar (Titipan)</span>
        </a>
        
        <a href="<?= BASE_URL ?>produksi/riwayat_status/" title="Riwayat & Status" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/riwayat_status/', $current_uri) ?>">
            <i class="fa-solid fa-clock-rotate-left w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Riwayat & Status</span>
        </a>
    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-[60] hidden md:hidden backdrop-blur-sm transition-opacity opacity-0 duration-300"></div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('-translate-x-full');
        if (sidebar.classList.contains('-translate-x-full')) {
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        } else {
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
            }, 10);
        }
    }

    function closeSidebarMobile() {
        if (window.innerWidth < 768) toggleSidebar();
    }
</script>ers