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

// Fungsi untuk mengecek apakah dropdown harus terbuka (aktif)
function isDropdownActive($paths, $current_uri)
{
    foreach ($paths as $path) {
        if (strpos($current_uri, $path) !== false) return true;
    }
    return false;
}
?>

<aside id="main-sidebar" class="w-64 bg-surface border-r border-slate-200 flex-col shadow-sm fixed inset-y-0 left-0 z-[70] transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 flex">

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 shrink-0">
        <h1 class="font-bold text-primary text-xl"><i class="fa-solid fa-cake-candles mr-2"></i> RotiKu</h1>
        <button onclick="toggleSidebar()" class="md:hidden text-secondary hover:text-danger p-2 rounded-lg bg-slate-50 hover:bg-red-50 transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <div class="px-4 py-3 mt-2 shrink-0">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider sidebar-text transition-all duration-300 opacity-100">Menu Produksi</p>
    </div>

    <nav class="flex-1 px-4 space-y-2 overflow-y-auto custom-scrollbar pb-6">
        
        <!-- MENU TUNGGAL -->
        <a href="<?= BASE_URL ?>produksi/dashboard/" title="Dashboard" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/dashboard/', $current_uri) ?>">
            <i class="fa-solid fa-chart-pie w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>produksi/rencana_harian/" title="Rencana Harian" onclick="closeSidebarMobile()" class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all <?= getNavClass('/produksi/rencana_harian/', $current_uri) ?>">
            <i class="fa-solid fa-clipboard-list w-6 text-center text-lg shrink-0"></i> 
            <span class="text-sm sidebar-text whitespace-nowrap transition-all duration-300 opacity-100">Rencana Harian</span>
        </a>

        <!-- DROPDOWN: INPUT DATA -->
        <?php 
        $inputPaths = ['/produksi/input_produksi/', '/produksi/input_titipan/']; 
        $isInputActive = isDropdownActive($inputPaths, $current_uri);
        ?>
        <div>
            <button onclick="toggleSubmenu('submenu-input', 'icon-input')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl transition-all <?= $isInputActive ? 'text-primary font-bold' : 'text-secondary hover:bg-slate-50 hover:text-primary font-medium' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-fire-burner w-6 text-center text-lg shrink-0"></i>
                    <span class="text-sm sidebar-text whitespace-nowrap">Input Data</span>
                </div>
                <i id="icon-input" class="fa-solid fa-chevron-<?= $isInputActive ? 'down' : 'right' ?> text-xs transition-transform duration-200"></i>
            </button>
            <div id="submenu-input" class="<?= $isInputActive ? 'flex' : 'hidden' ?> flex-col gap-1 mt-1 pl-10 pr-2">
                <a href="<?= BASE_URL ?>produksi/input_produksi/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getNavClass('/produksi/input_produksi/', $current_uri) ?>">Input Produksi</a>
                <a href="<?= BASE_URL ?>produksi/input_titipan/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getNavClass('/produksi/input_titipan/', $current_uri) ?>">Input Titipan</a>
            </div>
        </div>

        <!-- DROPDOWN: HISTORI & RIWAYAT -->
        <?php 
        $riwayatPaths = ['/produksi/riwayat_produksi/', '/produksi/riwayat_titipan/', '/produksi/riwayat_status/']; 
        $isRiwayatActive = isDropdownActive($riwayatPaths, $current_uri);
        ?>
        <div>
            <button onclick="toggleSubmenu('submenu-riwayat', 'icon-riwayat')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl transition-all <?= $isRiwayatActive ? 'text-primary font-bold' : 'text-secondary hover:bg-slate-50 hover:text-primary font-medium' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-clock-rotate-left w-6 text-center text-lg shrink-0"></i>
                    <span class="text-sm sidebar-text whitespace-nowrap">Histori & Riwayat</span>
                </div>
                <i id="icon-riwayat" class="fa-solid fa-chevron-<?= $isRiwayatActive ? 'down' : 'right' ?> text-xs transition-transform duration-200"></i>
            </button>
            <div id="submenu-riwayat" class="<?= $isRiwayatActive ? 'flex' : 'hidden' ?> flex-col gap-1 mt-1 pl-10 pr-2">
                <a href="<?= BASE_URL ?>produksi/riwayat_produksi/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getNavClass('/produksi/riwayat_produksi/', $current_uri) ?>">Riwayat Produksi</a>
                <a href="<?= BASE_URL ?>produksi/riwayat_titipan/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getNavClass('/produksi/riwayat_titipan/', $current_uri) ?>">Riwayat Titipan</a>
                <a href="<?= BASE_URL ?>produksi/riwayat_status/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getNavClass('/produksi/riwayat_status/', $current_uri) ?>">Riwayat Status</a>
            </div>
        </div>

        <!-- DROPDOWN: PRODUK KELUAR -->
        <?php 
        $keluarPaths = ['/produksi/produk_keluar/', '/produksi/keluar_titipan/']; 
        $isKeluarActive = isDropdownActive($keluarPaths, $current_uri);
        ?>
        <div>
            <button onclick="toggleSubmenu('submenu-keluar', 'icon-keluar')" class="w-full flex items-center justify-between px-3 py-3 rounded-xl transition-all <?= $isKeluarActive ? 'text-danger font-bold' : 'text-secondary hover:bg-slate-50 hover:text-danger font-medium' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-box-open w-6 text-center text-lg shrink-0"></i>
                    <span class="text-sm sidebar-text whitespace-nowrap">Produk Keluar</span>
                </div>
                <i id="icon-keluar" class="fa-solid fa-chevron-<?= $isKeluarActive ? 'down' : 'right' ?> text-xs transition-transform duration-200"></i>
            </button>
            <div id="submenu-keluar" class="<?= $isKeluarActive ? 'flex' : 'hidden' ?> flex-col gap-1 mt-1 pl-10 pr-2">
                <a href="<?= BASE_URL ?>produksi/produk_keluar/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getDangerNavClass('/produksi/produk_keluar/', $current_uri) ?>">Keluar (Expired)</a>
                <a href="<?= BASE_URL ?>produksi/keluar_titipan/" onclick="closeSidebarMobile()" class="block px-3 py-2 text-sm rounded-lg transition-all <?= getDangerNavClass('/produksi/keluar_titipan/', $current_uri) ?>">Keluar (Titipan)</a>
            </div>
        </div>

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

    // Fungsi baru untuk mengatur buka-tutup dropdown
    function toggleSubmenu(menuId, iconId) {
        const menu = document.getElementById(menuId);
        const icon = document.getElementById(iconId);

        if (menu.classList.contains('hidden')) {
            // Buka dropdown
            menu.classList.remove('hidden');
            menu.classList.add('flex');
            icon.classList.remove('fa-chevron-right');
            icon.classList.add('fa-chevron-down');
        } else {
            // Tutup dropdown
            menu.classList.add('hidden');
            menu.classList.remove('flex');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-right');
        }
    }
</script>